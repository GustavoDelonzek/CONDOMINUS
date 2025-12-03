<?php

namespace App\Http\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * @throws ValidationException
     */
    public function loginUser(array $data): array
    {
        $token = auth('api')->attempt([
            'email' => $data['email'],
            'password' => $data['password']
        ]);

        if (!$token) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return [
            'user' => auth('api')->user(),
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        auth('api')->logout($user);
    }
}
