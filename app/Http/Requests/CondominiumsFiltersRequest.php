<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CondominiumsFiltersRequest extends FormRequest
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
            'per_page' => ['sometimes', 'integer', 'min:1'],
            'name' => 'sometimes|string',
            'address' => 'sometimes|string',
            'admin_company_id' => ['sometimes', 'uuid', 'exists:admin_companies,id'], //TODO: isso vai depende muito da rolle do usuário, por enquanto sometimes, irei criar uma função genérica depednendo da role,...
        ];
    }
}
