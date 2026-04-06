<?php

namespace App\Services;

use App\Models\AiSetting;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\WhatsAppInstance;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AIService
{
    public function generateReply($instance, $conversation, $message): array
    {
        $messageText = trim((string) $message);
        $isArabic = preg_match('/[\x{0600}-\x{06FF}]/u', $messageText) === 1;
        $fallbackReply = $isArabic
            ? 'حصل مشكلة بسيطة 🤍 حاول تاني'
            : 'Something went wrong 🤍 please try again';
        $fallbackIntent = $isArabic ? 'استفسار عام' : 'general inquiry';

        try {
            $instanceModel = $instance instanceof WhatsAppInstance
                ? $instance
                : WhatsAppInstance::query()->findOrFail($instance);

            $conversationModel = $conversation instanceof Conversation
                ? $conversation
                : Conversation::query()->findOrFail($conversation);

            $settings = AiSetting::query()->firstOrCreate(
                ['instance_id' => $instanceModel->id],
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

            $intentTags = collect($settings->intents ?? [])
                ->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')
                ->map(fn ($tag) => trim($tag))
                ->values()
                ->all();

            if (! empty($intentTags)) {
                $fallbackIntent = (string) $intentTags[0];
            }

            $contextLimit = max(1, (int) $settings->context_limit);

            $history = Message::query()
                ->where('conversation_id', $conversationModel->id)
                ->select(['id', 'direction', 'content'])
                ->latest('id')
                ->limit($contextLimit)
                ->get()
                ->reverse()
                ->values();

            $messages = [];
            $systemParts = array_filter([
                trim((string) $settings->system_prompt),
                trim((string) $settings->rules),
                trim((string) $settings->restrictions),
            ], static fn ($part) => $part !== '');

            $systemParts[] = 'Analyze the overall conversation (not only the latest message) and determine user intent in 1 or 2 words only. '
                .'Return strict JSON only with keys: reply (string), intent (string max 2 words).';

            if (! empty($intentTags)) {
                $systemParts[] = 'Classify intent using one of these tags only: '.implode(', ', $intentTags).'. '
                    .'If nothing matches exactly, choose the closest one.';
            }

            $messages[] = [
                'role' => 'system',
                'content' => implode("\n\n", array_values($systemParts)),
            ];

            foreach ($history as $item) {
                $messages[] = [
                    'role' => $item->direction === 'outbound' ? 'assistant' : 'user',
                    'content' => (string) $item->content,
                ];
            }

            $latest = $history->last();
            $latestIsCurrentInbound = $latest
                && $latest->direction === 'inbound'
                && trim((string) $latest->content) === $messageText;

            if (! $latestIsCurrentInbound) {
                $messages[] = [
                    'role' => 'user',
                    'content' => $messageText,
                ];
            }

            $apiKey = (string) config('services.openai.key', '');
            if ($apiKey === '') {
                return $this->fallbackPayload($fallbackReply, $fallbackIntent);
            }

            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => (string) $settings->model,
                    'messages' => $messages,
                    'temperature' => (float) $settings->temperature,
                    'max_tokens' => (int) $settings->max_tokens,
                    'response_format' => [
                        'type' => 'json_object',
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('OpenAI request failed.', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return $this->fallbackPayload($fallbackReply, $fallbackIntent);
            }

            $responseJson = $response->json();
            $usage = data_get($responseJson, 'usage', []);
            $promptTokens = (int) data_get($usage, 'prompt_tokens', 0);
            $completionTokens = (int) data_get($usage, 'completion_tokens', 0);
            $totalTokens = (int) data_get($usage, 'total_tokens', 0);

            if ($totalTokens === 0 && ($promptTokens > 0 || $completionTokens > 0)) {
                $totalTokens = $promptTokens + $completionTokens;
            }

            $content = (string) data_get($responseJson, 'choices.0.message.content', '');
            $payload = $this->extractJsonPayload($content);

            $reply = trim((string) ($payload['reply'] ?? ''));
            $intent = $this->normalizeIntent(
                (string) ($payload['intent'] ?? ''),
                $fallbackIntent
            );

            if ($reply === '') {
                return $this->fallbackPayload($fallbackReply, $intent);
            }

            return [
                'reply' => $reply,
                'intent' => $intent,
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
            ];
        } catch (Throwable $exception) {
            report($exception);

            return $this->fallbackPayload($fallbackReply, $fallbackIntent);
        }
    }

    private function fallbackPayload(string $reply, string $intent): array
    {
        return [
            'reply' => $reply,
            'intent' => $this->normalizeIntent($intent, 'general inquiry'),
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0,
        ];
    }

    private function extractJsonPayload(string $content): array
    {
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $content, $matches) === 1) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function normalizeIntent(string $intent, string $fallback): string
    {
        $cleaned = preg_replace('/[^\p{L}\p{N}\s\-]+/u', ' ', trim($intent));
        $cleaned = is_string($cleaned) ? preg_replace('/\s+/u', ' ', trim($cleaned)) : '';
        $cleaned = is_string($cleaned) ? $cleaned : '';

        if ($cleaned === '') {
            $cleaned = $fallback;
        }

        $words = preg_split('/\s+/u', trim($cleaned), -1, PREG_SPLIT_NO_EMPTY);
        if (! is_array($words) || $words === []) {
            return $fallback;
        }

        $intentTwoWords = implode(' ', array_slice($words, 0, 2));

        return $intentTwoWords !== '' ? $intentTwoWords : $fallback;
    }
}
