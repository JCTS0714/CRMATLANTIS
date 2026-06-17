<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Services\Facturas\FacturaDispatchSupport;
use App\Services\Integrations\KapsoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class KapsoIntegrationController extends Controller
{
    public function __construct(
        private readonly KapsoService $kapsoService,
        private readonly FacturaDispatchSupport $support,
    ) {}

    public function status(): JsonResponse
    {
        return response()->json($this->kapsoService->status());
    }

    public function templates(): JsonResponse
    {
        try {
            $data = $this->kapsoService->listApprovedTemplates();
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => 'No se pudieron consultar las plantillas aprobadas de Meta.',
                'error_code' => 'KAPSO_TEMPLATES_FAILED',
                'details' => [
                    'error' => $e->getMessage(),
                ],
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Error inesperado al consultar las plantillas de Meta.',
                'error_code' => 'KAPSO_TEMPLATES_UNEXPECTED',
                'details' => [
                    'error' => $e->getMessage(),
                ],
            ], 500);
        }

        return response()->json([
            'message' => 'Plantillas aprobadas cargadas correctamente.',
            'data' => $data,
        ]);
    }

    public function sendTemplateBroadcast(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'template_name' => ['required', 'string', 'max:255'],
            'language_code' => ['nullable', 'string', 'max:20'],
            'parameters' => ['nullable', 'array', 'max:20'],
            'parameters.*' => ['nullable', 'string', 'max:255'],
            'recipients' => ['required', 'array', 'min:1', 'max:50'],
            'recipients.*.id' => ['nullable'],
            'recipients.*.display_name' => ['nullable', 'string', 'max:255'],
            'recipients.*.secondary' => ['nullable', 'string', 'max:255'],
            'recipients.*.phone' => ['required', 'string', 'max:40'],
            'recipients.*.parameters' => ['nullable', 'array', 'max:20'],
            'recipients.*.parameters.*' => ['nullable', 'string', 'max:255'],
        ]);

        $templateName = trim((string) $validated['template_name']);
        $languageCode = $this->kapsoService->normalizeTemplateLanguageCode($validated['language_code'] ?? null);
        $parameters = collect($validated['parameters'] ?? [])
            ->map(fn ($value) => trim((string) $value))
            ->values()
            ->all();

        $recipientCount = count($validated['recipients']);
        $timeLimit = min(600, max(60, $recipientCount * 20));
        if (function_exists('set_time_limit')) {
            @set_time_limit($timeLimit);
        }

        $results = [];
        $sentCount = 0;
        $failedCount = 0;

        try {
            foreach ($validated['recipients'] as $recipient) {
                $normalizedPhone = $this->support->normalizePhoneForWhatsApp((string) ($recipient['phone'] ?? ''));
                $recipientParameters = collect($recipient['parameters'] ?? $parameters)
                    ->map(fn ($value) => trim((string) $value))
                    ->values()
                    ->all();

                if (! $normalizedPhone) {
                    $failedCount++;
                    $results[] = [
                        'id' => $recipient['id'] ?? null,
                        'display_name' => $recipient['display_name'] ?? null,
                        'phone' => $recipient['phone'] ?? null,
                        'status' => 'failed',
                        'error' => 'Celular invalido para WhatsApp API.',
                    ];

                    continue;
                }

                try {
                    $response = $this->kapsoService->sendTemplate(
                        $normalizedPhone,
                        $templateName,
                        $recipientParameters,
                        $languageCode
                    );
                    $sentCount++;
                    $results[] = [
                        'id' => $recipient['id'] ?? null,
                        'display_name' => $recipient['display_name'] ?? null,
                        'phone' => $normalizedPhone,
                        'status' => 'sent',
                        'message_id' => data_get($response, 'messages.0.id'),
                    ];
                } catch (RuntimeException $e) {
                    $failedCount++;
                    $results[] = [
                        'id' => $recipient['id'] ?? null,
                        'display_name' => $recipient['display_name'] ?? null,
                        'phone' => $normalizedPhone,
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ];
                }
            }
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'No se pudo completar el envio masivo de plantillas.',
                'error_code' => 'KAPSO_TEMPLATE_BROADCAST_FAILED',
                'details' => [
                    'error' => $e->getMessage(),
                    'processed' => count($results),
                    'sent' => $sentCount,
                    'failed' => $failedCount,
                ],
            ], 500);
        }

        return response()->json([
            'message' => 'Envio de plantilla Meta procesado.',
            'data' => [
                'template_name' => $templateName,
                'language_code' => $languageCode,
                'sent' => $sentCount,
                'failed' => $failedCount,
                'results' => $results,
            ],
        ]);
    }

    public function test(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'celularDestino' => ['required', 'string', 'max:30'],
        ]);

        $to = $this->support->normalizePhoneForWhatsApp((string) $validated['celularDestino']);
        if (! $to) {
            return response()->json([
                'message' => 'Celular invalido para prueba.',
            ], 422);
        }

        try {
            $response = $this->kapsoService->sendText($to, 'Prueba de integracion Kapso desde CRM Atlantis.');
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => 'No se pudo enviar la prueba por Kapso. Revisa configuración o estado del número destino.',
                'error_code' => 'KAPSO_TEST_FAILED',
                'details' => [
                    'to' => $to,
                    'error' => $e->getMessage(),
                ],
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Error inesperado al probar Kapso.',
                'error_code' => 'KAPSO_TEST_UNEXPECTED',
                'details' => [
                    'to' => $to,
                    'error' => $e->getMessage(),
                ],
            ], 500);
        }

        return response()->json([
            'message' => 'Mensaje de prueba enviado.',
            'data' => [
                'response' => $response,
                'messageId' => data_get($response, 'messages.0.id'),
            ],
        ]);
    }
}
