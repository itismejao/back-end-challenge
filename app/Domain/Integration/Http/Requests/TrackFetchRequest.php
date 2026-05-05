<?php

namespace Integration\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackFetchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'isrcs' => ['required', 'array', 'min:1', 'max:100'],
            'isrcs.*' => ['required', 'string', 'size:12'],
            'provider' => ['sometimes', 'string', 'exists:providers,code'],
            'markets' => ['sometimes', 'array'],
            'markets.*' => ['required', 'string', 'size:2'],
        ];
    }
}
