<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login($request->validated());

        return response()->json([
            'message' => 'Login successful!',
            'token'   => $data['token'],
            'user'    => $data['user'],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logged out successfully!',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
