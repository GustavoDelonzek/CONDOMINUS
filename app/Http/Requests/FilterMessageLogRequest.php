<?php

namespace App\Http\Requests;

use App\Traits\HasCondominiumContext;
use Illuminate\Foundation\Http\FormRequest;

class FilterMessageLogRequest extends FormRequest
{
    use HasCondominiumContext;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => 'sometimes|integer|min:1',
        ];
    }
}
