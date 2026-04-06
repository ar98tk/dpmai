<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class EvolutionService
{
    public function createInstance(string $instanceKey): array
    {
        return $this->request('post', '/instance/create', [
            'instanceName' => $instanceKey,
            'token' => (string) config('evolution.token'),
            'integration' => (string) config('evolution.integration'),
        ]);
    }

    public function getQRCode(string $instanceKey): array
    {
        return $this->request('get', '/instance/connect/'.rawurlencode($instanceKey));
    }

    public function getInstanceStatus(string $instanceKey): array
    {
        return $this->request('get', '/instance/connectionState/'.rawurlencode($instanceKey));
    }

    public function deleteInstance(string $instanceKey): array
    {
        return $this->request('delete', '/instance/delete/'.rawurlencode($instanceKey));
    }

    public function setWebhook(string $instanceKey, string $url, array $events = ['MESSAGES_UPSERT']): array
    {
        return $this->request('post', '/webhook/set/'.rawurlencode($instanceKey), [
            'webhook' => [
                'enabled' => true,
                'url' => $url,
                'events' => $events,
            ],
        ]);
    }

    public function getWebhook(string $instanceKey): array
    {
        return $this->request('get', '/webhook/find/'.rawurlencode($instanceKey));
    }

    public function sendTextMessage(string $instanceKey, string $phone, string $text): array
    {
        return $this->request('post', '/message/sendText/'.rawurlencode($instanceKey), [
            'number' => $phone,
            'text' => $text,
            'textMessage' => [
                'text' => $text,
            ],
        ]);
    }

    public function getStatus(string $instanceKey): array
    {
        return $this->getInstanceStatus($instanceKey);
    }

    private function request(string $method, string $uri, array $payload = []): array
    {
        try {
            $response = $this->client()->{$method}($uri, $payload);

            if ($response->failed()) {
                return [
                    'success' => false,
                    'status' => $response->status(),
                    'message' => $this->resolveErrorMessage($response),
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => true,
                'status' => $response->status(),
                'message' => 'Success',
                'data' => $response->json(),
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'success' => false,
                'status' => null,
                'message' => 'Unable to connect to Evolution API.',
                'data' => null,
            ];
        }
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('evolution.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'apikey' => (string) config('evolution.api_key'),
                'Content-Type' => 'application/json',
            ])
            ->timeout(20);
    }

    private function resolveErrorMessage(Response $response): string
    {
        $json = $response->json();

        if (is_array($json)) {
            if (! empty($json['message']) && is_string($json['message'])) {
                return $json['message'];
            }

            if (! empty($json['error']) && is_string($json['error'])) {
                return $json['error'];
            }
        }

        return 'Evolution API request failed.';
    }
}
