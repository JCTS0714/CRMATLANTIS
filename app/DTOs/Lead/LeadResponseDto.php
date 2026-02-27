<?php

namespace App\DTOs\Lead;

use App\Models\Lead;

class LeadResponseDto
{
    public function __construct(
        public readonly int $id,
        public readonly int $stageId,
        public readonly ?int $customerId,
        public readonly ?int $createdBy,
        public readonly ?string $createdByName,
        public readonly string $name,
        public readonly ?float $amount,
        public readonly string $currency,
        public readonly ?string $contactName,
        public readonly ?string $contactPhone,
        public readonly ?string $contactEmail,
        public readonly ?string $companyName,
        public readonly ?string $companyAddress,
        public readonly ?string $documentType,
        public readonly ?string $documentNumber,
        public readonly ?string $observacion,
        public readonly ?string $migracion,
        public readonly ?string $referencia,
        public readonly ?string $wonAt,
        public readonly ?string $archivedAt,
        public readonly ?int $position,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?string $stageName = null,
    ) {}

    /**
     * Create DTO from Lead model
     */
    public static function fromModel(Lead $lead, bool $includeRelations = true): self
    {
        return new self(
            id: $lead->id,
            stageId: $lead->stage_id,
            customerId: $lead->customer_id,
            createdBy: $lead->created_by,
            createdByName: $includeRelations && $lead->relationLoaded('creator') ? $lead->creator?->name : null,
            name: $lead->name,
            amount: $lead->amount ? (float) $lead->amount : null,
            currency: $lead->currency ?? 'PEN',
            contactName: $lead->contact_name,
            contactPhone: $lead->contact_phone,
            contactEmail: $lead->contact_email,
            companyName: $lead->company_name,
            companyAddress: $lead->company_address,
            documentType: $lead->document_type,
            documentNumber: $lead->document_number,
            observacion: $lead->observacion,
            migracion: $lead->migracion,
            referencia: $lead->referencia,
            wonAt: $lead->won_at?->format('Y-m-d H:i:s'),
            archivedAt: $lead->archived_at?->format('Y-m-d H:i:s'),
            position: $lead->position,
            createdAt: $lead->created_at->format('Y-m-d H:i:s'),
            updatedAt: $lead->updated_at->format('Y-m-d H:i:s'),
            stageName: $includeRelations && $lead->relationLoaded('stage') ? $lead->stage?->name : null,
        );
    }

    /**
     * Convert DTO to array for JSON response
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'stage_id' => $this->stageId,
            'customer_id' => $this->customerId,
            'created_by' => $this->createdBy,
            'created_by_name' => $this->createdByName,
            'name' => $this->name,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'contact_name' => $this->contactName,
            'contact_phone' => $this->contactPhone,
            'contact_email' => $this->contactEmail,
            'company_name' => $this->companyName,
            'company_address' => $this->companyAddress,
            'document_type' => $this->documentType,
            'document_number' => $this->documentNumber,
            'observacion' => $this->observacion,
            'migracion' => $this->migracion,
            'referencia' => $this->referencia,
            'won_at' => $this->wonAt,
            'archived_at' => $this->archivedAt,
            'position' => $this->position,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];

        // Include stage_name only if available
        if ($this->stageName !== null) {
            $data['stage_name'] = $this->stageName;
        }

        return $data;
    }

    /**
     * Compact version for mobile API (fewer fields)
     */
    public function toCompactArray(): array
    {
        return [
            'id' => $this->id,
            'stage_id' => $this->stageId,
            'name' => $this->name,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'contact_name' => $this->contactName,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
            'stage_name' => $this->stageName,
            'created_at' => $this->createdAt,
        ];
    }
}
