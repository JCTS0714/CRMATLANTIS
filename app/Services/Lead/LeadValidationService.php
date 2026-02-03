<?php

namespace App\Services\Lead;

use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Validation\Validator;

class LeadValidationService
{
    /**
     * Validate document-related rules for lead creation/update
     */
    public function validateDocument(Validator $validator, ?string $documentType, ?string $documentNumber, ?int $excludeLeadId = null): void
    {
        if ($documentNumber && !$documentType) {
            $validator->errors()->add('document_type', 'El tipo de documento es requerido si envías número de documento.');
            return;
        }

        if (!$documentType) {
            return;
        }

        if (!$documentNumber) {
            $validator->errors()->add('document_number', 'El número de documento es requerido.');
            return;
        }

        // Validate digit count: DNI=8, RUC=11
        $digits = $documentType === 'dni' ? 8 : 11;
        if (!preg_match('/^\d{'.$digits.'}$/', (string) $documentNumber)) {
            $validator->errors()->add('document_number', "El número de documento debe tener {$digits} dígitos.");
            return;
        }

        // Check if customer already exists with this document
        $existingCustomer = Customer::query()
            ->where('document_type', $documentType)
            ->where('document_number', (string) $documentNumber)
            ->first();

        if ($existingCustomer) {
            // For updates: allow if the lead is already linked to this customer
            if ($excludeLeadId) {
                $lead = Lead::find($excludeLeadId);
                if ($lead && $lead->customer_id === $existingCustomer->id) {
                    return; // Allow update
                }
            }

            $validator->errors()->add('document_number', 'Ya existe un cliente con este documento. No se puede crear otro lead con el mismo documento.');
            return;
        }

        // Check if another active lead has this document
        $query = Lead::query()
            ->whereNull('archived_at')
            ->where('document_type', $documentType)
            ->where('document_number', (string) $documentNumber);

        if ($excludeLeadId) {
            $query->where('id', '<>', $excludeLeadId);
        }

        if ($query->exists()) {
            $validator->errors()->add('document_number', 'Ya existe un lead activo con este documento.');
        }
    }
}
