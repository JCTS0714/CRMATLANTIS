<?php

namespace App\Http\Controllers\Inbox;

use App\Http\Controllers\Controller;
use App\Models\AuditoriaEnvioFactura;
use App\Models\Customer;
use App\Models\EnvioFactura;
use App\Models\FacturaMensajePlantilla;
use App\Models\PagoMensual;
use App\Services\Facturas\FacturaDispatchSupport;
use App\Services\Integrations\BrevoService;
use App\Services\Integrations\KapsoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FacturaEnvioController extends Controller
{
    public function __construct(
        private readonly FacturaDispatchSupport $support,
        private readonly KapsoService $kapsoService,
        private readonly BrevoService $brevoService,
    ) {}

    public function pendientes(Request $request): JsonResponse
    {
        $query = PagoMensual::query()
            ->with(['cliente:id,name,company_name,contact_name,contact_phone,contact_email,precio,mes,pago_estado,mes_pagado,mes_por_pagar', 'envioFactura'])
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->orderByDesc('id');

        $estado = trim((string) $request->query('estado', ''));
        if ($estado !== '') {
            $query->where('estado', $estado);
        }

        $pagoEstado = trim((string) $request->query('pago_estado', ''));
        if ($pagoEstado !== '') {
            $query->whereHas('cliente', function ($q) use ($pagoEstado) {
                $q->where('pago_estado', $pagoEstado);
            });
        }

        $mesFilter = (int) $request->query('mes', 0);
        if ($mesFilter >= 1 && $mesFilter <= 12) {
            $query->where('mes', $mesFilter);
        }

        $perPage = max(10, min((int) $request->query('per_page', 30), 100));
        $rows = $query->paginate($perPage);

        return response()->json([
            'data' => $rows->through(function (PagoMensual $pago) {
                $cliente = $pago->cliente;
                $envio = $pago->envioFactura;

                return [
                    'id' => $pago->id,
                    'mes' => $pago->mes,
                    'anio' => $pago->anio,
                    'estado' => $pago->estado,
                    'cliente' => [
                        'id' => $cliente?->id,
                        'name' => $cliente?->name,
                        'company_name' => $cliente?->company_name,
                        'contact_name' => $cliente?->contact_name,
                        'contact_phone' => $cliente?->contact_phone,
                        'contact_email' => $cliente?->contact_email,
                        'precio' => $cliente?->precio,
                        'mes' => $cliente?->mes,
                        'pago_estado' => $cliente?->pago_estado,
                        'mes_pagado' => $cliente?->mes_pagado,
                        'mes_por_pagar' => $cliente?->mes_por_pagar,
                    ],
                    'envio' => [
                        'estado' => $envio?->estado,
                        'archivoUrl' => $envio?->archivo_url,
                        'mensaje' => $envio?->mensaje,
                        'fechaPreparado' => $envio?->fecha_preparado,
                        'fechaEnviado' => $envio?->fecha_enviado,
                    ],
                ];
            }),
            'meta' => [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
            ],
        ]);
    }

    public function plantillas(): JsonResponse
    {
        $rows = FacturaMensajePlantilla::query()
            ->orderByDesc('is_default')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'data' => $rows->map(function (FacturaMensajePlantilla $row) {
                return [
                    'id' => $row->id,
                    'nombre' => $row->nombre,
                    'contenido' => $row->contenido,
                    'is_default' => (bool) $row->is_default,
                    'created_by' => $row->created_by,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            })->values(),
        ]);
    }

    public function crearPlantilla(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'contenido' => ['required', 'string', 'max:4000'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $isDefault = (bool) ($validated['is_default'] ?? false);
        if (!$isDefault && !FacturaMensajePlantilla::query()->exists()) {
            $isDefault = true;
        }

        if ($isDefault) {
            FacturaMensajePlantilla::query()->update(['is_default' => false]);
        }

        $template = FacturaMensajePlantilla::query()->create([
            'nombre' => trim((string) $validated['nombre']),
            'contenido' => trim((string) $validated['contenido']),
            'is_default' => $isDefault,
            'created_by' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'Plantilla creada correctamente.',
            'data' => [
                'id' => $template->id,
                'nombre' => $template->nombre,
                'contenido' => $template->contenido,
                'is_default' => (bool) $template->is_default,
            ],
        ], 201);
    }

    public function actualizarPlantilla(Request $request, int $plantillaId): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'contenido' => ['required', 'string', 'max:4000'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $template = FacturaMensajePlantilla::query()->findOrFail($plantillaId);
        $isDefault = (bool) ($validated['is_default'] ?? false);

        if ($isDefault) {
            FacturaMensajePlantilla::query()
                ->where('id', '!=', $template->id)
                ->update(['is_default' => false]);
        }

        $template->fill([
            'nombre' => trim((string) $validated['nombre']),
            'contenido' => trim((string) $validated['contenido']),
            'is_default' => $isDefault,
        ]);
        $template->save();

        return response()->json([
            'message' => 'Plantilla actualizada correctamente.',
            'data' => [
                'id' => $template->id,
                'nombre' => $template->nombre,
                'contenido' => $template->contenido,
                'is_default' => (bool) $template->is_default,
            ],
        ]);
    }

    public function eliminarPlantilla(int $plantillaId): JsonResponse
    {
        $template = FacturaMensajePlantilla::query()->findOrFail($plantillaId);
        $wasDefault = (bool) $template->is_default;

        $template->delete();

        if ($wasDefault) {
            $nextDefault = FacturaMensajePlantilla::query()->orderBy('id')->first();
            if ($nextDefault) {
                $nextDefault->is_default = true;
                $nextDefault->save();
            }
        }

        return response()->json([
            'message' => 'Plantilla eliminada correctamente.',
        ]);
    }

    public function syncMesActual(): JsonResponse
    {
        $mes = (int) now()->format('m');
        $anio = (int) now()->format('Y');

        $customers = Customer::query()
            ->where('estado', '!=', 'eliminado')
            ->where(function ($query) {
                $query->whereNull('pago_estado')
                    ->orWhere('pago_estado', '!=', 'inactivo');
            })
            ->select(['id', 'mes', 'pago_estado', 'mes_por_pagar'])
            ->get()
            ->filter(function (Customer $customer) use ($mes) {
                return $this->customerAppliesToMonth($customer->mes_por_pagar, $customer->mes, $mes);
            });

        $created = 0;
        foreach ($customers as $customer) {
            $createdModel = PagoMensual::query()->firstOrCreate([
                'cliente_id' => $customer->id,
                'mes' => $mes,
                'anio' => $anio,
            ], [
                'estado' => $this->customerPaymentStateToPagoMensualState($customer->pago_estado),
            ]);

            if ($createdModel->wasRecentlyCreated) {
                $created++;
            }
        }

        return response()->json([
            'message' => 'Pagos mensuales sincronizados para el mes actual.',
            'data' => [
                'mes' => $mes,
                'anio' => $anio,
                'created' => $created,
                'customers_considered' => $customers->count(),
            ],
        ]);
    }

    private function customerAppliesToMonth(?int $mesPorPagar, ?string $mesConfig, int $currentMonth): bool
    {
        if (is_int($mesPorPagar) && $mesPorPagar >= 1 && $mesPorPagar <= 12) {
            return $mesPorPagar === $currentMonth;
        }

        $raw = trim((string) $mesConfig);
        if ($raw === '') {
            // Backward-compatible: if no month is configured, include customer every month.
            return true;
        }

        $normalized = mb_strtolower($raw);
        if (
            str_contains($normalized, 'todos')
            || str_contains($normalized, 'todo')
            || str_contains($normalized, 'mensual')
            || str_contains($normalized, 'cada mes')
            || $normalized === '*'
            || $normalized === 'all'
        ) {
            return true;
        }

        $monthMap = [
            'enero' => 1,
            'febrero' => 2,
            'marzo' => 3,
            'abril' => 4,
            'mayo' => 5,
            'junio' => 6,
            'julio' => 7,
            'agosto' => 8,
            'septiembre' => 9,
            'setiembre' => 9,
            'octubre' => 10,
            'noviembre' => 11,
            'diciembre' => 12,
        ];

        $tokens = preg_split('/[\s,;\/|+-]+/', $normalized) ?: [];
        $months = [];

        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token === '') {
                continue;
            }

            if (isset($monthMap[$token])) {
                $months[] = $monthMap[$token];
                continue;
            }

            if (ctype_digit($token)) {
                $month = (int) $token;
                if ($month >= 1 && $month <= 12) {
                    $months[] = $month;
                }
            }
        }

        if (empty($months)) {
            // If a value exists but we cannot parse it, avoid accidental exclusion.
            return true;
        }

        return in_array($currentMonth, array_values(array_unique($months)), true);
    }

    private function customerPaymentStateToPagoMensualState(?string $pagoEstado): string
    {
        return $pagoEstado === 'factura_enviada' ? 'factura_enviada' : 'factura_pendiente';
    }

    private function updateCustomerPaymentTracking(Customer $customer, int $paidMonth): void
    {
        $nextMonth = $paidMonth === 12 ? 1 : ($paidMonth + 1);

        $customer->pago_estado = 'factura_enviada';
        $customer->mes_pagado = $paidMonth;
        $customer->mes_por_pagar = $nextMonth;
        $customer->save();
    }

    public function preparar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pagoId' => ['required', 'integer', 'exists:pagos_mensuales,id'],
            'mensajeTemplate' => ['required', 'string', 'max:4000'],
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ]);

        $pago = PagoMensual::query()->with('cliente')->findOrFail((int) $validated['pagoId']);

        if (!in_array($pago->estado, ['factura_pendiente', 'factura_enviada'], true)) {
            return response()->json([
                'message' => 'El estado del pago no permite preparar factura.',
            ], 422);
        }

        $cliente = $pago->cliente;
        $expectedName = Str::of((string) ($cliente?->company_name ?: $cliente?->name ?: ''))
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();

        $originalName = Str::of(pathinfo((string) $request->file('archivo')->getClientOriginalName(), PATHINFO_FILENAME))
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();

        if ($expectedName !== '' && $originalName !== '' && !Str::contains($originalName, $expectedName)) {
            return response()->json([
                'message' => 'El nombre del archivo no coincide con el comercio/cliente.',
                'errors' => [
                    'archivo' => ['El nombre del archivo debe incluir el nombre del comercio normalizado.'],
                ],
            ], 422);
        }

        $ext = strtolower((string) $request->file('archivo')->getClientOriginalExtension());
        $storedName = sprintf('factura_pago_%d_%s.%s', $pago->id, now()->format('Ymd_His'), $ext);
        $storedPath = $request->file('archivo')->storeAs('facturas', $storedName, 'public');
        $archivoUrl = '/storage/' . ltrim($storedPath, '/');

        $vars = [
            'cliente' => $cliente?->contact_name ?: $cliente?->name ?: 'cliente',
            'comercio' => $cliente?->company_name ?: $cliente?->name ?: 'comercio',
            'mes' => $pago->mes,
            'anio' => $pago->anio,
            'precio' => $cliente?->precio,
        ];

        $mensaje = $this->support->renderMensajeTemplate((string) $validated['mensajeTemplate'], $vars);

        $envio = EnvioFactura::query()->updateOrCreate(
            ['pago_id' => $pago->id],
            [
                'archivo_url' => $archivoUrl,
                'mensaje' => $mensaje,
                'estado' => 'preparado',
                'fecha_preparado' => now(),
            ]
        );

        $baseUrl = $this->support->getBaseUrl($request);
        $celularConPais = $this->support->normalizePhoneForWhatsApp($cliente?->contact_phone);
        $kapsoStatus = $this->kapsoService->status();
        $diagnostics = $this->support->obtenerDiagnosticoWhatsapp(
            $celularConPais,
            $baseUrl,
            (bool) ($kapsoStatus['configured'] ?? false)
        );

        $facturaUrl = $this->support->buildPublicFacturaUrl($baseUrl, $envio->archivo_url ?? $archivoUrl);
        $whatsappUrl = $this->support->construirUrlWhatsAppManual($celularConPais, $mensaje);

        return response()->json([
            'message' => 'Factura preparada correctamente.',
            'data' => [
                'archivoUrl' => $envio->archivo_url,
                'facturaUrl' => $facturaUrl,
                'mensaje' => $mensaje,
                'whatsappUrl' => $whatsappUrl,
                'whatsappApiReady' => empty($diagnostics),
                'whatsappDiagnostics' => $diagnostics,
                'whatsappDelivery' => [
                    'reason' => 'prepared_only',
                ],
            ],
        ]);
    }

    public function enviarWhatsapp(Request $request, int $pagoId): JsonResponse
    {
        $pago = PagoMensual::query()->with(['cliente', 'envioFactura'])->findOrFail($pagoId);
        $cliente = $pago->cliente;
        $envio = $pago->envioFactura;

        if (!$envio || !$envio->archivo_url) {
            return response()->json([
                'message' => 'Debes preparar la factura antes de enviarla por WhatsApp.',
            ], 422);
        }

        $baseUrl = $this->support->getBaseUrl($request);
        $celularConPais = $this->support->normalizePhoneForWhatsApp($cliente?->contact_phone);
        $kapsoStatus = $this->kapsoService->status();
        $diagnostics = $this->support->obtenerDiagnosticoWhatsapp(
            $celularConPais,
            $baseUrl,
            (bool) ($kapsoStatus['configured'] ?? false)
        );

        $whatsappUrl = $this->support->construirUrlWhatsAppManual($celularConPais, (string) $envio->mensaje);

        if (!empty($diagnostics)) {
            return response()->json([
                'message' => 'No se puede enviar por API. Revisa diagnostico.',
                'diagnostics' => $diagnostics,
                'whatsappUrl' => $whatsappUrl,
            ], 400);
        }

        $facturaUrl = $this->support->buildPublicFacturaUrl($baseUrl, (string) $envio->archivo_url);
        $filename = basename(parse_url($facturaUrl, PHP_URL_PATH) ?: ('factura_' . $pago->id . '.pdf'));

        $kapsoResponse = $this->kapsoService->sendDocument(
            (string) $celularConPais,
            $facturaUrl,
            (string) $envio->mensaje,
            $filename,
            'pago_' . $pago->id
        );

        $messageId = data_get($kapsoResponse, 'messages.0.id');

        DB::transaction(function () use ($pago, $envio, $messageId, $kapsoResponse, $request) {
            $envio->estado = 'enviado';
            $envio->fecha_enviado = now();
            $envio->canal_envio = 'whatsapp_api';
            $envio->message_id = is_string($messageId) ? $messageId : null;
            $envio->save();

            if ($pago->estado === 'factura_pendiente') {
                $pago->estado = 'factura_enviada';
                $pago->save();
            }

            if ($pago->cliente) {
                $this->updateCustomerPaymentTracking($pago->cliente, (int) $pago->mes);
            }

            AuditoriaEnvioFactura::query()->create([
                'accion' => 'ENVIO_WHATSAPP_KAPSO',
                'pago_id' => $pago->id,
                'cliente_id' => $pago->cliente_id,
                'usuario_id' => $request->user()?->id,
                'detalles' => [
                    'messageId' => $messageId,
                    'response' => $kapsoResponse,
                ],
                'created_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Factura enviada por WhatsApp API.',
            'data' => [
                'messageId' => $messageId,
                'facturaUrl' => $facturaUrl,
            ],
        ]);
    }

    public function marcarEnviada(Request $request, int $pagoId): JsonResponse
    {
        $pago = PagoMensual::query()->with('envioFactura')->findOrFail($pagoId);
        $envio = $pago->envioFactura;

        if (!$envio) {
            return response()->json([
                'message' => 'No existe una factura preparada para este pago.',
            ], 422);
        }

        DB::transaction(function () use ($pago, $envio, $request) {
            $envio->estado = 'enviado';
            $envio->fecha_enviado = now();
            $envio->canal_envio = 'manual';
            $envio->save();

            if ($pago->estado === 'factura_pendiente') {
                $pago->estado = 'factura_enviada';
                $pago->save();
            }

            if ($pago->cliente) {
                $this->updateCustomerPaymentTracking($pago->cliente, (int) $pago->mes);
            }

            AuditoriaEnvioFactura::query()->create([
                'accion' => 'ENVIO_MANUAL_MARCADO',
                'pago_id' => $pago->id,
                'cliente_id' => $pago->cliente_id,
                'usuario_id' => $request->user()?->id,
                'detalles' => ['canal' => 'manual'],
                'created_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Factura marcada como enviada manualmente.',
        ]);
    }

    public function enviarEmail(Request $request, int $pagoId): JsonResponse
    {
        $pago = PagoMensual::query()->with(['cliente', 'envioFactura'])->findOrFail($pagoId);
        $cliente = $pago->cliente;
        $envio = $pago->envioFactura;

        if (!$envio || !$envio->archivo_url) {
            return response()->json([
                'message' => 'Debes preparar la factura antes de enviar email.',
            ], 422);
        }

        $email = trim((string) ($cliente?->contact_email ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'message' => 'El cliente no tiene un email valido.',
            ], 422);
        }

        $baseUrl = $this->support->getBaseUrl($request);
        $facturaUrl = $this->support->buildPublicFacturaUrl($baseUrl, (string) $envio->archivo_url);

        $response = $this->brevoService->sendInvoiceLink(
            $email,
            (string) ($cliente?->contact_name ?: $cliente?->name ?: ''),
            'Factura ' . $pago->mes . '/' . $pago->anio,
            $facturaUrl,
            (string) ($envio->mensaje ?: 'Te compartimos tu factura.'),
        );

        $messageId = data_get($response, 'messageId');

        DB::transaction(function () use ($pago, $envio, $messageId, $response, $request) {
            $envio->estado = 'enviado';
            $envio->fecha_enviado = now();
            $envio->canal_envio = 'email';
            $envio->message_id = is_string($messageId) ? $messageId : null;
            $envio->save();

            if ($pago->estado === 'factura_pendiente') {
                $pago->estado = 'factura_enviada';
                $pago->save();
            }

            if ($pago->cliente) {
                $this->updateCustomerPaymentTracking($pago->cliente, (int) $pago->mes);
            }

            AuditoriaEnvioFactura::query()->create([
                'accion' => 'ENVIO_EMAIL_BREVO',
                'pago_id' => $pago->id,
                'cliente_id' => $pago->cliente_id,
                'usuario_id' => $request->user()?->id,
                'detalles' => [
                    'messageId' => $messageId,
                    'response' => $response,
                ],
                'created_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Factura enviada por email.',
            'data' => [
                'messageId' => $messageId,
                'facturaUrl' => $facturaUrl,
            ],
        ]);
    }

    public function updateCliente(Request $request, int $clienteId): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'precio' => ['nullable', 'numeric', 'min:0'],
            'pago_estado' => ['nullable', 'in:pendiente,factura_enviada,inactivo'],
            'mes_pagado' => ['nullable', 'integer', 'between:1,12'],
            'mes_por_pagar' => ['nullable', 'integer', 'between:1,12'],
            'mes' => ['nullable', 'string', 'max:40'],
        ]);

        $cliente = Customer::query()->findOrFail($clienteId);

        $payload = [
            'name' => $validated['name'] ?? $cliente->name,
            'company_name' => $validated['company_name'] ?? $cliente->company_name,
            'contact_name' => $validated['contact_name'] ?? $cliente->contact_name,
            'contact_phone' => $validated['contact_phone'] ?? $cliente->contact_phone,
            'contact_email' => $validated['contact_email'] ?? $cliente->contact_email,
            'precio' => $validated['precio'] ?? $cliente->precio,
            'pago_estado' => $validated['pago_estado'] ?? $cliente->pago_estado,
            'mes_pagado' => $validated['mes_pagado'] ?? $cliente->mes_pagado,
            'mes_por_pagar' => $validated['mes_por_pagar'] ?? $cliente->mes_por_pagar,
            'mes' => $validated['mes'] ?? $cliente->mes,
        ];

        $cliente->fill($payload);
        $cliente->save();

        return response()->json([
            'message' => 'Cliente actualizado correctamente.',
            'data' => [
                'id' => $cliente->id,
                'name' => $cliente->name,
                'company_name' => $cliente->company_name,
                'contact_name' => $cliente->contact_name,
                'contact_phone' => $cliente->contact_phone,
                'contact_email' => $cliente->contact_email,
                'precio' => $cliente->precio,
                'mes' => $cliente->mes,
                'pago_estado' => $cliente->pago_estado,
                'mes_pagado' => $cliente->mes_pagado,
                'mes_por_pagar' => $cliente->mes_por_pagar,
            ],
        ]);
    }
}
