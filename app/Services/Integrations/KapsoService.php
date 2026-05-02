<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;
use RuntimeException;

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
            throw new RuntimeException('Error Kapso: ' . $response->status() . ' - ' . $response->body());
        }

        return $response->json() ?: [];
    }
}
