<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Auth\Actions\LoginUserAction;
use App\Domain\Auth\Actions\RegisterUserAction;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return ApiResponse::success(
            'Registrasi berhasil.',
            [
                'user' => UserResource::make($result['user']),
                'token' => $result['token'],
            ],
            201,
        );
    }

    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        $result = $action->execute($request->validated(), $request->ip());

        return ApiResponse::success('Login berhasil.', [
            'user' => UserResource::make($result['user']),
            'token' => $result['token'],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success('Data pengguna berhasil diambil.', [
            'user' => UserResource::make($request->user()),
        ]);
    }

    public function logout(Request $request, AuthSessionService $authSessionService): JsonResponse
    {
        $authSessionService->revokeCurrentToken($request->user());

        return ApiResponse::success('Logout berhasil.');
    }

    public function logoutAll(Request $request, AuthSessionService $authSessionService): JsonResponse
    {
        $authSessionService->revokeAllTokens($request->user());

        return ApiResponse::success('Semua sesi berhasil diakhiri.');
    }
}
