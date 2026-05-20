<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use App\Exceptions\Kapso\ClosedSessionException;

class KapsoService
{
    public function status(): array
    {
        $enabled = filter_var(config('services.kapso.enabled', false), FILTER_VALIDATE_BOOL);
        $apiKey = trim((string) config('services.kapso.api_key', ''));
        $phoneNumberId = trim((string) config('services.kapso.phone_number_id', ''));

        $missing = [];
        if ($apiKey === '') {
            $missing[] = 'KAPSO_API_KEY';
        }
        if ($phoneNumberId === '') {
            $missing[] = 'KAPSO_PHONE_NUMBER_ID';
        }

        return [
            'enabled' => $enabled,
            'configured' => $enabled && empty($missing),
            'phoneNumberId' => $phoneNumberId !== '' ? $phoneNumberId : null,
            'missing' => $missing,
        ];
    }

    public function sendText(string $to, string $text): array
    {
        return $this->sendMessage([
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $text,
            ],
        ]);
    }

    public function sendDocument(string $to, string $documentUrl, string $caption, string $filename, string $callbackData): array
    {
        return $this->sendMessage([
            'to' => $to,
            'type' => 'document',
            'document' => [
                'link' => $documentUrl,
                'caption' => $caption,
                'filename' => $filename,
            ],
            'biz_opaque_callback_data' => $callbackData,
        ]);
    }

    public function sendTemplate(string $to, string $templateName, array $parameters = []): array
    {
        $payload = [
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
            ],
        ];

        if (!empty($parameters)) {
            $payload['template']['language'] = [
                'code' => 'es_ES',
            ];
            $payload['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => array_map(function ($value) {
                        return ['type' => 'text', 'text' => (string) $value];
                    }, $parameters),
                ],
            ];
        }

        return $this->sendMessage($payload);
    }

    private function sendMessage(array $payload): array
    {
        $status = $this->status();
        if (!($status['configured'] ?? false)) {
            throw new RuntimeException('Kapso no esta configurado.');
        }

        $baseUrl = rtrim((string) config('services.kapso.base_url', 'https://api.kapso.ai/meta/whatsapp/v24.0'), '/');
        $apiKey = (string) config('services.kapso.api_key');
        $phoneNumberId = (string) config('services.kapso.phone_number_id');

        $response = Http::timeout(25)
            ->withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->post("{$baseUrl}/{$phoneNumberId}/messages", array_merge([
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
            ], $payload));

        if (!$response->successful()) {
            $body = $response->body();
            $jsonBody = $response->json();
            $errorMessage = 'Error Kapso: ' . $response->status() . ' - ' . $body;
            
            if (is_array($jsonBody) && isset($jsonBody['error'])) {
                $kapsoError = (string) $jsonBody['error'];
                if (str_contains($kapsoError, '24-hour') || str_contains($kapsoError, 'window')) {
                    throw new ClosedSessionException($errorMessage, $jsonBody);
                }
            }
            
            throw new RuntimeException($errorMessage);
        }

        return $response->json() ?: [];
    }
}
