<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'include_failed' => ['nullable', Rule::in(['0', '1', 'true', 'false'])],
        ];
    }

    public function messages(): array
    {
        return [
            'include_failed.in' => 'El valor debe ser verdadero o falso.',
        ];
    }
}
