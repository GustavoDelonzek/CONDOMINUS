<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
