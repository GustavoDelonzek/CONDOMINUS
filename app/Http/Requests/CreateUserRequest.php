<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|unique:users,phone_number',
            'chat_lid' => ['nullable', 'string', 'unique:users,chat_lid'],
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols(),
                'confirmed'
            ],
            'role' => ['required', 'string', 'in:company_admin,syndic,resident'],
            'unit_id' => ['uuid', Rule::requiredIf(fn () => $this->role === 'resident' && $this->filled('condominium_id')), 'exists:units,id'],
        ];
    }
}
