<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function data(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(5, min(100, $perPage));

        $query = Customer::query();

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('contact_name', 'like', "%{$q}%")
                    ->orWhere('contact_email', 'like', "%{$q}%")
                    ->orWhere('contact_phone', 'like', "%{$q}%")
                    ->orWhere('company_name', 'like', "%{$q}%")
                    ->orWhere('document_number', 'like', "%{$q}%");
            });
        }

        $customers = $query
            ->orderByDesc('id')
            ->paginate($perPage)
            ->appends($request->query());

        return response()->json([
            'customers' => $customers->items(),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'from' => $customers->firstItem(),
                'to' => $customers->lastItem(),
            ],
            'filters' => [
                'q' => $q,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'document_type' => ['nullable', 'string', Rule::in(['dni', 'ruc'])],
            'document_number' => ['nullable', 'string', 'max:20'],
        ]);

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
                ->where('id', '!=', $customer->id)
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

        $customer->fill([
            'name' => $validated['name'],
            'contact_name' => $validated['contact_name'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'company_address' => $validated['company_address'] ?? null,
            'document_type' => $documentType,
            'document_number' => $documentNumber,
        ]);
        $customer->save();

        return response()->json([
            'message' => 'Cliente actualizado.',
            'data' => $customer->fresh(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'document_type' => ['nullable', 'string', Rule::in(['dni', 'ruc'])],
            'document_number' => ['nullable', 'string', 'max:20'],
        ]);

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
            'contact_name' => $validated['contact_name'] ?? null,
            'contact_phone' => $validated['contact_phone'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'company_address' => $validated['company_address'] ?? null,
            'document_type' => $documentType,
            'document_number' => $documentNumber,
        ]);

        return response()->json([
            'message' => 'Cliente creado.',
            'data' => $customer,
        ], 201);
    }

    public function destroy(Request $request, Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json([
            'message' => 'Cliente eliminado.',
        ]);
    }
}
