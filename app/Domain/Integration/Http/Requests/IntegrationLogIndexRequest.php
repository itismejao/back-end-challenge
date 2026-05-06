<?php

namespace Integration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Integration\Enums\IntegrationStatus;

class IntegrationLogIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'provider_code' => ['sometimes', 'string', 'exists:providers,code'],
            'isrc' => ['sometimes', 'string', 'size:12'],
            'status' => ['sometimes', 'string', Rule::in(array_column(IntegrationStatus::cases(), 'value'))],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
