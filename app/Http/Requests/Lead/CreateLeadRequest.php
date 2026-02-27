<?php

namespace App\Http\Requests\Lead;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('leads.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'stage_id' => ['nullable', 'integer', Rule::exists('lead_stages', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', Rule::in(['PEN', 'USD'])],
            'observacion' => ['nullable', 'string', 'max:2000'],
            'migracion' => ['nullable', 'string', 'max:255'],
            'referencia' => ['nullable', 'string', Rule::in(['TIK TOK', 'FACEBOOK', 'INSTAGRAM', 'whatsapp', 'otros'])],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'string', 'email', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'document_type' => ['nullable', 'string', Rule::in(['dni', 'ruc', 'otro'])],
            'document_number' => ['nullable', 'string', 'max:20'],
        ];
    }
}
