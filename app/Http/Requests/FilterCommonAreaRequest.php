<?php

namespace App\Http\Requests;

use App\Traits\HasCondominiumContext;
use Illuminate\Foundation\Http\FormRequest;

class FilterCommonAreaRequest extends FormRequest
{
    use HasCondominiumContext;


    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => 'sometimes|integer|min:1|max:100',
            'search' => 'sometimes|string|min:1|max:255',
        ];
    }
}
