<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEmailCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Check if user can view customers when source is customers
        if ($this->input('source') === 'customers') {
            return $this->user()?->can('customers.view') ?? false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'source' => ['required', 'string', Rule::in(['leads', 'customers'])],
            'subject_template' => ['required', 'string', 'max:255'],
            'body_template' => ['required', 'string', 'max:4000'],
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'source.required' => 'El origen es requerido.',
            'source.in' => 'El origen debe ser leads o customers.',
            'subject_template.required' => 'El asunto es requerido.',
            'subject_template.max' => 'El asunto no puede exceder 255 caracteres.',
            'body_template.required' => 'El cuerpo del mensaje es requerido.',
            'body_template.max' => 'El cuerpo no puede exceder 4000 caracteres.',
            'ids.required' => 'Debes seleccionar al menos un destinatario.',
            'ids.min' => 'Debes seleccionar al menos un destinatario.',
            'ids.max' => 'No puedes seleccionar más de 500 destinatarios.',
        ];
    }
}
