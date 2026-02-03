<?php

namespace App\Http\Requests\Incidence;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('incidences.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['sometimes', 'required', 'integer', Rule::exists('customers', 'id')],
            'stage_id' => ['sometimes', 'required', 'integer', Rule::exists('incidence_stages', 'id')],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'priority' => ['nullable', 'string', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'notes' => ['nullable', 'string', 'max:4000'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'El cliente es requerido.',
            'customer_id.exists' => 'El cliente seleccionado no existe.',
            'stage_id.required' => 'La etapa es requerida.',
            'stage_id.exists' => 'La etapa seleccionada no existe.',
            'title.required' => 'El título es requerido.',
            'title.max' => 'El título no puede exceder 255 caracteres.',
            'date.date' => 'La fecha debe ser una fecha válida.',
            'priority.in' => 'La prioridad debe ser: low, medium, high o urgent.',
            'notes.max' => 'Las notas no pueden exceder 4000 caracteres.',
        ];
    }
}
