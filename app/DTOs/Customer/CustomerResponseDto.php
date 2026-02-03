<?php

namespace App\DTOs\Customer;

use App\Models\Customer;

class CustomerResponseDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $contactName,
        public readonly ?string $contactPhone,
        public readonly ?string $contactEmail,
        public readonly ?string $companyName,
        public readonly ?string $companyAddress,
        public readonly ?string $documentType,
        public readonly ?string $documentNumber,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    /**
     * Create DTO from Customer model
     */
    public static function fromModel(Customer $customer): self
    {
        return new self(
            id: $customer->id,
            name: $customer->name,
            contactName: $customer->contact_name,
            contactPhone: $customer->contact_phone,
            contactEmail: $customer->contact_email,
            companyName: $customer->company_name,
            companyAddress: $customer->company_address,
            documentType: $customer->document_type,
            documentNumber: $customer->document_number,
            createdAt: $customer->created_at->format('Y-m-d H:i:s'),
            updatedAt: $customer->updated_at->format('Y-m-d H:i:s'),
        );
    }

    /**
     * Convert to array for JSON response
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'contact_name' => $this->contactName,
            'contact_phone' => $this->contactPhone,
            'contact_email' => $this->contactEmail,
            'company_name' => $this->companyName,
            'company_address' => $this->companyAddress,
            'document_type' => $this->documentType,
            'document_number' => $this->documentNumber,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Compact version for mobile
     */
    public function toCompactArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'contact_name' => $this->contactName,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
        ];
    }
}
