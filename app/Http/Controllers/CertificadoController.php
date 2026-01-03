<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CertificadoController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(5, min(100, $perPage));

        $query = Certificado::query();

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('ruc', 'like', "%{$q}%")
                    ->orWhere('usuario', 'like', "%{$q}%");
            });
        }

        $rows = $query
            ->orderByDesc('id')
            ->paginate($perPage)
            ->appends($request->query());

        return response()->json([
            'certificados' => $rows->items(),
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
            'nombre' => ['required', 'string', 'max:255'],
            'ruc' => ['nullable', 'string', 'max:50'],
            'usuario' => ['nullable', 'string', 'max:255'],
            'clave' => ['nullable', 'string', 'max:255'],
            'fecha_creacion' => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'estado' => ['nullable', Rule::in(['activo', 'inactivo'])],
            'tipo' => ['nullable', Rule::in(['OSE', 'PSE'])],
            'observacion' => ['nullable', 'string'],
            'imagen' => ['nullable'],
        ]);

        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $request->validate([
                'imagen' => ['file', 'image', 'max:4096'],
            ]);
            $imagenPath = $request->file('imagen')->store('certificados', 'public');
        } elseif ($request->filled('imagen') && is_string($request->input('imagen'))) {
            $imagenPath = trim((string) $request->input('imagen'));
        }

        $userId = $request->user()?->id;

        $certificado = Certificado::query()->create([
            'nombre' => $validated['nombre'],
            'ruc' => $validated['ruc'] ?? null,
            'usuario' => $validated['usuario'] ?? null,
            'clave' => $validated['clave'] ?? null,
            'fecha_creacion' => $validated['fecha_creacion'] ?? null,
            'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
            'estado' => $validated['estado'] ?? 'activo',
            'tipo' => $validated['tipo'] ?? null,
            'observacion' => $validated['observacion'] ?? null,
            'imagen' => $imagenPath,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        return response()->json([
            'message' => 'Certificado creado.',
            'data' => $certificado,
        ], 201);
    }

    public function update(Request $request, Certificado $certificado): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'ruc' => ['nullable', 'string', 'max:50'],
            'usuario' => ['nullable', 'string', 'max:255'],
            'clave' => ['nullable', 'string', 'max:255'],
            'fecha_creacion' => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'estado' => ['nullable', Rule::in(['activo', 'inactivo'])],
            'tipo' => ['nullable', Rule::in(['OSE', 'PSE'])],
            'observacion' => ['nullable', 'string'],
            'imagen' => ['nullable'],
        ]);

        $userId = $request->user()?->id;

        if ($request->hasFile('imagen')) {
            $request->validate([
                'imagen' => ['file', 'image', 'max:4096'],
            ]);

            if ($certificado->imagen && Storage::disk('public')->exists($certificado->imagen)) {
                Storage::disk('public')->delete($certificado->imagen);
            }

            $certificado->imagen = $request->file('imagen')->store('certificados', 'public');
        } elseif ($request->filled('imagen') && is_string($request->input('imagen'))) {
            $certificado->imagen = trim((string) $request->input('imagen'));
        }

        $certificado->fill([
            'nombre' => $validated['nombre'],
            'ruc' => $validated['ruc'] ?? null,
            'usuario' => $validated['usuario'] ?? null,
            'clave' => $validated['clave'] ?? null,
            'fecha_creacion' => $validated['fecha_creacion'] ?? null,
            'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
            'estado' => $validated['estado'] ?? $certificado->estado,
            'tipo' => $validated['tipo'] ?? null,
            'observacion' => $validated['observacion'] ?? null,
            'updated_by' => $userId,
        ]);
        $certificado->save();

        return response()->json([
            'message' => 'Certificado actualizado.',
            'data' => $certificado->fresh(),
        ]);
    }

    public function destroy(Request $request, Certificado $certificado): JsonResponse
    {
        if ($certificado->imagen && Storage::disk('public')->exists($certificado->imagen)) {
            Storage::disk('public')->delete($certificado->imagen);
        }
        $certificado->delete();

        return response()->json([
            'message' => 'Certificado eliminado.',
        ]);
    }
}
