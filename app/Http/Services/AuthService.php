<?php

namespace App\Http\Services;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

    public function logout(): void
    {
        auth('api')->logout();
    }

    public function registerUser(array $data): User
    {
        DB::beginTransaction();
        try {
            $user = User::firstOrCreate(
                ['phone_number' => $data['phone_number']],
                $data
            );

            $data['user_id'] = $user->id;
            Membership::query()->create($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $user;
    }
}
