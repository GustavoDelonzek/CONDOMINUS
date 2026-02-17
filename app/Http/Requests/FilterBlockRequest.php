<?php

namespace App\Http\Requests;

use App\Traits\HasCondominiumContext;
use Illuminate\Foundation\Http\FormRequest;

class FilterBlockRequest extends FormRequest
{
    use HasCondominiumContext;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'condominium_id' => 'sometimes|string|exists:condominiums,id',
            'name' => 'sometimes|string',
            'per_page' => 'sometimes|integer|min:1',
        ];
    }
}
