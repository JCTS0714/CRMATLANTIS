<?php

namespace App\DTOs\Customer;

use App\Models\Customer;

class CustomerResponseDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?int $csvNumero,
        public readonly ?string $contactName,
        public readonly ?string $contactPhone,
        public readonly ?string $contactEmail,
        public readonly ?string $companyName,
        public readonly ?string $companyAddress,
        public readonly ?string $precio,
        public readonly ?string $rubro,
        public readonly ?string $mes,
        public readonly ?string $link,
        public readonly ?string $usuario,
        public readonly ?string $contrasena,
        public readonly ?string $servidor,
        public readonly ?string $menbresia,
        public readonly ?string $fechaCreacion,
        public readonly ?string $fechaContacto,
        public readonly ?int $fechaContactoMes,
        public readonly ?int $fechaContactoAnio,
        public readonly ?string $documentType,
        public readonly ?string $documentNumber,
        public readonly ?string $observacion,
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
            csvNumero: $customer->csv_numero,
            contactName: $customer->contact_name,
            contactPhone: $customer->contact_phone,
            contactEmail: $customer->contact_email,
            companyName: $customer->company_name,
            companyAddress: $customer->company_address,
            precio: $customer->precio,
            rubro: $customer->rubro,
            mes: $customer->mes,
            link: $customer->link,
            usuario: $customer->usuario,
            contrasena: $customer->contrasena,
            servidor: $customer->servidor,
            menbresia: $customer->menbresia,
            fechaCreacion: $customer->fecha_creacion?->format('Y-m-d'),
            fechaContacto: $customer->fecha_contacto?->format('Y-m-d'),
            fechaContactoMes: $customer->fecha_contacto_mes,
            fechaContactoAnio: $customer->fecha_contacto_anio,
            documentType: $customer->document_type,
            documentNumber: $customer->document_number,
            observacion: $customer->observacion,
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
            'csv_numero' => $this->csvNumero,
            'contact_name' => $this->contactName,
            'contact_phone' => $this->contactPhone,
            'contact_email' => $this->contactEmail,
            'company_name' => $this->companyName,
            'company_address' => $this->companyAddress,
            'precio' => $this->precio,
            'rubro' => $this->rubro,
            'mes' => $this->mes,
            'link' => $this->link,
            'usuario' => $this->usuario,
            'contrasena' => $this->contrasena,
            'servidor' => $this->servidor,
            'menbresia' => $this->menbresia,
            'fecha_creacion' => $this->fechaCreacion,
            'fecha_contacto' => $this->fechaContacto,
            'fecha_contacto_mes' => $this->fechaContactoMes,
            'fecha_contacto_anio' => $this->fechaContactoAnio,
            'document_type' => $this->documentType,
            'document_number' => $this->documentNumber,
            'observacion' => $this->observacion,
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
