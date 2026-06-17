<?php

namespace App\Services\Facturas;

use Illuminate\Http\Request;

class FacturaDispatchSupport
{
    public function getBaseUrl(Request $request): string
    {
        $publicBaseUrl = trim((string) env('PUBLIC_BASE_URL', ''));
        if ($publicBaseUrl !== '') {
            return rtrim($publicBaseUrl, '/');
        }

        return rtrim($request->getSchemeAndHttpHost(), '/');
    }

    public function esBaseUrlPublica(string $baseUrl): bool
    {
        $host = parse_url($baseUrl, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return false;
        }

        $host = strtolower($host);
        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }

        return ! str_ends_with($host, '.local');
    }

    public function normalizePhoneForWhatsApp(?string $rawPhone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $rawPhone);
        if (! is_string($digits) || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 9) {
            $digits = '51'.$digits;
        }

        if (strlen($digits) < 11 || strlen($digits) > 15) {
            return null;
        }

        return $digits;
    }

    public function construirUrlWhatsAppManual(?string $celularConPais, string $mensaje): ?string
    {
        if (! $celularConPais) {
            return null;
        }

        return 'https://wa.me/'.$celularConPais.'?text='.rawurlencode($mensaje);
    }

    public function obtenerDiagnosticoWhatsapp(?string $celularConPais, string $baseUrl, bool $kapsoConfigured): array
    {
        $diagnostics = [];

        if (! $celularConPais) {
            $diagnostics[] = 'celular_invalido';
        }

        if (! $kapsoConfigured) {
            $diagnostics[] = 'kapso_no_configurado';
        }

        if (! $this->esBaseUrlPublica($baseUrl)) {
            $diagnostics[] = 'public_base_url_no_publica';
        }

        return $diagnostics;
    }

    public function renderMensajeTemplate(string $template, array $vars): string
    {
        $rendered = $template;
        foreach ($vars as $key => $value) {
            $rendered = str_replace('{{'.$key.'}}', (string) $value, $rendered);
        }

        return trim($rendered);
    }

    public function buildPublicFacturaUrl(string $baseUrl, string $archivoUrl): string
    {
        if (preg_match('/^https?:\/\//i', $archivoUrl)) {
            return $archivoUrl;
        }

        return rtrim($baseUrl, '/').'/'.ltrim($archivoUrl, '/');
    }
}
