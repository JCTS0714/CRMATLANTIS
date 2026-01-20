<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Services\ProspectosCsvImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class LeadController extends Controller
{
    public function importProspectos(Request $request, ProspectosCsvImporter $importer): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:51200'],
        ]);

        $file = $validated['file'];
        $path = $file->getRealPath();

        if (!$path) {
            return response()->json([
                'message' => 'No se pudo leer el archivo CSV.',
            ], 422);
        }

        try {
            $result = $importer->import($path, [
                'dryRun' => false,
                'updateExisting' => false,
                'createdBy' => $request->user()?->id,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'No se pudo importar el CSV.',
            ], 422);
        }

        return response()->json([
            'message' => 'Importación completada.',
            'data' => $result,
        ]);
    }

    public function tableData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'stage_id' => ['nullable', 'integer', Rule::exists('lead_stages', 'id')],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 15);
        $query = trim((string) ($validated['q'] ?? ''));
        $stageId = $validated['stage_id'] ?? null;

        $stages = LeadStage::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'key', 'name', 'sort_order', 'is_won']);

        $stageIds = $stages->pluck('id')->values();

        $filtered = Lead::query()->whereIn('stage_id', $stageIds);

        if ($query !== '') {
            $filtered->where(function ($q) use ($query) {
                $like = '%'.$query.'%';

                $q->where('name', 'like', $like)
                    ->orWhere('company_name', 'like', $like)
                    ->orWhere('contact_name', 'like', $like)
                    ->orWhere('contact_email', 'like', $like)
                    ->orWhere('contact_phone', 'like', $like)
                    ->orWhere('document_number', 'like', $like);
            });
        }

        $countsByStage = (clone $filtered)
            ->select('stage_id', DB::raw('count(*) as count'))
            ->groupBy('stage_id')
            ->pluck('count', 'stage_id');

        $totalCount = (clone $filtered)->count();

        $listQuery = (clone $filtered);
        if ($stageId) {
            $listQuery->where('stage_id', $stageId);
        }

        $paginator = $listQuery
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        $items = collect($paginator->items());
        $stageNameById = $stages->keyBy('id')->map(fn (LeadStage $s) => $s->name);

        $items = $items->map(function (Lead $lead) use ($stageNameById) {
            $data = $lead->only([
                'id',
                'stage_id',
                'customer_id',
                'created_by',
                'name',
                'amount',
                'currency',
                'contact_name',
                'contact_phone',
                'contact_email',
                'company_name',
                'company_address',
                'document_type',
                'document_number',
                'observacion',
                'migracion',
                'won_at',
                'archived_at',
                'created_at',
                'updated_at',
            ]);

            $data['stage_name'] = $stageNameById->get($lead->stage_id);

            return $data;
        })->values();

        return response()->json([
            'data' => [
                'stages' => $stages->map(function (LeadStage $stage) use ($countsByStage) {
                    return [
                        'id' => $stage->id,
                        'key' => $stage->key,
                        'name' => $stage->name,
                        'sort_order' => $stage->sort_order,
                        'is_won' => (bool) $stage->is_won,
                        'count' => (int) ($countsByStage->get($stage->id) ?? 0),
                    ];
                })->values(),
                'total_count' => (int) $totalCount,
                'leads' => $items,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'filters' => [
                    'stage_id' => $stageId,
                    'q' => $query,
                ],
            ],
        ]);
    }

    public function boardData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:0'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        // Default: 20 leads per column
        $limit = isset($validated['limit']) ? (int) $validated['limit'] : 20;

        $start = isset($validated['date_from']) ? Carbon::parse($validated['date_from'])->startOfDay() : null;
        $end = isset($validated['date_to']) ? Carbon::parse($validated['date_to'])->endOfDay() : null;

        $stages = LeadStage::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'key', 'name', 'sort_order', 'is_won']);

        $stageIds = $stages->pluck('id')->values();

        // Get counts per stage (total, ignoring per-column limit)
        $countsByStage = Lead::query()
            ->whereIn('stage_id', $stageIds)
            ->whereNull('archived_at')
            ->when($start, fn($q) => $q->where('updated_at', '>=', $start))
            ->when($end, fn($q) => $q->where('updated_at', '<=', $end))
            ->select('stage_id', DB::raw('count(*) as count'))
            ->groupBy('stage_id')
            ->pluck('count', 'stage_id');

        // For each stage retrieve up to $limit leads ordered by updated_at
        $stagesData = $stages->map(function (LeadStage $stage) use ($limit, $countsByStage, $start, $end) {
            $query = Lead::query()
                ->where('stage_id', $stage->id)
                ->whereNull('archived_at')
                ->when($start, fn($q) => $q->where('updated_at', '>=', $start))
                ->when($end, fn($q) => $q->where('updated_at', '<=', $end))
                ->orderByDesc('updated_at')
                ->take($limit)
                ->get([
                    'id',
                    'stage_id',
                    'customer_id',
                    'name',
                    'amount',
                    'currency',
                    'contact_name',
                    'contact_phone',
                    'contact_email',
                    'company_name',
                    'company_address',
                    'document_type',
                    'document_number',
                    'observacion',
                    'migracion',
                    'won_at',
                    'archived_at',
                    'updated_at',
                    'created_at',
                ]);

            return [
                'id' => $stage->id,
                'key' => $stage->key,
                'name' => $stage->name,
                'sort_order' => $stage->sort_order,
                'is_won' => (bool) $stage->is_won,
                'count' => (int) ($countsByStage->get($stage->id) ?? 0),
                'leads' => $query->values(),
            ];
        })->values();

        return response()->json([
            'data' => [
                'stages' => $stagesData,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stage_id' => ['nullable', 'integer', Rule::exists('lead_stages', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', Rule::in(['PEN', 'USD'])],

            'observacion' => ['nullable', 'string', 'max:2000'],
            'migracion' => ['nullable', 'date'],

            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'string', 'email', 'max:255'],

            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],

            'document_type' => ['nullable', 'string', Rule::in(['dni', 'ruc'])],
            'document_number' => ['nullable', 'string', 'max:20'],
        ]);

        $validator->after(function ($v) use ($request) {
            $documentType = $request->input('document_type');
            $documentNumber = $request->input('document_number');

            if ($documentNumber && !$documentType) {
                $v->errors()->add('document_type', 'El tipo de documento es requerido si envías número de documento.');
                return;
            }

            if (!$documentType) return;

            if (!$documentNumber) {
                $v->errors()->add('document_number', 'El número de documento es requerido.');
                return;
            }

            $digits = $documentType === 'dni' ? 8 : 11;
            if (!preg_match('/^\d{'.$digits.'}$/', (string) $documentNumber)) {
                $v->errors()->add('document_number', "El número de documento debe tener {$digits} dígitos.");
                return;
            }

            $existingCustomer = Customer::query()
                ->where('document_type', $documentType)
                ->where('document_number', (string) $documentNumber)
                ->exists();

            if ($existingCustomer) {
                $v->errors()->add('document_number', 'Ya existe un cliente con este documento. No se puede crear otro lead con el mismo documento.');
                return;
            }

            $existingLead = Lead::query()
                ->whereNull('archived_at')
                ->where('document_type', $documentType)
                ->where('document_number', (string) $documentNumber)
                ->exists();

            if ($existingLead) {
                $v->errors()->add('document_number', 'Ya existe un lead activo con este documento.');
            }
        });

        $validated = $validator->validate();

        $stageId = $validated['stage_id'] ?? null;
        if (!$stageId) {
            $stageId = LeadStage::query()->orderBy('sort_order')->orderBy('id')->value('id');
        }

        if (!$stageId) {
            return response()->json([
                'message' => 'No hay etapas de leads configuradas. Ejecuta las migraciones/seeders (LeadStagesSeeder) y vuelve a intentar.',
            ], 422);
        }

        try {
            $lead = Lead::create([
                'stage_id' => (int) $stageId,
                'created_by' => $request->user()?->id,
                'name' => $validated['name'],
                'amount' => $validated['amount'] ?? null,
                'currency' => $validated['currency'] ?? 'PEN',
                'observacion' => $validated['observacion'] ?? null,
                'migracion' => $validated['migracion'] ?? null,
                'contact_name' => $validated['contact_name'] ?? null,
                'contact_phone' => $validated['contact_phone'] ?? null,
                'contact_email' => $validated['contact_email'] ?? null,
                'company_name' => $validated['company_name'] ?? null,
                'company_address' => $validated['company_address'] ?? null,
                'document_type' => $validated['document_type'] ?? null,
                'document_number' => $validated['document_number'] ?? null,
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'No se pudo crear el lead. Revisa la configuración de la base de datos (migraciones/seeders y permisos de INSERT).',
            ], 422);
        }

        return response()->json([
            'message' => 'Lead creado.',
            'data' => $lead,
        ], 201);
    }

    public function moveStage(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'stage_id' => ['required', 'integer', Rule::exists('lead_stages', 'id')],
        ]);

        if ($lead->archived_at) {
            return response()->json([
                'message' => 'Este lead está archivado y no se puede mover de etapa.',
            ], 422);
        }

        $currentStageIsWon = (bool) LeadStage::query()->whereKey($lead->stage_id)->value('is_won');
        if ($lead->won_at || $currentStageIsWon) {
            return response()->json([
                'message' => 'Este lead ya está GANADO y no se puede mover de columna. Solo se puede archivar.',
            ], 422);
        }

        $targetStage = LeadStage::query()->findOrFail($validated['stage_id']);

        $updatedLead = DB::transaction(function () use ($lead, $targetStage) {
            $lead->stage_id = $targetStage->id;

            if ($targetStage->is_won) {
                $lead->won_at = $lead->won_at ?? now();
                $this->convertLeadToCustomer($lead);
            }

            $lead->save();

            return $lead->fresh();
        });

        return response()->json([
            'message' => 'Lead actualizado.',
            'data' => $updatedLead,
        ]);
    }

    public function update(Request $request, Lead $lead): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', Rule::in(['PEN', 'USD'])],

            'observacion' => ['nullable', 'string', 'max:2000'],
            'migracion' => ['nullable', 'date'],

            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'string', 'email', 'max:255'],

            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],

            'document_type' => ['nullable', 'string', Rule::in(['dni', 'ruc'])],
            'document_number' => ['nullable', 'string', 'max:20'],
        ]);

        $validator->after(function ($v) use ($request, $lead) {
            $documentType = $request->input('document_type');
            $documentNumber = $request->input('document_number');

            if ($documentNumber && !$documentType) {
                $v->errors()->add('document_type', 'El tipo de documento es requerido si envías número de documento.');
                return;
            }

            if (!$documentType) return;

            if (!$documentNumber) {
                $v->errors()->add('document_number', 'El número de documento es requerido.');
                return;
            }

            $digits = $documentType === 'dni' ? 8 : 11;
            if (!preg_match('/^\d{'.$digits.'}$/', (string) $documentNumber)) {
                $v->errors()->add('document_number', "El número de documento debe tener {$digits} dígitos.");
                return;
            }

            $existingCustomer = Customer::query()
                ->where('document_type', $documentType)
                ->where('document_number', (string) $documentNumber)
                ->first();

            if ($existingCustomer && $existingCustomer->id !== $lead->customer_id) {
                $v->errors()->add('document_number', 'Ya existe un cliente con este documento.');
                return;
            }

            $existingLead = Lead::query()
                ->whereNull('archived_at')
                ->where('document_type', $documentType)
                ->where('document_number', (string) $documentNumber)
                ->where('id', '<>', $lead->id)
                ->exists();

            if ($existingLead) {
                $v->errors()->add('document_number', 'Ya existe otro lead activo con este documento.');
            }
        });

        $validated = $validator->validate();

        if ($lead->archived_at) {
            return response()->json([
                'message' => 'No se puede editar un lead archivado.'
            ], 422);
        }

        $lead->name = $validated['name'];
        $lead->amount = $validated['amount'] ?? null;
        $lead->currency = $validated['currency'] ?? $lead->currency;

        if (array_key_exists('observacion', $validated)) {
            $lead->observacion = $validated['observacion'] ?? null;
        }

        if (array_key_exists('migracion', $validated)) {
            $lead->migracion = $validated['migracion'] ?? null;
        }

        $lead->contact_name = $validated['contact_name'] ?? null;
        $lead->contact_phone = $validated['contact_phone'] ?? null;
        $lead->contact_email = $validated['contact_email'] ?? null;
        $lead->company_name = $validated['company_name'] ?? null;
        $lead->company_address = $validated['company_address'] ?? null;
        $lead->document_type = $validated['document_type'] ?? null;
        $lead->document_number = $validated['document_number'] ?? null;

        $lead->save();

        return response()->json([
            'message' => 'Lead actualizado.',
            'data' => $lead->fresh(),
        ]);
    }

    private function convertLeadToCustomer(Lead $lead): void
    {
        if ($lead->customer_id) return;

        $documentType = $lead->document_type;
        $documentNumber = $lead->document_number;

        $email = $lead->contact_email;

        $payload = [
            'name' => $lead->name,
            'contact_name' => $lead->contact_name,
            'contact_phone' => $lead->contact_phone,
            'contact_email' => $lead->contact_email,
            'company_name' => $lead->company_name,
            'company_address' => $lead->company_address,
            'document_type' => $documentType,
            'document_number' => $documentNumber,
        ];

        $customer = null;

        // Prefer matching by document (strong identifier)
        if ($documentType && $documentNumber) {
            $customer = Customer::query()
                ->where('document_type', $documentType)
                ->where('document_number', $documentNumber)
                ->first();
        }

        // Fallback match by email if no document
        if (!$customer && $email) {
            $customer = Customer::query()
                ->where('contact_email', $email)
                ->first();
        }

        if (!$customer) {
            $customer = Customer::query()->create($payload);
        } else {
            // Enrich existing customer with missing fields from lead (do not overwrite filled data)
            $updates = [];
            foreach ($payload as $k => $v) {
                if ($v === null || $v === '') continue;
                $current = $customer->{$k} ?? null;
                if ($current === null || $current === '') {
                    $updates[$k] = $v;
                }
            }

            if (!empty($updates)) {
                $customer->fill($updates);
                $customer->save();
            }
        }

        $lead->customer_id = $customer->id;
    }
}
