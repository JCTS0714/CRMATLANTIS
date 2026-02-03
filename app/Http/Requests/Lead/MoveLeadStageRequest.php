<?php

namespace App\Http\Requests\Lead;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveLeadStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('leads.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'stage_id' => ['required', 'integer', Rule::exists('lead_stages', 'id')],
        ];
    }
}
