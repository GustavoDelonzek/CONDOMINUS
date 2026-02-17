<?php

namespace App\Http\Requests;

use App\Traits\HasCondominiumContext;
use Illuminate\Foundation\Http\FormRequest;

class FilterUnitRequest extends FormRequest
{
    use HasCondominiumContext;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'condominium_id' => 'sometimes|integer|exists:condominiums,id',
            'block_id' => 'sometimes|integer|exists:blocks,id',
            'number' => 'sometimes|string',
            'floor' => 'sometimes|string',
            'per_page' => 'sometimes|integer|min:1',
        ];
    }
}
