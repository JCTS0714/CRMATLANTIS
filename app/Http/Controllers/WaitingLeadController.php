<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\WaitingLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WaitingLeadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) ($request->query('per_page', 25));

        $query = WaitingLead::query()->orderByDesc('created_at');

        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function ($qb) use ($like) {
                $qb->where('name', 'like', $like)
                    ->orWhere('contact_name', 'like', $like)
                    ->orWhere('contact_email', 'like', $like)
                    ->orWhere('company_name', 'like', $like)
                    ->orWhere('document_number', 'like', $like)
                    ->orWhere('observacion', 'like', $like);
            });
        }

        $p = $query->paginate($perPage);

        return response()->json([
            'data' => [
                'items' => $p->items(),
                'pagination' => [
                    'current_page' => $p->currentPage(),
                    'last_page' => $p->lastPage(),
                    'per_page' => $p->perPage(),
                    'total' => $p->total(),
                ],
            ],
        ]);
    }

    public function show(WaitingLead $waitingLead): JsonResponse
    {
        return response()->json([
            'data' => $waitingLead,
        ]);
    }

    public function update(Request $request, WaitingLead $waitingLead): JsonResponse
    {
        $validated = $request->validate([
            'observacion' => ['nullable', 'string', 'max:2000'],
        ]);

        $waitingLead->observacion = $validated['observacion'] ?? null;
        $waitingLead->save();

        return response()->json([
            'data' => $waitingLead,
        ]);
    }

    public function createFromLead(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'observacion' => ['nullable', 'string', 'max:2000'],
        ]);

        $userId = $request->user()?->id ?? null;

        $waitingLead = null;

        try {
            $waitingLead = DB::transaction(function () use ($lead, $validated, $userId) {
                $created = WaitingLead::create([
                    'lead_id' => $lead->id,
                    'name' => $lead->name,
                    'contact_name' => $lead->contact_name,
                    'contact_phone' => $lead->contact_phone,
                    'contact_email' => $lead->contact_email,
                    'company_name' => $lead->company_name,
                    'company_address' => $lead->company_address,
                    'document_type' => $lead->document_type,
                    'document_number' => $lead->document_number,
                    'observacion' => $validated['observacion'] ?? $lead->observacion ?? null,
                    'created_by' => $userId,
                ]);

                // Archive original lead so it doesn't appear in pipeline
                $lead->archived_at = $lead->archived_at ?? now();
                $lead->save();

                return $created;
            });
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage() ?: 'No se pudo pasar a zona de espera.'], 422);
        }

        $id = $waitingLead?->id;
        $location = $id ? ('/espera?open='.$id.'&focus=observacion') : '/espera';

        return response()->json([
            'message' => 'Lead enviado a zona de espera.',
            'location' => $location,
        ]);
    }

    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        /** @var UploadedFile $file */
        $file = $request->file('csv');
        $filename = 'waiting_leads_import_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('imports', $filename);

        $fullPath = Storage::path($path);
        $dry = $request->boolean('dry');

        $exit = Artisan::call('import:waiting-leads', ['file' => $fullPath, '--dry-run' => $dry]);
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
