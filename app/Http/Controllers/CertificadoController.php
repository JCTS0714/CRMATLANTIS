<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public function importData(Request $request): JsonResponse
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        /** @var UploadedFile $file */
        $file = $request->file('csv');
        $filename = 'certificados_data_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('imports', $filename);

        $fullPath = Storage::path($path);
        $dry = $request->boolean('dry');

        $exit = Artisan::call('import:certificados', ['file' => $fullPath, '--dry-run' => $dry]);
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

    public function importImages(Request $request): JsonResponse
    {
        // Two modes:
        // 1) Multiple files upload: images[]
        // 2) ZIP upload: zip (requires ZipArchive)

        $dry = $request->boolean('dry');

        if ($request->hasFile('images')) {
            $request->validate([
                'images' => ['required', 'array', 'min:1'],
                'images.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf', 'max:8192'],
            ]);

            /** @var UploadedFile[] $files */
            $files = $request->file('images', []);

            $updated = 0;
            $skipped = 0;
            $unmatched = 0;

            foreach ($files as $file) {
                if (!$file instanceof UploadedFile) {
                    $skipped++;
                    continue;
                }

                $original = (string) $file->getClientOriginalName();
                if (!preg_match('/(\d{11})/', $original, $m)) {
                    $unmatched++;
                    continue;
                }
                $ruc = $m[1];

                $cert = Certificado::query()->where('ruc', $ruc)->orderByDesc('id')->first();
                if (!$cert) {
                    $unmatched++;
                    continue;
                }

                if (!empty($cert->imagen)) {
                    $skipped++;
                    continue;
                }

                $ext = $file->getClientOriginalExtension();
                $ext = $ext ? strtolower($ext) : '';
                if ($ext === '') {
                    $ext = $file->getClientMimeType() === 'application/pdf' ? 'pdf' : 'jpg';
                }

                $targetName = $ruc . '.' . $ext;
                $targetPath = 'certificados/' . $targetName;

                if ($dry) {
                    $updated++;
                    continue;
                }

                $stored = $file->storeAs('certificados', $targetName, 'public');
                $cert->imagen = $stored;
                $cert->save();
                $updated++;
            }

            return response()->json([
                'message' => 'Importación de imágenes finalizada.',
                'updated' => $updated,
                'skipped' => $skipped,
                'unmatched' => $unmatched,
                'mode' => $dry ? 'dry-run' : 'write',
            ]);
        }

        // ZIP fallback
        $request->validate([
            'zip' => ['required', 'file', 'mimes:zip'],
        ]);

        if (!class_exists(\ZipArchive::class)) {
            return response()->json([
                'message' => 'ZipArchive no está disponible en esta instalación de PHP. Usa selección múltiple (images[]) en vez de ZIP.',
            ], 422);
        }

        /** @var UploadedFile $file */
        $file = $request->file('zip');
        $filename = 'certificados_images_' . Str::random(8) . '.zip';
        $path = $file->storeAs('imports', $filename);

        $fullPath = Storage::path($path);

        $exit = Artisan::call('import:certificados:imagenes', ['file' => $fullPath, '--dry-run' => $dry]);
        $output = Artisan::output();

        if ($exit !== 0) {
            return response()->json([
                'message' => trim($output) !== '' ? trim($output) : 'Error importing images',
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
