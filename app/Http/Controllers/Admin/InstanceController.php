<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Models\Business;
use App\Models\Conversation;
use App\Models\Lead;
use App\Models\WhatsAppInstance;
use App\Services\EvolutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstanceController extends Controller
{
    private const FREE_DAILY_TOKEN_LIMIT = 1000;
    private const FREE_MONTHLY_TOKEN_LIMIT = 5000;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
            'business_id' => ['required', 'integer', 'exists:businesses,id'],
        ]);

        $business = Business::query()
            ->findOrFail((int) $validated['business_id']);
        $activeSubscription = $business->getActiveSubscription();
        $activePlan = $activeSubscription ? $activeSubscription->plan : null;

        if (! $activePlan) {
            $message = 'No active subscription found for this business.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()
                ->route('admin.businesses.show', $business)
                ->withErrors([
                    'business_id' => $message,
                ]);
        }

        $maxInstances = (int) $activePlan->max_instances;
        $currentInstancesCount = WhatsAppInstance::query()
            ->where('business_id', $business->id)
            ->count();

        if ($currentInstancesCount >= $maxInstances) {
            $message = 'Maximum instances limit reached for this business plan.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()
                ->route('admin.businesses.show', $business)
                ->withErrors([
                    'business_id' => $message,
                ]);
        }

        do {
            $instanceKey = (string) Str::uuid();
        } while (WhatsAppInstance::query()->where('instance_key', $instanceKey)->exists());

        $instance = WhatsAppInstance::create([
            'business_id' => $validated['business_id'],
            'name' => $validated['name'],
            'phone_number' => $this->sanitizePhoneNumber($validated['phone_number']),
            'instance_key' => $instanceKey,
            'status' => 'pending',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Instance created successfully.',
                'data' => $instance,
            ], 201);
        }

        return redirect()
            ->route('admin.businesses.show', $business)
            ->with('success', 'Instance created successfully. Scan QR to connect.');
    }

    public function qr(WhatsAppInstance $instance, EvolutionService $evolution): JsonResponse
    {
        $this->authorizeBusinessAccess($instance);

        $qrResponse = $evolution->getQRCode($instance->instance_key);

        if (! $qrResponse['success'] && $this->shouldCreateInstance($qrResponse['message'] ?? '')) {
            $createResponse = $evolution->createInstance($instance->instance_key);
            if (! $createResponse['success'] && ! $this->isAlreadyExistsError($createResponse['message'] ?? '')) {
                return response()->json([
                    'success' => false,
                    'message' => $createResponse['message'] ?? 'Failed to create instance on Evolution.',
                ], 422);
            }

            $qrResponse = $evolution->getQRCode($instance->instance_key);
        }

        if (! $qrResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => $qrResponse['message'] ?? 'Failed to fetch QR code.',
            ], 422);
        }

        $this->ensureWebhookConfigured($instance, $evolution);

        $qrCode = $this->extractQrCode($qrResponse['data']);
        if (! $qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'QR code not found in Evolution response.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'QR code generated successfully.',
            'data' => [
                'qr' => $qrCode,
            ],
        ]);
    }

    public function status(WhatsAppInstance $instance, EvolutionService $evolution): JsonResponse
    {
        $this->authorizeBusinessAccess($instance);
        $this->ensureWebhookConfigured($instance, $evolution);

        $statusResponse = $evolution->getInstanceStatus($instance->instance_key);
        if (! $statusResponse['success']) {
            if ($instance->status !== 'disconnected') {
                $instance->update(['status' => 'disconnected']);
            }

            return response()->json([
                'status' => 'disconnected',
            ]);
        }

        $status = $this->normalizeStatus($this->extractStatus($statusResponse['data']));

        if ($status === 'connected' && $instance->status !== 'connected') {
            $instance->update(['status' => 'connected']);
        } elseif ($status === 'disconnected' && $instance->status !== 'disconnected') {
            $instance->update(['status' => 'disconnected']);
        } elseif ($status === 'pending' && $instance->status === 'connected') {
            $instance->update(['status' => 'disconnected']);
            $status = 'disconnected';
        }

        return response()->json([
            'status' => $status,
        ]);
    }

    public function edit(WhatsAppInstance $instance): View
    {
        $this->authorizeBusinessAccess($instance);

        $aiSetting = AiSetting::query()->firstOrCreate(
            ['instance_id' => $instance->id],
            [
                'system_prompt' => '',
                'rules' => null,
                'restrictions' => null,
                'intents' => [],
                'model' => 'gpt-4o-mini',
                'temperature' => 0.7,
                'max_tokens' => 500,
                'context_limit' => 10,
            ]
        );

        return view('admin.instances.edit', [
            'instance' => $instance,
            'aiSetting' => $aiSetting,
        ]);
    }

    public function leads(WhatsAppInstance $instance): View
    {
        $this->authorizeBusinessAccess($instance);

        $hasNameColumn = Schema::hasColumn('leads', 'name');
        $hasMessagesCountColumn = Schema::hasColumn('leads', 'messages_count');

        $leadsQuery = Lead::query()
            ->where('instance_id', $instance->id)
            ->select(['id', 'phone', 'intent', 'last_interaction_at']);

        if ($hasNameColumn) {
            $leadsQuery->addSelect('name');
        } else {
            $leadsQuery->selectRaw('NULL as name');
        }

        if ($hasMessagesCountColumn) {
            $leadsQuery->addSelect('messages_count');
        } else {
            $leadsQuery->selectRaw('0 as messages_count');
        }

        $leads = $leadsQuery
            ->orderByDesc('last_interaction_at')
            ->orderByDesc('id')
            ->get();

        $countsByPhone = DB::table('conversations')
            ->join('messages', 'messages.conversation_id', '=', 'conversations.id')
            ->where('conversations.instance_id', $instance->id)
            ->selectRaw('conversations.phone, COUNT(messages.id) as aggregate_count')
            ->groupBy('conversations.phone')
            ->pluck('aggregate_count', 'conversations.phone');

        $leads->transform(function ($lead) use ($countsByPhone) {
            $storedCount = (int) ($lead->messages_count ?? 0);
            $calculatedCount = (int) ($countsByPhone[$lead->phone] ?? 0);

            $lead->setAttribute('display_messages_count', max($storedCount, $calculatedCount));

            return $lead;
        });

        return view('admin.instances.leads', [
            'instance' => $instance,
            'leads' => $leads,
        ]);
    }

    public function chat(WhatsAppInstance $instance, Lead $lead): View
    {
        $this->authorizeBusinessAccess($instance);
        abort_unless((int) $lead->instance_id === (int) $instance->id, 404);

        $conversation = Conversation::query()
            ->where('instance_id', $instance->id)
            ->where('phone', $lead->phone)
            ->first();

        $messages = $conversation
            ? $conversation->messages()->orderBy('created_at')->get()
            : collect();

        return view('admin.instances.chat', [
            'instance' => $instance,
            'lead' => $lead,
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    public function update(Request $request, WhatsAppInstance $instance)
    {
        $this->authorizeBusinessAccess($instance);

        if ($this->hasAiSettingsPayload($request)) {
            return $this->updateAiSettings($request, $instance);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'ai_enabled' => ['sometimes', 'required', 'boolean'],
        ]);

        if (empty($validated)) {
            return response()->json([
                'success' => false,
                'message' => 'No fields provided for update.',
            ], 422);
        }

        $payload = [];
        if (array_key_exists('name', $validated)) {
            $payload['name'] = $validated['name'];
        }
        if (array_key_exists('ai_enabled', $validated)) {
            if ((bool) $validated['ai_enabled']) {
                $business = Business::query()->find($instance->business_id);

                if ($business && $this->hasExceededTokenLimits($business)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'AI is paused because this business reached the daily or monthly token limit.',
                    ], 422);
                }
            }

            $payload['ai_enabled'] = (bool) $validated['ai_enabled'];
        }

        $instance->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Instance updated successfully.',
            'data' => $instance->fresh(),
        ]);
    }

    public function destroy(WhatsAppInstance $instance, EvolutionService $evolution): JsonResponse
    {
        $this->authorizeBusinessAccess($instance);

        $deleteResponse = $evolution->deleteInstance($instance->instance_key);
        if (! $deleteResponse['success'] && ! $this->isSafeDeleteFailure($deleteResponse['message'] ?? '')) {
            return response()->json([
                'success' => false,
                'message' => $deleteResponse['message'] ?? 'Failed to delete instance from Evolution.',
            ], 422);
        }

        $instance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Instance deleted successfully.',
        ]);
    }

    public function destroyLead(WhatsAppInstance $instance, Lead $lead): RedirectResponse
    {
        $this->authorizeBusinessAccess($instance);
        abort_unless((int) $lead->instance_id === (int) $instance->id, 404);

        $lead->delete();

        return redirect()
            ->route('admin.instances.leads', $instance)
            ->with('success', 'Lead deleted successfully.');
    }

    private function authorizeBusinessAccess(WhatsAppInstance $instance): void
    {
        abort_unless($instance->exists, 404);
    }

    private function isAlreadyExistsError(string $message): bool
    {
        $normalized = strtolower($message);

        return str_contains($normalized, 'already') && str_contains($normalized, 'exist');
    }

    private function shouldCreateInstance(string $message): bool
    {
        $normalized = strtolower($message);

        return str_contains($normalized, 'not found')
            || str_contains($normalized, 'does not exist')
            || str_contains($normalized, 'instance not found');
    }

    private function isSafeDeleteFailure(string $message): bool
    {
        $normalized = strtolower($message);

        return str_contains($normalized, 'not found')
            || str_contains($normalized, 'does not exist')
            || str_contains($normalized, 'already deleted');
    }

    private function extractQrCode($payload): ?string
    {
        if (! is_array($payload)) {
            return null;
        }

        $candidates = [
            data_get($payload, 'base64'),
            data_get($payload, 'qrcode'),
            data_get($payload, 'qr'),
            data_get($payload, 'code'),
            data_get($payload, 'data.base64'),
            data_get($payload, 'data.qrcode'),
            data_get($payload, 'data.qr'),
            data_get($payload, 'qrcode.base64'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function extractStatus($payload): string
    {
        if (is_string($payload) && $payload !== '') {
            return $payload;
        }

        if (! is_array($payload)) {
            return '';
        }

        $candidates = [
            data_get($payload, 'instance.state'),
            data_get($payload, 'state'),
            data_get($payload, 'status'),
            data_get($payload, 'connectionStatus'),
            data_get($payload, 'data.state'),
            data_get($payload, 'data.status'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return '';
    }

    private function normalizeStatus(string $rawStatus): string
    {
        $normalized = strtolower(trim($rawStatus));

        if (in_array($normalized, ['open', 'connected'], true)) {
            return 'connected';
        }

        if (in_array($normalized, ['close', 'closed', 'disconnected', 'logout'], true)) {
            return 'disconnected';
        }

        return 'pending';
    }

    private function sanitizePhoneNumber(string $phoneNumber): string
    {
        $normalized = trim($phoneNumber);
        $normalized = str_replace([' ', "\t", "\n", "\r"], '', $normalized);

        return ltrim($normalized, '+');
    }

    private function hasAiSettingsPayload(Request $request): bool
    {
        return $request->hasAny([
            'system_prompt',
            'rules',
            'restrictions',
            'intents',
            'model',
            'temperature',
            'context_limit',
        ]);
    }

    private function updateAiSettings(Request $request, WhatsAppInstance $instance)
    {
        $validated = $request->validate([
            'system_prompt' => ['required', 'string'],
            'rules' => ['nullable', 'string'],
            'restrictions' => ['nullable', 'string'],
            'intents' => ['nullable', 'array'],
            'intents.*' => ['nullable', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:255'],
            'temperature' => ['required', 'numeric'],
            'context_limit' => ['required', 'integer', 'min:1'],
        ]);

        $validated['intents'] = $this->normalizeIntentTags($validated['intents'] ?? []);

        $aiSetting = AiSetting::query()->firstOrCreate(
            ['instance_id' => $instance->id],
            [
                'system_prompt' => '',
                'rules' => null,
                'restrictions' => null,
                'intents' => [],
                'model' => 'gpt-4o-mini',
                'temperature' => 0.7,
                'max_tokens' => 500,
                'context_limit' => 10,
            ]
        );

        $aiSetting->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'AI settings and instructions updated for the phone number.',
                'data' => $aiSetting->fresh(),
            ]);
        }

        return redirect()
            ->route('admin.instances.edit', $instance)
            ->with('success', 'AI settings and instructions updated for the phone number.');
    }

    private function ensureWebhookConfigured(WhatsAppInstance $instance, EvolutionService $evolution): void
    {
        $expectedUrl = $this->buildWebhookUrl($instance);

        if ((string) $instance->webhook_secret === $expectedUrl) {
            return;
        }

        $existingWebhookResponse = $evolution->getWebhook($instance->instance_key);
        if (($existingWebhookResponse['success'] ?? false) === true) {
            $existingWebhookUrl = $this->extractWebhookUrl($existingWebhookResponse['data'] ?? null);
            if (is_string($existingWebhookUrl) && trim($existingWebhookUrl) === $expectedUrl) {
                $instance->forceFill([
                    'webhook_secret' => $expectedUrl,
                ])->save();

                return;
            }
        }

        $setWebhookResponse = $evolution->setWebhook($instance->instance_key, $expectedUrl, ['MESSAGES_UPSERT']);

        if (($setWebhookResponse['success'] ?? false) === true) {
            $instance->forceFill([
                'webhook_secret' => $expectedUrl,
            ])->save();

            return;
        }

        Log::warning('Failed to configure Evolution webhook for instance.', [
            'instance_id' => $instance->id,
            'instance_key' => $instance->instance_key,
            'expected_url' => $expectedUrl,
            'response_status' => $setWebhookResponse['status'] ?? null,
            'response_message' => $setWebhookResponse['message'] ?? null,
        ]);
    }

    private function buildWebhookUrl(WhatsAppInstance $instance): string
    {
        $pathTemplate = (string) config('evolution.webhook_path', '/webhook/whatsapp/{instance_key}');
        $resolvedPath = str_replace('{instance_key}', $instance->instance_key, $pathTemplate);
        $normalizedPath = '/'.ltrim($resolvedPath, '/');

        $configuredBaseUrl = trim((string) config('evolution.webhook_base_url', ''));
        if ($configuredBaseUrl !== '') {
            return rtrim($configuredBaseUrl, '/').$normalizedPath;
        }

        return url($normalizedPath);
    }

    private function extractWebhookUrl($payload): ?string
    {
        if (! is_array($payload)) {
            return null;
        }

        $candidates = [
            data_get($payload, 'url'),
            data_get($payload, 'webhook.url'),
            data_get($payload, 'data.url'),
            data_get($payload, 'data.webhook.url'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return null;
    }

    private function normalizeIntentTags(array $tags): array
    {
        $normalized = [];

        foreach ($tags as $tag) {
            if (! is_string($tag)) {
                continue;
            }

            $cleanTag = trim(preg_replace('/\s+/', ' ', $tag) ?? '');
            if ($cleanTag === '') {
                continue;
            }

            $normalized[] = mb_substr($cleanTag, 0, 60);
        }

        return array_values(array_unique($normalized));
    }

    private function hasExceededTokenLimits(Business $business): bool
    {
        [$dailyLimit, $monthlyLimit] = $this->resolveTokenLimits($business);

        $dailyReached = $dailyLimit > 0 && (int) $business->daily_tokens_used >= $dailyLimit;
        $monthlyReached = $monthlyLimit > 0 && (int) $business->monthly_tokens_used >= $monthlyLimit;

        return $dailyReached || $monthlyReached;
    }

    private function resolveTokenLimits(Business $business): array
    {
        $activeSubscription = $business->getActiveSubscription();
        $activePlan = $activeSubscription ? $activeSubscription->plan : null;

        if (! $activePlan) {
            return [self::FREE_DAILY_TOKEN_LIMIT, self::FREE_MONTHLY_TOKEN_LIMIT];
        }

        return [
            (int) $activePlan->daily_token_limit,
            (int) $activePlan->monthly_token_limit,
        ];
    }
}
