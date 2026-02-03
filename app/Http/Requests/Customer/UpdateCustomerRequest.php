<?php

namespace App\Http\Requests\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('customers.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'document_type' => ['nullable', 'string', Rule::in(['dni', 'ruc'])],
            'document_number' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido.',
            'contact_email.email' => 'El email debe ser válido.',
            'document_type.in' => 'El tipo de documento debe ser DNI o RUC.',
        ];
    }

    /**
     * Validate document consistency and uniqueness after standard validation
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $documentType = $this->input('document_type');
            $documentNumber = $this->input('document_number');

            if ($documentNumber && !$documentType) {
                $validator->errors()->add(
                    'document_type',
                    'El tipo de documento es requerido si envías número de documento.'
                );
            }

            if ($documentType && !$documentNumber) {
                $validator->errors()->add(
                    'document_number',
                    'El número de documento es requerido.'
                );
            }

            // Check for duplicate document (excluding current customer)
            if ($documentType && $documentNumber) {
                $customer = $this->route('customer');
                
                $exists = Customer::query()
                    ->where('id', '!=', $customer->id)
                    ->where('document_type', $documentType)
                    ->where('document_number', $documentNumber)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        'document_number',
                        'Ya existe un cliente con ese documento.'
                    );
                }
            }
        });
    }
}
