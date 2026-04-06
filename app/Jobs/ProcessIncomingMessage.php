<?php

namespace App\Jobs;

use App\Models\Business;
use App\Models\Conversation;
use App\Models\Lead;
use App\Models\Message;
use App\Models\WhatsAppInstance;
use App\Services\AIService;
use App\Services\EvolutionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProcessIncomingMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const FREE_DAILY_TOKEN_LIMIT = 1000;
    private const FREE_MONTHLY_TOKEN_LIMIT = 5000;

    private static ?array $leadColumns = null;

    public int $tries = 3;
    public int $instanceId;
    public string $message;
    public string $phone;
    public ?string $incomingMessageId;

    public function __construct(int $instanceId, string $message, string $phone, ?string $incomingMessageId = null)
    {
        $this->instanceId = $instanceId;
        $this->message = trim($message);
        $this->phone = $this->normalizePhone($phone);
        $this->incomingMessageId = $incomingMessageId;
    }

    public function handle(AIService $aiService, EvolutionService $evolutionService): void
    {
        $instance = WhatsAppInstance::query()
            ->with('business')
            ->find($this->instanceId);

        if (! $instance || ! $instance->business || $this->message === '' || $this->phone === '') {
            return;
        }

        $business = $instance->business;
        [$dailyLimit, $monthlyLimit] = $this->resolveTokenLimits($business);

        $conversation = Conversation::query()->firstOrCreate(
            [
                'business_id' => $instance->business_id,
                'instance_id' => $instance->id,
                'phone' => $this->phone,
            ],
            [
                'last_message_at' => now(),
            ]
        );

        $conversation->forceFill([
            'last_message_at' => now(),
        ])->save();

        $inboundMessageId = $this->incomingMessageId ?: 'in-'.Str::uuid();

        if ($this->incomingMessageId && Message::query()->where('message_id', $inboundMessageId)->exists()) {
            return;
        }

        Message::query()->create([
            'conversation_id' => $conversation->id,
            'message_id' => $inboundMessageId,
            'direction' => 'inbound',
            'content' => $this->message,
        ]);

        $intent = null;

        if ($instance->ai_enabled) {
            if ($this->hasExceededTokenLimits($business, $dailyLimit, $monthlyLimit)) {
                $limitMessage = $this->limitReachedMessage($this->message);

                Message::query()->create([
                    'conversation_id' => $conversation->id,
                    'message_id' => 'out-'.Str::uuid(),
                    'direction' => 'outbound',
                    'content' => $limitMessage,
                    'prompt_tokens' => 0,
                    'completion_tokens' => 0,
                    'total_tokens' => 0,
                ]);

                $sendResponse = $evolutionService->sendTextMessage(
                    (string) $instance->instance_key,
                    $this->phone,
                    $limitMessage
                );

                if (! ($sendResponse['success'] ?? false)) {
                    Log::warning('Failed to send usage limit message via Evolution API.', [
                        'instance_id' => $instance->id,
                        'phone' => $this->phone,
                        'error' => $sendResponse['message'] ?? 'Unknown error',
                    ]);
                }
            } else {
                $aiResult = $aiService->generateReply($instance, $conversation, $this->message);
                $reply = trim((string) ($aiResult['reply'] ?? ''));
                $intent = trim((string) ($aiResult['intent'] ?? ''));
                $promptTokens = max(0, (int) ($aiResult['prompt_tokens'] ?? 0));
                $completionTokens = max(0, (int) ($aiResult['completion_tokens'] ?? 0));
                $totalTokens = max(0, (int) ($aiResult['total_tokens'] ?? 0));

                $this->incrementBusinessTokenUsage((int) $business->id, $totalTokens);

                if ($reply !== '') {
                    Message::query()->create([
                        'conversation_id' => $conversation->id,
                        'message_id' => 'out-'.Str::uuid(),
                        'direction' => 'outbound',
                        'content' => $reply,
                        'prompt_tokens' => $promptTokens,
                        'completion_tokens' => $completionTokens,
                        'total_tokens' => $totalTokens,
                    ]);

                    $sendResponse = $evolutionService->sendTextMessage(
                        (string) $instance->instance_key,
                        $this->phone,
                        $reply
                    );

                    if (! ($sendResponse['success'] ?? false)) {
                        Log::warning('Failed to send outbound WhatsApp message via Evolution API.', [
                            'instance_id' => $instance->id,
                            'phone' => $this->phone,
                            'error' => $sendResponse['message'] ?? 'Unknown error',
                        ]);
                    }
                }
            }
        }

        $messagesCount = Message::query()
            ->where('conversation_id', $conversation->id)
            ->count();

        $this->syncLead($instance, $intent, $messagesCount);
    }

    private function normalizePhone(string $phone): string
    {
        $normalized = preg_replace('/\D+/', '', ltrim(trim($phone), '+'));

        return is_string($normalized) ? $normalized : '';
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessIncomingMessage job failed.', [
            'instance_id' => $this->instanceId,
            'phone' => $this->phone,
            'error' => $exception->getMessage(),
        ]);
    }

    private function syncLead(WhatsAppInstance $instance, ?string $intent, int $messagesCount): void
    {
        $lead = Lead::query()->firstOrCreate(
            [
                'business_id' => $instance->business_id,
                'phone' => $this->phone,
            ],
            [
                'instance_id' => $instance->id,
                'status' => 'new',
            ]
        );

        $payload = [
            'instance_id' => $instance->id,
            'last_interaction_at' => now(),
        ];

        if ($intent !== null && $intent !== '') {
            $payload['intent'] = $intent;
        }

        if ($this->hasLeadColumn('name') && $lead->wasRecentlyCreated) {
            $payload['name'] = null;
        }

        if ($this->hasLeadColumn('messages_count')) {
            $payload['messages_count'] = $messagesCount;
        }

        $lead->forceFill($payload)->save();
    }

    private function hasLeadColumn(string $column): bool
    {
        if (self::$leadColumns === null) {
            self::$leadColumns = array_flip(Schema::getColumnListing('leads'));
        }

        return isset(self::$leadColumns[$column]);
    }

    private function hasExceededTokenLimits(Business $business, int $dailyLimit, int $monthlyLimit): bool
    {
        $dailyReached = $dailyLimit > 0 && (int) $business->daily_tokens_used >= $dailyLimit;
        $monthlyReached = $monthlyLimit > 0 && (int) $business->monthly_tokens_used >= $monthlyLimit;

        return $dailyReached || $monthlyReached;
    }

    private function incrementBusinessTokenUsage(int $businessId, int $totalTokens): void
    {
        if ($totalTokens <= 0) {
            return;
        }

        Business::query()->whereKey($businessId)->increment('daily_tokens_used', $totalTokens);
        Business::query()->whereKey($businessId)->increment('monthly_tokens_used', $totalTokens);
    }

    private function limitReachedMessage(string $message): string
    {
        $isArabic = preg_match('/[\x{0600}-\x{06FF}]/u', $message) === 1;

        return $isArabic
            ? "\u{062A}\u{0645} \u{0627}\u{0644}\u{0648}\u{0635}\u{0648}\u{0644} \u{0644}\u{062D}\u{062F} \u{0627}\u{0644}\u{0627}\u{0633}\u{062A}\u{062E}\u{062F}\u{0627}\u{0645} \u{0627}\u{0644}\u{064A}\u{0648}\u{0645}\u{064A} \u{0623}\u{0648} \u{0627}\u{0644}\u{0634}\u{0647}\u{0631}\u{064A} \u{1F90D}"
            : "Daily or monthly limit reached \u{1F90D}";
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
