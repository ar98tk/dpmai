<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessIncomingMessage;
use App\Models\WhatsAppInstance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request, string $instance_key): JsonResponse
    {
        $payload = $request->all();

        $instance = WhatsAppInstance::query()
            ->where('instance_key', $instance_key)
            ->first();

        if (! $instance) {
            return response()->json([
                'success' => false,
                'message' => 'Instance not found.',
            ], 404);
        }

        return $this->dispatchIncomingMessage($instance, $payload);
    }

    public function handleWithoutInstanceKey(Request $request): JsonResponse
    {
        $payload = $request->all();
        $instanceKey = $this->extractInstanceKey($payload);

        if ($instanceKey === null) {
            $singleInstance = WhatsAppInstance::query()->count() === 1
                ? WhatsAppInstance::query()->first()
                : null;

            if ($singleInstance) {
                return $this->dispatchIncomingMessage($singleInstance, $payload);
            }

            return response()->json([
                'success' => false,
                'message' => 'Instance key is missing.',
            ], 404);
        }

        $instance = WhatsAppInstance::query()
            ->where('instance_key', $instanceKey)
            ->first();

        if (! $instance) {
            return response()->json([
                'success' => false,
                'message' => 'Instance not found.',
            ], 404);
        }

        return $this->dispatchIncomingMessage($instance, $payload);
    }

    private function dispatchIncomingMessage(WhatsAppInstance $instance, array $payload): JsonResponse
    {
        if ($this->isFromMe($payload)) {
            return response()->json(['success' => true]);
        }

        $messageText = $this->extractMessageText($payload);
        $senderNumber = $this->extractSenderNumber($payload);
        $messageId = $this->extractMessageId($payload);

        if ($messageText === '' || $senderNumber === '') {
            Log::warning('Webhook payload skipped: missing message or sender.', [
                'instance_id' => $instance->id,
                'has_message' => $messageText !== '',
                'has_sender' => $senderNumber !== '',
                'payload_keys' => array_keys($payload),
            ]);

            return response()->json(['success' => true]);
        }

        ProcessIncomingMessage::dispatch($instance->id, $messageText, $senderNumber, $messageId);

        return response()->json(['success' => true]);
    }

    private function extractMessageText(array $payload): string
    {
        $candidates = [
            data_get($payload, 'message.conversation'),
            data_get($payload, 'message.extendedTextMessage.text'),
            data_get($payload, 'message.imageMessage.caption'),
            data_get($payload, 'message.videoMessage.caption'),
            data_get($payload, 'data.message.conversation'),
            data_get($payload, 'data.message.extendedTextMessage.text'),
            data_get($payload, 'data.message.imageMessage.caption'),
            data_get($payload, 'data.message.videoMessage.caption'),
            data_get($payload, 'data.body'),
            data_get($payload, 'body'),
            data_get($payload, 'message'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return '';
    }

    private function extractSenderNumber(array $payload): string
    {
        $rawSender = '';
        $candidates = [
            data_get($payload, 'key.remoteJidAlt'),
            data_get($payload, 'key.remoteJid'),
            data_get($payload, 'key.participantAlt'),
            data_get($payload, 'key.participant'),
            data_get($payload, 'data.key.remoteJid'),
            data_get($payload, 'data.key.remoteJidAlt'),
            data_get($payload, 'data.key.participant'),
            data_get($payload, 'data.key.participantAlt'),
            data_get($payload, 'data.sender'),
            data_get($payload, 'sender'),
            data_get($payload, 'from'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                $rawSender = trim($candidate);
                break;
            }
        }

        if ($rawSender === '' || str_contains($rawSender, '@g.us')) {
            return '';
        }

        if (str_contains($rawSender, '@')) {
            $rawSender = explode('@', $rawSender)[0];
        }

        $rawSender = ltrim($rawSender, '+');
        $normalized = preg_replace('/\D+/', '', $rawSender);

        return is_string($normalized) ? $normalized : '';
    }

    private function extractMessageId(array $payload): ?string
    {
        $candidates = [
            data_get($payload, 'key.id'),
            data_get($payload, 'data.key.id'),
            data_get($payload, 'data.messageId'),
            data_get($payload, 'messageId'),
            data_get($payload, 'id'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return null;
    }

    private function extractInstanceKey(array $payload): ?string
    {
        $candidates = [
            data_get($payload, 'instance'),
            data_get($payload, 'instanceName'),
            data_get($payload, 'instance_key'),
            data_get($payload, 'data.instance'),
            data_get($payload, 'data.instanceName'),
            data_get($payload, 'data.instance_key'),
            data_get($payload, 'event.instance'),
            data_get($payload, 'event.instanceName'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return null;
    }

    private function isFromMe(array $payload): bool
    {
        return (bool) (data_get($payload, 'key.fromMe') ?? data_get($payload, 'data.key.fromMe'));
    }
}
