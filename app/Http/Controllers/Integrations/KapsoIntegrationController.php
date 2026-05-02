<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Services\Facturas\FacturaDispatchSupport;
use App\Services\Integrations\KapsoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function test(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'celularDestino' => ['required', 'string', 'max:30'],
        ]);

        $to = $this->support->normalizePhoneForWhatsApp((string) $validated['celularDestino']);
        if (!$to) {
            return response()->json([
                'message' => 'Celular invalido para prueba.',
            ], 422);
        }

        $response = $this->kapsoService->sendText($to, 'Prueba de integracion Kapso desde CRM Atlantis.');

        return response()->json([
            'message' => 'Mensaje de prueba enviado.',
            'data' => [
                'response' => $response,
                'messageId' => data_get($response, 'messages.0.id'),
            ],
        ]);
    }
}
