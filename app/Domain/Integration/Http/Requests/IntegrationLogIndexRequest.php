<?php

namespace Integration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IntegrationLogIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'provider_code' => ['sometimes', 'string', 'exists:providers,code'],
            'isrc' => ['sometimes', 'string', 'size:12'],
            'status' => ['sometimes', 'string', 'in:pending,success,not_found,failed'],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
