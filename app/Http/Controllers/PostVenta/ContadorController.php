<?php

namespace App\Http\Controllers\PostVenta;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Contador;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContadorController extends Controller
{
    private const SERVIDOR_OPTIONS = ['ATLANTIS ONLINE', 'ATLANTIS VIP', 'ATLANTIS POS', 'ATLANTIS FAST', 'LORITO'];
    private const ESTADO_EMPRESA_OPTIONS = ['activo', 'retirado', 'eliminado'];

    public function data(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(5, min(100, $perPage));
        $servidor = trim((string) $request->query('servidor', ''));
        $estadoEmpresa = trim((string) $request->query('estado_empresa', ''));
        $hasUsuario = trim((string) $request->query('has_usuario', ''));
        $hasTelefono = trim((string) $request->query('has_telefono', ''));
        $hasLink = trim((string) $request->query('has_link', ''));

        $query = Contador::query()->with(['customers:id,name,company_name,document_number,estado']);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nro', 'like', "%{$q}%")
                    ->orWhere('comercio', 'like', "%{$q}%")
                    ->orWhere('nom_contador', 'like', "%{$q}%")
                    ->orWhere('titular_tlf', 'like', "%{$q}%")
                    ->orWhere('telefono', 'like', "%{$q}%")
                    ->orWhere('usuario', 'like', "%{$q}%")
                    ->orWhere('servidor', 'like', "%{$q}%");
            });
        }

        if (in_array($servidor, self::SERVIDOR_OPTIONS, true)) {
            $query->where('servidor', $servidor);
        }

        if (in_array($estadoEmpresa, self::ESTADO_EMPRESA_OPTIONS, true)) {
            $this->applyEstadoEmpresaFilter($query, $estadoEmpresa);
        }

        $this->applyPresenceFilter($query, 'usuario', $hasUsuario);
        $this->applyPresenceFilter($query, 'telefono', $hasTelefono);
        $this->applyPresenceFilter($query, 'link', $hasLink);

        $rows = $query
            ->orderByDesc('id')
            ->paginate($perPage)
            ->appends($request->query());

        return response()->json([
            'contadores' => collect($rows->items())->map(function (Contador $c) {
                $customer = $c->customers->first();
                $estadoEmpresa = $customer?->estado;

                if (!$estadoEmpresa) {
                    $comercio = trim((string) ($c->comercio ?? ''));
                    if ($comercio !== '') {
                        $fallbackCustomer = Customer::query()
                            ->select(['id', 'name', 'company_name', 'document_number', 'estado'])
                            ->where('name', $comercio)
                            ->orWhere('company_name', $comercio)
                            ->first();

                        if ($fallbackCustomer) {
                            $customer = $fallbackCustomer;
                            $estadoEmpresa = $fallbackCustomer->estado;
                        }
                    }
                }

                $estadoEmpresa = $estadoEmpresa ?: 'activo';
                $customers = $c->customers
                    ->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'company_name' => $item->company_name,
                        'document_number' => $item->document_number,
                        'estado' => $item->estado,
                    ])
                    ->values();

                return [
                    'id' => $c->id,
                    'nro' => $c->nro,
                    'comercio' => $c->comercio,
                    'nom_contador' => $c->nom_contador,
                    'titular_tlf' => $c->titular_tlf,
                    'telefono' => $c->telefono,
                    'telefono_actu' => $c->telefono_actu,
                    'link' => $c->link,
                    'usuario' => $c->usuario,
                    'contrasena' => $c->contrasena,
                    'servidor' => $c->servidor,
                    'estado_empresa' => $estadoEmpresa,
                    'customer' => $customer
                        ? [
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'company_name' => $customer->company_name,
                            'document_number' => $customer->document_number,
                            'estado' => $customer->estado,
                        ]
                        : null,
                    'customers' => $customers,
                ];
            })->values(),
            'pagination' => [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
                'from' => $rows->firstItem(),
                'to' => $rows->lastItem(),
            ],
            'filters' => [
                'q' => $q,
                'per_page' => $perPage,
                'servidor' => $servidor,
                'estado_empresa' => $estadoEmpresa,
                'has_usuario' => $hasUsuario,
                'has_telefono' => $hasTelefono,
                'has_link' => $hasLink,
            ],
        ]);
    }

    private function applyEstadoEmpresaFilter(Builder $query, string $estadoEmpresa): void
    {
        $query->where(function (Builder $subQuery) use ($estadoEmpresa) {
            $subQuery->whereHas('customers', function (Builder $customerQuery) use ($estadoEmpresa) {
                $customerQuery->where(function (Builder $statusQuery) use ($estadoEmpresa) {
                    $statusQuery->where('estado', $estadoEmpresa);

                    if ($estadoEmpresa === 'activo') {
                        $statusQuery->orWhereNull('estado')
                            ->orWhere('estado', '');
                    }
                });
            })->orWhereExists(function ($customerQuery) use ($estadoEmpresa) {
                $customerQuery->select(DB::raw(1))
                    ->from('customers')
                    ->where(function ($statusQuery) use ($estadoEmpresa) {
                        $statusQuery->where('customers.estado', $estadoEmpresa);

                        if ($estadoEmpresa === 'activo') {
                            $statusQuery->orWhereNull('customers.estado')
                                ->orWhere('customers.estado', '');
                        }
                    })
                    ->where(function ($matchQuery) {
                        $matchQuery->whereColumn('customers.name', 'contadores.comercio')
                            ->orWhereColumn('customers.company_name', 'contadores.comercio');
                    });
            });

            if ($estadoEmpresa === 'activo') {
                $subQuery->orWhere(function (Builder $unknownStatusQuery) {
                    $unknownStatusQuery->whereDoesntHave('customers')
                        ->whereNotExists(function ($customerQuery) {
                            $customerQuery->select(DB::raw(1))
                                ->from('customers')
                                ->where(function ($matchQuery) {
                                    $matchQuery->whereColumn('customers.name', 'contadores.comercio')
                                        ->orWhereColumn('customers.company_name', 'contadores.comercio');
                                });
                        });
                });
            }
        });
    }

    private function applyPresenceFilter(Builder $query, string $column, string $value): void
    {
        if (!in_array($value, ['1', '0'], true)) {
            return;
        }

        if ($value === '1') {
            $query->whereNotNull($column)
                ->where($column, '!=', '');

            return;
        }

        $query->where(function (Builder $subQuery) use ($column) {
            $subQuery->whereNull($column)
                ->orWhere($column, '');
        });
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nro' => ['nullable', 'string', 'max:50'],
            'nom_contador' => ['required', 'string', 'max:255'],
            'titular_tlf' => ['required', 'string', 'max:100'],
            'telefono' => ['required', 'string', 'max:50'],
            'link' => ['required', 'string', 'max:512'],
            'usuario' => ['required', 'string', 'max:150'],
            'contrasena' => ['required', 'string', 'max:255'],
            'servidor' => ['required', 'string', 'max:50', Rule::in(self::SERVIDOR_OPTIONS)],
            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
                Rule::unique('contador_customer', 'customer_id'),
            ],
        ]);

        $contador = null;

        DB::transaction(function () use ($validated, &$contador) {
            $customerId = (int) $validated['customer_id'];
            $comercio = $this->resolveComercioFromCustomerId($customerId);

            $contador = Contador::query()->create([
                'nro' => $validated['nro'] ?? null,
                'comercio' => $comercio,
                'nom_contador' => $validated['nom_contador'] ?? null,
                'titular_tlf' => $validated['titular_tlf'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'telefono_actu' => null,
                'link' => $validated['link'] ?? null,
                'usuario' => $validated['usuario'] ?? null,
                'contrasena' => $validated['contrasena'] ?? null,
                'servidor' => $validated['servidor'] ?? null,
            ]);

            $contador->customers()->sync([
                $customerId => ['fecha_asignacion' => now()],
            ]);
        });

        if (!$contador) {
            return response()->json([
                'message' => 'No se pudo crear el contador.',
            ], 500);
        }

        $fresh = $contador->fresh()->load(['customers:id,name,company_name,document_number']);
        $customer = $fresh?->customers?->first();

        return response()->json([
            'message' => 'Contador creado.',
            'data' => [
                ...($fresh?->toArray() ?? []),
                'customer' => $customer ? [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'company_name' => $customer->company_name,
                    'document_number' => $customer->document_number,
                ] : null,
            ],
        ], 201);
    }

    public function update(Request $request, Contador $contador): JsonResponse
    {
        $validated = $request->validate([
            'nro' => ['nullable', 'string', 'max:50'],
            'nom_contador' => ['required', 'string', 'max:255'],
            'titular_tlf' => ['required', 'string', 'max:100'],
            'telefono' => ['required', 'string', 'max:50'],
            'link' => ['required', 'string', 'max:512'],
            'usuario' => ['required', 'string', 'max:150'],
            'contrasena' => ['required', 'string', 'max:255'],
            'servidor' => ['required', 'string', 'max:50', Rule::in(self::SERVIDOR_OPTIONS)],
            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
                Rule::unique('contador_customer', 'customer_id')->ignore($contador->id, 'contador_id'),
            ],
        ]);

        DB::transaction(function () use ($validated, $contador) {
            $customerId = (int) $validated['customer_id'];
            $comercio = $this->resolveComercioFromCustomerId($customerId);

            $contador->fill([
                'nro' => $validated['nro'] ?? null,
                'comercio' => $comercio,
                'nom_contador' => $validated['nom_contador'] ?? null,
                'titular_tlf' => $validated['titular_tlf'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'telefono_actu' => null,
                'link' => $validated['link'] ?? null,
                'usuario' => $validated['usuario'] ?? null,
                'contrasena' => $validated['contrasena'] ?? null,
                'servidor' => $validated['servidor'] ?? null,
            ]);
            $contador->save();

            $contador->customers()->sync([
                $customerId => ['fecha_asignacion' => now()],
            ]);
        });

        $fresh = $contador->fresh()->load(['customers:id,name,company_name,document_number']);
        $customer = $fresh?->customers?->first();

        return response()->json([
            'message' => 'Contador actualizado.',
            'data' => [
                ...($fresh?->toArray() ?? []),
                'customer' => $customer ? [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'company_name' => $customer->company_name,
                    'document_number' => $customer->document_number,
                ] : null,
            ],
        ]);
    }

    public function destroy(Request $request, Contador $contador): JsonResponse
    {
        $contador->delete();

        return response()->json([
            'message' => 'Contador eliminado.',
        ]);
    }

    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        /** @var UploadedFile $file */
        $file = $request->file('csv');
        $filename = 'contadores_import_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('imports', $filename);

        $fullPath = Storage::path($path);
        $dry = $request->boolean('dry');

        $exit = Artisan::call('import:contadores', ['file' => $fullPath, '--dry-run' => $dry]);
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

    public function clearTableLocal(Request $request): JsonResponse
    {
        if (!app()->environment('local')) {
            return response()->json([
                'message' => 'Esta acción solo está disponible en entorno local.',
            ], 403);
        }

        $deleted = Contador::query()->count();
        Contador::query()->delete();

        return response()->json([
            'message' => 'Tabla de contadores limpiada en local.',
            'deleted' => $deleted,
        ]);
    }

    private function resolveComercioFromCustomerId(int $customerId): ?string
    {
        $customer = Customer::query()
            ->select(['id', 'name', 'company_name'])
            ->find($customerId);

        if (!$customer) {
            return null;
        }

        $companyName = trim((string) ($customer->company_name ?? ''));
        if ($companyName !== '') {
            return $companyName;
        }

        $customerName = trim((string) ($customer->name ?? ''));
        return $customerName !== '' ? $customerName : null;
    }
}
