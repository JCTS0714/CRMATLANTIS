<?php

namespace App\Http\Controllers\PostVenta;

use App\Models\Contador;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class ContadorController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(5, min(100, $perPage));

        $query = Contador::query()->with(['customers:id,name']);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nro', 'like', "%{$q}%")
                    ->orWhere('comercio', 'like', "%{$q}%")
                    ->orWhere('nom_contador', 'like', "%{$q}%")
                    ->orWhere('usuario', 'like', "%{$q}%")
                    ->orWhere('servidor', 'like', "%{$q}%");
            });
        }

        $rows = $query
            ->orderByDesc('id')
            ->paginate($perPage)
            ->appends($request->query());

        return response()->json([
            'contadores' => collect($rows->items())->map(function (Contador $c) {
                $customer = $c->customers->first();
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
                    'customer' => $customer ? ['id' => $customer->id, 'name' => $customer->name] : null,
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
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nro' => ['nullable', 'string', 'max:50'],
            'comercio' => ['nullable', 'string', 'max:255'],
            'nom_contador' => ['nullable', 'string', 'max:255'],
            'titular_tlf' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'telefono_actu' => ['nullable', 'string', 'max:50'],
            'link' => ['nullable', 'string', 'max:512'],
            'usuario' => ['nullable', 'string', 'max:150'],
            'contrasena' => ['nullable', 'string', 'max:255'],
            'servidor' => ['nullable', 'string', 'max:50'],
            'customer_ids' => ['nullable', 'array'],
            'customer_ids.*' => ['integer', 'exists:customers,id'],
        ]);

        $contador = null;

        DB::transaction(function () use ($validated, &$contador) {
            $contador = Contador::query()->create([
                'nro' => $validated['nro'] ?? null,
                'comercio' => $validated['comercio'] ?? null,
                'nom_contador' => $validated['nom_contador'] ?? null,
                'titular_tlf' => $validated['titular_tlf'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'telefono_actu' => $validated['telefono_actu'] ?? null,
                'link' => $validated['link'] ?? null,
                'usuario' => $validated['usuario'] ?? null,
                'contrasena' => $validated['contrasena'] ?? null,
                'servidor' => $validated['servidor'] ?? null,
            ]);

            $customerIds = $validated['customer_ids'] ?? [];
            if (!empty($customerIds)) {
                $syncData = [];
                foreach ($customerIds as $customerId) {
                    $syncData[$customerId] = ['fecha_asignacion' => now()];
                }
                $contador->customers()->sync($syncData);
            }
        });

        if (!$contador) {
            return response()->json([
                'message' => 'No se pudo crear el contador.',
            ], 500);
        }

        return response()->json([
            'message' => 'Contador creado.',
            'data' => $contador->fresh()->load(['customers:id,name']),
        ], 201);
    }

    public function update(Request $request, Contador $contador): JsonResponse
    {
        $validated = $request->validate([
            'nro' => ['nullable', 'string', 'max:50'],
            'comercio' => ['nullable', 'string', 'max:255'],
            'nom_contador' => ['nullable', 'string', 'max:255'],
            'titular_tlf' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'telefono_actu' => ['nullable', 'string', 'max:50'],
            'link' => ['nullable', 'string', 'max:512'],
            'usuario' => ['nullable', 'string', 'max:150'],
            'contrasena' => ['nullable', 'string', 'max:255'],
            'servidor' => ['nullable', 'string', 'max:50'],
            'customer_ids' => ['nullable', 'array'],
            'customer_ids.*' => ['integer', 'exists:customers,id'],
        ]);

        DB::transaction(function () use ($validated, $contador) {
            $contador->fill([
                'nro' => $validated['nro'] ?? null,
                'comercio' => $validated['comercio'] ?? null,
                'nom_contador' => $validated['nom_contador'] ?? null,
                'titular_tlf' => $validated['titular_tlf'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'telefono_actu' => $validated['telefono_actu'] ?? null,
                'link' => $validated['link'] ?? null,
                'usuario' => $validated['usuario'] ?? null,
                'contrasena' => $validated['contrasena'] ?? null,
                'servidor' => $validated['servidor'] ?? null,
            ]);
            $contador->save();

            $customerIds = $validated['customer_ids'] ?? [];
            if (!empty($customerIds)) {
                $syncData = [];
                foreach ($customerIds as $customerId) {
                    $syncData[$customerId] = ['fecha_asignacion' => now()];
                }
                $contador->customers()->sync($syncData);
            } else {
                $contador->customers()->detach();
            }
        });

        return response()->json([
            'message' => 'Contador actualizado.',
            'data' => $contador->fresh()->load(['customers:id,name']),
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
}
