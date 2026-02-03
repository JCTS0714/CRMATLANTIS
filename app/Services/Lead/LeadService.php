<?php

namespace App\Services\Lead;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Repositories\Lead\LeadRepositoryInterface;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function __construct(
        private readonly LeadValidationService $validationService,
        private readonly LeadRepositoryInterface $leadRepository
    ) {}

    /**
     * Create a new lead with validation
     */
    public function create(array $data, ?int $userId = null): Lead
    {
        // Get or determine stage_id
        $stageId = $data['stage_id'] ?? null;
        if (!$stageId) {
            $stageId = LeadStage::query()->orderBy('sort_order')->orderBy('id')->value('id');
        }

        if (!$stageId) {
            throw new \RuntimeException('No hay etapas de leads configuradas.');
        }

        return $this->leadRepository->create([
            'stage_id' => (int) $stageId,
            'created_by' => $userId,
            'name' => $data['name'],
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? 'PEN',
            'observacion' => $data['observacion'] ?? null,
            'migracion' => $data['migracion'] ?? null,
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
     * Update an existing lead
     */
    public function update(Lead $lead, array $data): Lead
    {
        if ($lead->archived_at) {
            throw new \RuntimeException('No se puede editar un lead archivado.');
        }

        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['amount'])) {
            $updateData['amount'] = $data['amount'];
        }
        if (isset($data['currency'])) {
            $updateData['currency'] = $data['currency'];
        }
        if (array_key_exists('observacion', $data)) {
            $updateData['observacion'] = $data['observacion'];
        }
        if (array_key_exists('migracion', $data)) {
            $updateData['migracion'] = $data['migracion'];
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

        $this->leadRepository->update($lead, $updateData);

        return $lead->fresh();
    }

    /**
     * Move lead to a different stage
     */
    public function moveStage(Lead $lead, int $newStageId): Lead
    {
        if ($lead->archived_at) {
            throw new \RuntimeException('No se puede mover un lead archivado.');
        }

        $stage = LeadStage::find($newStageId);
        if (!$stage) {
            throw new \RuntimeException('La etapa seleccionada no existe.');
        }

        $lead->stage_id = $newStageId;
        $lead->save();

        return $lead->fresh(['stage']);
    }

    /**
     * Archive a lead (soft archive, not delete)
     */
    public function archive(Lead $lead): Lead
    {
        if ($lead->archived_at) {
            throw new \RuntimeException('El lead ya está archivado.');
        }

        $lead->archived_at = now();
        $lead->save();

        return $lead->fresh();
    }

    /**
     * Convert lead to customer
     */
    public function convertToCustomer(Lead $lead): Customer
    {
        if ($lead->archived_at) {
            throw new \RuntimeException('No se puede convertir un lead archivado.');
        }

        return DB::transaction(function () use ($lead) {
            // Check if customer already exists with same document
            $existingCustomer = null;
            if ($lead->document_type && $lead->document_number) {
                $existingCustomer = Customer::where('document_type', $lead->document_type)
                    ->where('document_number', $lead->document_number)
                    ->first();
            }

            if ($existingCustomer) {
                // Archive the lead and return existing customer
                $lead->archived_at = now();
                $lead->save();

                return $existingCustomer;
            }

            // Create new customer
            $customer = Customer::create([
                'name' => $lead->name,
                'contact_name' => $lead->contact_name,
                'contact_phone' => $lead->contact_phone,
                'contact_email' => $lead->contact_email,
                'company_name' => $lead->company_name,
                'company_address' => $lead->company_address,
                'document_type' => $lead->document_type,
                'document_number' => $lead->document_number,
            ]);

            // Move lead to won stage and archive
            $wonStage = LeadStage::where('is_won', true)->first();
            if ($wonStage) {
                $lead->stage_id = $wonStage->id;
            }

            $lead->customer_id = $customer->id;
            $lead->archived_at = now();
            $lead->save();

            return $customer;
        });
    }

    /**
     * Get lead with relationships
     */
    public function find(int $id, array $with = []): ?Lead
    {
        return $this->leadRepository->find($id, $with);
    }

    /**
     * Validate lead document
     */
    public function validateDocument(?string $documentType, ?string $documentNumber): array
    {
        return $this->validationService->validateDocumentRules($documentType, $documentNumber);
    }
}
