<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Incidence;
use App\Models\IncidenceStage;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IncidenceController extends Controller
{
    public function tableData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'stage_id' => ['nullable', 'integer', Rule::exists('incidence_stages', 'id')],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 15);
        $query = trim((string) ($validated['q'] ?? ''));
        $stageId = $validated['stage_id'] ?? null;

        $stages = IncidenceStage::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'key', 'name', 'sort_order', 'is_done']);

        $stageIds = $stages->pluck('id')->values();

        $filtered = Incidence::query()
            ->with(['customer:id,name,company_name'])
            ->whereIn('stage_id', $stageIds);

        if ($query !== '') {
            $filtered->where(function ($q) use ($query) {
                $like = '%'.$query.'%';
                $q->where('title', 'like', $like)
                    ->orWhere('correlative', 'like', $like)
                    ->orWhereHas('customer', function ($c) use ($like) {
                        $c->where('name', 'like', $like)
                            ->orWhere('company_name', 'like', $like);
                    });
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
        $stageNameById = $stages->keyBy('id')->map(fn (IncidenceStage $s) => $s->name);

        $items = $items->map(function (Incidence $incidence) use ($stageNameById) {
            $data = $incidence->only([
                'id',
                'correlative',
                'stage_id',
                'customer_id',
                'created_by',
                'title',
                'date',
                'priority',
                'notes',
                'archived_at',
                'created_at',
                'updated_at',
            ]);

            $data['stage_name'] = $stageNameById->get($incidence->stage_id);
            $data['customer'] = $incidence->customer
                ? $incidence->customer->only(['id', 'name', 'company_name'])
                : null;

            return $data;
        })->values();

        return response()->json([
            'data' => [
                'stages' => $stages->map(function (IncidenceStage $stage) use ($countsByStage) {
                    return [
                        'id' => $stage->id,
                        'key' => $stage->key,
                        'name' => $stage->name,
                        'sort_order' => $stage->sort_order,
                        'is_done' => (bool) $stage->is_done,
                        'count' => (int) ($countsByStage->get($stage->id) ?? 0),
                    ];
                })->values(),
                'total_count' => (int) $totalCount,
                'incidences' => $items,
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
        $stages = IncidenceStage::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'key', 'name', 'sort_order', 'is_done']);

        $stageIds = $stages->pluck('id')->values();

        $items = Incidence::query()
            ->with(['customer:id,name,company_name'])
            ->whereIn('stage_id', $stageIds)
            ->whereNull('archived_at')
            ->orderByDesc('updated_at')
            ->get([
                'id',
                'correlative',
                'stage_id',
                'customer_id',
                'created_by',
                'title',
                'date',
                'priority',
                'notes',
                'archived_at',
                'updated_at',
                'created_at',
            ])
            ->groupBy('stage_id');

        return response()->json([
            'data' => [
                'stages' => $stages->map(function (IncidenceStage $stage) use ($items) {
                    $list = $items->get($stage->id, collect())->values();

                    $mapped = $list->map(function (Incidence $incidence) {
                        $data = $incidence->only([
                            'id',
                            'correlative',
                            'stage_id',
                            'customer_id',
                            'created_by',
                            'title',
                            'date',
                            'priority',
                            'notes',
                            'archived_at',
                            'updated_at',
                            'created_at',
                        ]);

                        $data['customer'] = $incidence->customer
                            ? $incidence->customer->only(['id', 'name', 'company_name'])
                            : null;

                        return $data;
                    })->values();

                    return [
                        'id' => $stage->id,
                        'key' => $stage->key,
                        'name' => $stage->name,
                        'sort_order' => $stage->sort_order,
                        'is_done' => (bool) $stage->is_done,
                        'count' => $mapped->count(),
                        'incidences' => $mapped,
                    ];
                })->values(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stage_id' => ['nullable', 'integer', Rule::exists('incidence_stages', 'id')],
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'priority' => ['nullable', 'string', Rule::in(['alta', 'media', 'baja'])],
            'notes' => ['nullable', 'string'],
        ]);

        $validated = $validator->validate();

        $stageId = $validated['stage_id'] ?? null;
        if (!$stageId) {
                $stageId = IncidenceStage::query()->orderBy('sort_order')->orderBy('id')->value('id');
        }

            if (!$stageId) {
                return response()->json([
                    'message' => 'No hay etapas de incidencias configuradas. Ejecuta las migraciones/seeders (IncidenceStagesSeeder) y vuelve a intentar.',
                ], 422);
            }

            try {
                $incidence = DB::transaction(function () use ($request, $validated, $stageId) {
                    $created = Incidence::create([
                        'correlative' => null,
                        'stage_id' => (int) $stageId,
                        'customer_id' => $validated['customer_id'] ?? null,
                        'created_by' => $request->user()?->id,
                        'title' => $validated['title'],
                        'date' => $validated['date'] ?? null,
                        'priority' => $validated['priority'] ?? 'media',
                        'notes' => $validated['notes'] ?? null,
                        'archived_at' => null,
                    ]);

                    // Generate a correlativo similar to the legacy system if not provided.
                    $created->correlative = 'INC-'.str_pad((string) $created->id, 6, '0', STR_PAD_LEFT);
                    $created->save();

                    return $created->fresh(['customer:id,name,company_name']);
                });
            } catch (QueryException $e) {
                return response()->json([
                    'message' => 'No se pudo crear la incidencia. Revisa la configuración de la base de datos (migraciones/seeders y permisos de INSERT).',
                ], 422);
            }

        return response()->json([
            'message' => 'Incidencia creada.',
            'data' => $incidence,
        ], 201);
    }

    public function update(Request $request, Incidence $incidence): JsonResponse
    {
        if ($incidence->archived_at) {
            return response()->json([
                'message' => 'No se puede editar una incidencia archivada.',
            ], 422);
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'priority' => ['nullable', 'string', Rule::in(['alta', 'media', 'baja'])],
            'notes' => ['nullable', 'string'],
        ]);

        $incidence->customer_id = $validated['customer_id'] ?? null;
        $incidence->title = $validated['title'];
        $incidence->date = $validated['date'] ?? null;
        $incidence->priority = $validated['priority'] ?? 'media';
        $incidence->notes = $validated['notes'] ?? null;

        try {
            $incidence->save();
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'No se pudo actualizar la incidencia. Revisa la configuración de la base de datos.',
            ], 422);
        }

        return response()->json([
            'message' => 'Incidencia actualizada.',
            'data' => $incidence->fresh(['customer:id,name,company_name']),
        ]);
    }

    public function moveStage(Request $request, Incidence $incidence): JsonResponse
    {
        $validated = $request->validate([
            'stage_id' => ['required', 'integer', Rule::exists('incidence_stages', 'id')],
        ]);

        if ($incidence->archived_at) {
            return response()->json([
                'message' => 'Esta incidencia está archivada y no se puede mover de columna.',
            ], 422);
        }

        $targetStage = IncidenceStage::query()->findOrFail($validated['stage_id']);

        $updated = DB::transaction(function () use ($incidence, $targetStage) {
            $incidence->stage_id = $targetStage->id;
            $incidence->save();
            return $incidence->fresh(['customer:id,name,company_name']);
        });

        return response()->json([
            'message' => 'Incidencia actualizada.',
            'data' => $updated,
        ]);
    }

    public function archive(Request $request, Incidence $incidence): JsonResponse
    {
        if ($incidence->archived_at) {
            return response()->json([
                'message' => 'Esta incidencia ya está archivada.',
                'data' => $incidence,
            ]);
        }

        $currentStageIsDone = (bool) IncidenceStage::query()->whereKey($incidence->stage_id)->value('is_done');
        if (!$currentStageIsDone) {
            return response()->json([
                'message' => 'Solo puedes archivar incidencias en una columna final (resuelta).',
            ], 422);
        }

        $incidence->archived_at = now();
        $incidence->save();

        return response()->json([
            'message' => 'Incidencia archivada.',
            'data' => $incidence->fresh(['customer:id,name,company_name']),
        ]);
    }

    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        /** @var UploadedFile $file */
        $file = $request->file('csv');
        $filename = 'incidencias_import_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('imports', $filename);

        $fullPath = Storage::path($path);
        $dry = $request->boolean('dry');

        $exit = Artisan::call('import:incidencias', ['file' => $fullPath, '--dry-run' => $dry]);
        $output = Artisan::output();

        if ($exit !== 0) {
            return response()->json([
                'message' => trim($output) !== '' ? trim($output) : 'Error importing CSV',
                'exit' => $exit,
                'output' => $output,
                'path' => $path,
            ], 422);
        }

        return response()->json([
            'exit' => $exit,
            'output' => $output,
            'path' => $path,
        ]);
    }
}
