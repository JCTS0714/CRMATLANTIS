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
            'contact_name' => $data['contact_name'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'company_address' => $data['company_address'] ?? null,
            'document_type' => $data['document_type'] ?? null,
            'document_number' => $data['document_number'] ?? null,
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
        if (array_key_exists('document_type', $data)) {
            $updateData['document_type'] = $data['document_type'];
        }
        if (array_key_exists('document_number', $data)) {
            $updateData['document_number'] = $data['document_number'];
        }

        $customer->update($updateData);

        return $customer->fresh();
    }

    /**
     * Search customers with filters
     */
    public function search(string $query, int $perPage = 15): array
    {
        $queryBuilder = Customer::query();

        if ($query !== '') {
            $queryBuilder->where(function ($sub) use ($query) {
                $sub->where('name', 'like', "%{$query}%")
                    ->orWhere('contact_name', 'like', "%{$query}%")
                    ->orWhere('contact_email', 'like', "%{$query}%")
                    ->orWhere('contact_phone', 'like', "%{$query}%")
                    ->orWhere('company_name', 'like', "%{$query}%")
                    ->orWhere('document_number', 'like', "%{$query}%");
            });
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
