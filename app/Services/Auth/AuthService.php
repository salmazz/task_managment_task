<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): User
    {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function login(array $data): array
    {
        $user = User::where('email', $data['email'])->firstOrFail();

        if (!Hash::check($data['password'], $user->password)) {
            abort(401, 'Invalid credentials.');
        }

        return [
            'user'  => $user,
            'token' => $user->createToken('api-token')->plainTextToken,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
