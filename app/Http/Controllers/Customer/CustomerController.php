<?php

namespace App\Http\Controllers\Customer;

use App\DTOs\Customer\CustomerResponseDto;
use App\Http\Requests\Customer\CreateCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\Customer\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService
    ) {}
    public function data(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(5, min(100, $perPage));

        $filters = [
            'servidor' => trim((string) $request->query('servidor', '')),
            'menbresia' => trim((string) $request->query('menbresia', '')),
            'rubro' => trim((string) $request->query('rubro', '')),
            'document_type' => trim((string) $request->query('document_type', '')),
            'fecha_contacto_mes' => $request->query('fecha_contacto_mes'),
            'fecha_contacto_anio' => $request->query('fecha_contacto_anio'),
        ];

        $filters['fecha_contacto_mes'] = is_numeric($filters['fecha_contacto_mes'])
            ? (int) $filters['fecha_contacto_mes']
            : null;

        $filters['fecha_contacto_anio'] = is_numeric($filters['fecha_contacto_anio'])
            ? (int) $filters['fecha_contacto_anio']
            : null;

        $result = $this->customerService->search($q, $perPage, $filters);

        return response()->json(array_merge($result, [
            'filters' => [
                'q' => $q,
                'per_page' => $perPage,
                ...$filters,
            ],
        ]));
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json([
            'data' => CustomerResponseDto::fromModel($customer)->toArray(),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        try {
            $updated = $this->customerService->update($customer, $request->validated());

            return response()->json([
                'message' => 'Cliente actualizado.',
                'data' => CustomerResponseDto::fromModel($updated)->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el cliente.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function store(CreateCustomerRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $documentType = $validated['document_type'] ?? null;
        $documentNumber = $validated['document_number'] ?? null;

        if ($documentNumber && !$documentType) {
            return response()->json([
                'message' => 'El tipo de documento es requerido si envías número de documento.',
                'errors' => ['document_type' => ['El tipo de documento es requerido si envías número de documento.']],
            ], 422);
        }

        if ($documentType && !$documentNumber) {
            return response()->json([
                'message' => 'El número de documento es requerido.',
                'errors' => ['document_number' => ['El número de documento es requerido.']],
            ], 422);
        }

        if ($documentType && $documentNumber) {
            $exists = Customer::query()
                ->where('document_type', $documentType)
                ->where('document_number', $documentNumber)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Ya existe un cliente con ese documento.',
                    'errors' => ['document_number' => ['Ya existe un cliente con ese documento.']],
                ], 422);
            }
        }

        $customer = Customer::query()->create([
            'name' => $validated['name'],
            'csv_numero' => $validated['csv_numero'] ?? null,
            'contact_name' => $validated['contact_name'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'company_address' => $validated['company_address'] ?? null,
            'precio' => $validated['precio'] ?? null,
            'rubro' => $validated['rubro'] ?? null,
            'mes' => $validated['mes'] ?? null,
            'link' => $validated['link'] ?? null,
            'usuario' => $validated['usuario'] ?? null,
            'contrasena' => $validated['contrasena'] ?? null,
            'servidor' => $validated['servidor'] ?? null,
            'menbresia' => $validated['menbresia'] ?? null,
            'fecha_creacion' => $validated['fecha_creacion'] ?? now()->toDateString(),
            'fecha_contacto' => $validated['fecha_contacto'] ?? null,
            'fecha_contacto_mes' => $validated['fecha_contacto_mes'] ?? null,
            'fecha_contacto_anio' => $validated['fecha_contacto_anio'] ?? null,
            'document_type' => $documentType,
            'document_number' => $documentNumber,
            'observacion' => $validated['observacion'] ?? null,
        ]);

        return response()->json([
            'message' => 'Cliente creado.',
            'data' => CustomerResponseDto::fromModel($customer)->toArray(),
        ], 201);
    }

    public function destroy(Request $request, Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json([
            'message' => 'Cliente eliminado.',
        ]);
    }
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        
        if (strlen($q) < 2) {
            return response()->json(['customers' => []]);
        }

        $customers = Customer::where('name', 'LIKE', "%{$q}%")
            ->orWhere('company_name', 'LIKE', "%{$q}%")
            ->orWhere('document_number', 'LIKE', "%{$q}%")
            ->select(['id', 'name', 'company_name', 'document_number'])
            ->limit(10)
            ->get();

        return response()->json(['customers' => $customers]);
    }
    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        /** @var UploadedFile $file */
        $file = $request->file('csv');
        $filename = 'customers_import_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('imports', $filename);

        $fullPath = Storage::path($path);
        $dry = $request->boolean('dry');

        $exit = Artisan::call('import:customers', ['file' => $fullPath, '--dry-run' => $dry]);
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

        $deleted = Customer::query()->count();
        Customer::query()->delete();

        return response()->json([
            'message' => 'Tabla de clientes limpiada en local.',
            'deleted' => $deleted,
        ]);
    }
}
