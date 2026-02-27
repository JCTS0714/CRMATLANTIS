<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('customers.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'csv_numero' => ['nullable', 'integer', 'min:1'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'precio' => ['nullable', 'numeric', 'min:0'],
            'rubro' => ['nullable', 'string', 'max:255'],
            'mes' => ['nullable', 'string', 'max:40'],
            'link' => ['nullable', 'string', 'max:512'],
            'usuario' => ['nullable', 'string', 'max:150'],
            'contrasena' => ['nullable', 'string', 'max:255'],
            'servidor' => ['nullable', 'string', Rule::in(['ATLANTIS ONLINE', 'ATLANTIS VIP', 'ATLANTIS POS', 'ATLANTIS FAST', 'LORITO'])],
            'fecha_creacion' => ['nullable', 'date'],
            'fecha_contacto' => ['nullable', 'date'],
            'fecha_contacto_mes' => ['nullable', 'integer', 'between:1,12', 'required_with:fecha_contacto_anio'],
            'fecha_contacto_anio' => ['nullable', 'integer', 'between:2000,2100', 'required_with:fecha_contacto_mes'],
            'document_type' => ['nullable', 'string', Rule::in(['dni', 'ruc', 'otro'])],
            'document_number' => ['nullable', 'string', 'max:20'],
            'observacion' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido.',
            'csv_numero.integer' => 'El N° debe ser numérico.',
            'contact_email.email' => 'El email debe ser válido.',
            'document_type.in' => 'El tipo de documento debe ser DNI, RUC u OTRO.',
            'servidor.in' => 'El servidor debe ser ATLANTIS ONLINE, ATLANTIS VIP, ATLANTIS POS, ATLANTIS FAST o LORITO.',
            'fecha_contacto_mes.required_with' => 'Debes completar mes y año de contacto.',
            'fecha_contacto_anio.required_with' => 'Debes completar mes y año de contacto.',
            'fecha_contacto_mes.between' => 'El mes de contacto debe estar entre 1 y 12.',
            'fecha_contacto_anio.between' => 'El año de contacto no es válido.',
        ];
    }

    /**
     * Validate document consistency after standard validation
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
        });
    }
}
