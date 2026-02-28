<?php

namespace App\Services\Customer;

use App\Models\Customer;
use Illuminate\Support\Collection;

class CustomerService
{
    /**
     * Create a new customer with validation
     */
    public function create(array $data): Customer
    {
        return Customer::create([
            'name' => $data['name'],
            'csv_numero' => $data['csv_numero'] ?? null,
            'contact_name' => $data['contact_name'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'company_address' => $data['company_address'] ?? null,
            'precio' => $data['precio'] ?? null,
            'rubro' => $data['rubro'] ?? null,
            'mes' => $data['mes'] ?? null,
            'link' => $data['link'] ?? null,
            'usuario' => $data['usuario'] ?? null,
            'contrasena' => $data['contrasena'] ?? null,
            'servidor' => $data['servidor'] ?? null,
            'menbresia' => $data['menbresia'] ?? null,
            'fecha_creacion' => $data['fecha_creacion'] ?? null,
            'fecha_contacto' => $data['fecha_contacto'] ?? null,
            'fecha_contacto_mes' => $data['fecha_contacto_mes'] ?? null,
            'fecha_contacto_anio' => $data['fecha_contacto_anio'] ?? null,
            'document_type' => $data['document_type'] ?? null,
            'document_number' => $data['document_number'] ?? null,
            'observacion' => $data['observacion'] ?? null,
        ]);
    }

    /**
     * Update an existing customer
     */
    public function update(Customer $customer, array $data): Customer
    {
        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (array_key_exists('contact_name', $data)) {
            $updateData['contact_name'] = $data['contact_name'];
        }
        if (array_key_exists('csv_numero', $data)) {
            $updateData['csv_numero'] = $data['csv_numero'];
        }
        if (array_key_exists('contact_phone', $data)) {
            $updateData['contact_phone'] = $data['contact_phone'];
        }
        if (array_key_exists('contact_email', $data)) {
            $updateData['contact_email'] = $data['contact_email'];
        }
        if (array_key_exists('company_name', $data)) {
            $updateData['company_name'] = $data['company_name'];
        }
        if (array_key_exists('company_address', $data)) {
            $updateData['company_address'] = $data['company_address'];
        }
        if (array_key_exists('precio', $data)) {
            $updateData['precio'] = $data['precio'];
        }
        if (array_key_exists('rubro', $data)) {
            $updateData['rubro'] = $data['rubro'];
        }
        if (array_key_exists('mes', $data)) {
            $updateData['mes'] = $data['mes'];
        }
        if (array_key_exists('link', $data)) {
            $updateData['link'] = $data['link'];
        }
        if (array_key_exists('usuario', $data)) {
            $updateData['usuario'] = $data['usuario'];
        }
        if (array_key_exists('contrasena', $data)) {
            $updateData['contrasena'] = $data['contrasena'];
        }
        if (array_key_exists('servidor', $data)) {
            $updateData['servidor'] = $data['servidor'];
        }
        if (array_key_exists('menbresia', $data)) {
            $updateData['menbresia'] = $data['menbresia'];
        }
        if (array_key_exists('fecha_creacion', $data)) {
            $updateData['fecha_creacion'] = $data['fecha_creacion'];
        }
        if (array_key_exists('fecha_contacto', $data)) {
            $updateData['fecha_contacto'] = $data['fecha_contacto'];
        }
        if (array_key_exists('fecha_contacto_mes', $data)) {
            $updateData['fecha_contacto_mes'] = $data['fecha_contacto_mes'];
        }
        if (array_key_exists('fecha_contacto_anio', $data)) {
            $updateData['fecha_contacto_anio'] = $data['fecha_contacto_anio'];
        }
        if (array_key_exists('document_type', $data)) {
            $updateData['document_type'] = $data['document_type'];
        }
        if (array_key_exists('document_number', $data)) {
            $updateData['document_number'] = $data['document_number'];
        }
        if (array_key_exists('observacion', $data)) {
            $updateData['observacion'] = $data['observacion'];
        }

        $customer->update($updateData);

        return $customer->fresh();
    }

    /**
     * Search customers with filters
     */
    public function search(string $query, int $perPage = 15, array $filters = []): array
    {
        $queryBuilder = Customer::query();

        if ($query !== '') {
            $queryBuilder->where(function ($sub) use ($query) {
                $sub->where('name', 'like', "%{$query}%")
                    ->orWhere('contact_name', 'like', "%{$query}%")
                    ->orWhere('contact_email', 'like', "%{$query}%")
                    ->orWhere('contact_phone', 'like', "%{$query}%")
                    ->orWhere('company_name', 'like', "%{$query}%")
                    ->orWhere('rubro', 'like', "%{$query}%")
                    ->orWhere('mes', 'like', "%{$query}%")
                    ->orWhere('usuario', 'like', "%{$query}%")
                    ->orWhere('servidor', 'like', "%{$query}%")
                    ->orWhere('menbresia', 'like', "%{$query}%")
                    ->orWhere('csv_numero', 'like', "%{$query}%")
                    ->orWhere('document_number', 'like', "%{$query}%");
            });
        }

        $servidor = trim((string) ($filters['servidor'] ?? ''));
        if ($servidor !== '') {
            $queryBuilder->where('servidor', $servidor);
        }

        $menbresia = trim((string) ($filters['menbresia'] ?? ''));
        if ($menbresia !== '') {
            $queryBuilder->where('menbresia', $menbresia);
        }

        $rubro = trim((string) ($filters['rubro'] ?? ''));
        if ($rubro !== '') {
            $queryBuilder->where('rubro', 'like', "%{$rubro}%");
        }

        $documentType = trim((string) ($filters['document_type'] ?? ''));
        if ($documentType !== '') {
            $queryBuilder->where('document_type', $documentType);
        }

        $contactMonth = (int) ($filters['fecha_contacto_mes'] ?? 0);
        if ($contactMonth >= 1 && $contactMonth <= 12) {
            $queryBuilder->where('fecha_contacto_mes', $contactMonth);
        }

        $contactYear = (int) ($filters['fecha_contacto_anio'] ?? 0);
        if ($contactYear >= 2000 && $contactYear <= 2100) {
            $queryBuilder->where('fecha_contacto_anio', $contactYear);
        }

        $paginator = $queryBuilder
            ->orderByDesc('id')
            ->paginate($perPage);

        return [
            'customers' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    /**
     * Find customer by document
     */
    public function findByDocument(string $documentType, string $documentNumber): ?Customer
    {
        return Customer::query()
            ->where('document_type', $documentType)
            ->where('document_number', $documentNumber)
            ->first();
    }

    /**
     * Check if document exists (excluding specific customer)
     */
    public function documentExists(string $documentType, string $documentNumber, ?int $excludeCustomerId = null): bool
    {
        $query = Customer::query()
            ->where('document_type', $documentType)
            ->where('document_number', $documentNumber);

        if ($excludeCustomerId) {
            $query->where('id', '!=', $excludeCustomerId);
        }

        return $query->exists();
    }

    /**
     * Get customer with relationships
     */
    public function find(int $id, array $with = []): ?Customer
    {
        $query = Customer::query();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }

    /**
     * Import customers from array data
     */
    public function importBatch(array $customersData): array
    {
        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($customersData as $index => $data) {
            try {
                // Check if customer exists by document
                if (!empty($data['document_type']) && !empty($data['document_number'])) {
                    $exists = $this->findByDocument($data['document_type'], $data['document_number']);
                    if ($exists) {
                        $skipped++;
                        continue;
                    }
                }

                $this->create($data);
                $created++;
            } catch (\Exception $e) {
                $errors[] = [
                    'row' => $index + 1,
                    'error' => $e->getMessage(),
                ];
                $skipped++;
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }
}
