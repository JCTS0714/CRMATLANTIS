<?php

namespace App\Services\Integrations;

use App\Exceptions\Kapso\ClosedSessionException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class KapsoService
{
    public function status(): array
    {
        $enabled = filter_var(config('services.kapso.enabled', false), FILTER_VALIDATE_BOOL);
        $apiKey = trim((string) config('services.kapso.api_key', ''));
        $phoneNumberId = trim((string) config('services.kapso.phone_number_id', ''));
        $wabaId = trim((string) config('services.kapso.waba_id', ''));

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
            'wabaId' => $wabaId !== '' ? $wabaId : null,
            'metaTemplatesConfigured' => $apiKey !== '' && $wabaId !== '',
            'missing' => $missing,
        ];
    }

    public function listApprovedTemplates(): array
    {
        $apiKey = trim((string) config('services.kapso.api_key', ''));
        $wabaId = trim((string) config('services.kapso.waba_id', ''));
        $baseUrl = rtrim((string) config('services.kapso.base_url', 'https://api.kapso.ai/meta/whatsapp/v24.0'), '/');

        $missing = [];
        if ($apiKey === '') {
            $missing[] = 'KAPSO_API_KEY';
        }
        if ($wabaId === '') {
            $missing[] = 'WABA_ID';
        }

        if ($missing !== []) {
            throw new RuntimeException('Faltan variables para consultar plantillas Meta en Kapso: '.implode(', ', $missing));
        }

        $response = Http::timeout(25)
            ->withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->get("{$baseUrl}/{$wabaId}/message_templates", [
                'fields' => 'id,name,status,language,category,components',
                'limit' => 200,
                'status' => 'APPROVED',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Error consultando plantillas Meta en Kapso: '.$response->status().' - '.$response->body());
        }

        $items = collect($response->json('data', []))
            ->filter(fn ($item) => strtoupper((string) data_get($item, 'status', '')) === 'APPROVED')
            ->map(function ($item) {
                $components = collect(data_get($item, 'components', []));
                $bodyText = (string) ($components->firstWhere('type', 'BODY')['text'] ?? '');
                $headerText = (string) ($components->firstWhere('type', 'HEADER')['text'] ?? '');
                preg_match_all('/\{\{\d+\}\}/', $bodyText, $matches);

                return [
                    'id' => data_get($item, 'id'),
                    'name' => (string) data_get($item, 'name', ''),
                    'status' => (string) data_get($item, 'status', ''),
                    'language' => (string) data_get($item, 'language', ''),
                    'category' => (string) data_get($item, 'category', ''),
                    'header_text' => $headerText,
                    'body_text' => $bodyText,
                    'parameter_count' => count($matches[0] ?? []),
                    'components' => $components->values()->all(),
                ];
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        return [
            'templates' => $items,
            'count' => count($items),
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

    public function sendTemplate(string $to, string $templateName, array $parameters = [], ?string $languageCode = null): array
    {
        $payload = [
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $this->normalizeTemplateLanguageCode($languageCode),
                ],
            ],
        ];

        if ($parameters !== []) {
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

    public function normalizeTemplateLanguageCode(?string $languageCode): string
    {
        $code = trim((string) ($languageCode ?: config('services.kapso.template_language', 'es')));
        if ($code === '') {
            return 'es';
        }

        return str_replace('-', '_', $code);
    }

    private function sendMessage(array $payload): array
    {
        $status = $this->status();
        if (! ($status['configured'] ?? false)) {
            throw new RuntimeException('Kapso no esta configurado.');
        }

        $baseUrl = rtrim((string) config('services.kapso.base_url', 'https://api.kapso.ai/meta/whatsapp/v24.0'), '/');
        $apiKey = (string) config('services.kapso.api_key');
        $phoneNumberId = (string) config('services.kapso.phone_number_id');

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'X-API-Key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->post("{$baseUrl}/{$phoneNumberId}/messages", array_merge([
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                ], $payload));
        } catch (ConnectionException|RequestException $e) {
            throw new RuntimeException('No se pudo conectar con Kapso: '.$e->getMessage(), 0, $e);
        } catch (Throwable $e) {
            throw new RuntimeException('Error inesperado al enviar mensaje por Kapso: '.$e->getMessage(), 0, $e);
        }

        if (! $response->successful()) {
            $body = $response->body();
            $jsonBody = $response->json();
            $errorMessage = 'Error Kapso: '.$response->status().' - '.$body;

            if (is_array($jsonBody) && isset($jsonBody['error'])) {
                $rawKapsoError = $jsonBody['error'];
                $kapsoError = data_get($jsonBody, 'error.message');

                if (! is_string($kapsoError) || trim($kapsoError) === '') {
                    $kapsoError = is_scalar($rawKapsoError)
                        ? (string) $rawKapsoError
                        : (json_encode($rawKapsoError, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
                }

                if (str_contains($kapsoError, '24-hour') || str_contains($kapsoError, 'window')) {
                    throw new ClosedSessionException($errorMessage, $jsonBody);
                }
            }

            throw new RuntimeException($errorMessage);
        }

        return $response->json() ?: [];
    }
}
