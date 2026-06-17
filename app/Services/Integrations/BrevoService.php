<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class BrevoService
{
    public function isConfigured(): bool
    {
        return filter_var(config('services.brevo.enabled', false), FILTER_VALIDATE_BOOL)
            && trim((string) config('services.brevo.api_key', '')) !== ''
            && trim((string) config('services.brevo.sender_email', '')) !== '';
    }

    public function sendInvoiceLink(string $email, string $name, string $subject, string $facturaUrl, string $mensaje): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Brevo no esta configurado.');
        }

        $apiKey = (string) config('services.brevo.api_key');
        $senderEmail = (string) config('services.brevo.sender_email');
        $senderName = (string) config('services.brevo.sender_name', 'CRM Atlantis');

        $safeMessage = nl2br(e($mensaje));
        $safeUrl = e($facturaUrl);
        $safeName = e($name !== '' ? $name : 'cliente');

        $html = "<p>Hola {$safeName},</p><p>{$safeMessage}</p><p><a href=\"{$safeUrl}\" target=\"_blank\" rel=\"noopener\">Descargar factura</a></p>";

        $response = Http::timeout(25)
            ->withHeaders([
                'api-key' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => $senderName,
                    'email' => $senderEmail,
                ],
                'to' => [[
                    'email' => $email,
                    'name' => $name,
                ]],
                'subject' => $subject,
                'htmlContent' => $html,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Error Brevo: '.$response->status().' - '.$response->body());
        }

        return $response->json() ?: [];
    }
}
