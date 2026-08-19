<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Auth\Actions\ChangePasswordAction;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\Api\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return ApiResponse::success('Profil berhasil diambil.', [
            'user' => UserResource::make($request->user()),
        ]);
    }

    public function update(UpdateProfileRequest $request, ProfileService $profile): JsonResponse
    {
        return ApiResponse::success('Profil berhasil diperbarui.', [
            'user' => UserResource::make($profile->update($request->user(), $request->validated())),
        ]);
    }

    public function changePassword(ChangePasswordRequest $request, ChangePasswordAction $action): JsonResponse
    {
        $action->execute($request->user(), $request->validated());

        return ApiResponse::success('Password berhasil diubah. Sesi perangkat lain telah diakhiri.');
    }
}
