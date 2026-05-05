<?php

namespace Music\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Music\Enums\TrackOrderBy;

class TrackIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'market' => ['required', 'string', 'size:2'],
            'order_by' => ['sometimes', 'string', Rule::in(TrackOrderBy::values())],
            'direction' => ['sometimes', 'string', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
