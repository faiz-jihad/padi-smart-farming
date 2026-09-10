<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domain\Auth\Actions\LoginUserAction;
use App\Domain\Auth\Actions\RegisterUserAction;
use App\Enums\UserStatus;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\FaceEnrollRequest;
use App\Http\Requests\Api\V1\Auth\FaceLoginRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const FACE_LOGIN_THRESHOLD = 0.46;
    private const FACE_ONLY_LOGIN_THRESHOLD = 0.44;
    private const FACE_ONLY_MARGIN = 0.10;
    private const FACE_LOGIN_MARGIN = 0.05;

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

    public function faceLogin(FaceLoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $query = User::query()
            ->where('status', UserStatus::Active->value)
            ->whereNotNull('face_descriptor');

        if (! empty($data['email'])) {
            $query->where('email', $data['email']);
        } elseif (! empty($data['phone'])) {
            $query->where('phone', $data['phone']);
        }

        $hasIdentityHint = ! empty($data['email']) || ! empty($data['phone']) || ! empty($data['pin']);
        $user = $this->findBestFaceUser($query->get(), $data['face_descriptor'], $hasIdentityHint);

        if (! $user) {
            throw ValidationException::withMessages([
                'face_descriptor' => ['Wajah belum cocok. Coba di tempat terang.'],
            ]);
        }

        if (! empty($data['pin']) && (! $user->pin_hash || ! Hash::check($data['pin'], $user->pin_hash))) {
            throw ValidationException::withMessages([
                'pin' => ['PIN belum cocok. Coba lagi atau gunakan bantuan lupa PIN.'],
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        $token = $user
            ->createToken($data['device_name'] ?? 'P.A.D.I Mobile Face Login')
            ->plainTextToken;

        return ApiResponse::success('Login wajah berhasil.', [
            'user' => UserResource::make($user),
            'token' => $token,
        ]);
    }

    public function faceEnroll(FaceEnrollRequest $request): JsonResponse
    {
        $data = $request->validated();

        $request->user()->forceFill([
            'face_descriptor' => $data['face_descriptors'] ?? [$data['face_descriptor']],
            'face_registered_at' => now(),
            'pin_hash' => ! empty($data['pin']) ? Hash::make($data['pin']) : $request->user()->pin_hash,
        ])->save();

        return ApiResponse::success('Wajah dan PIN berhasil didaftarkan.', [
            'user' => UserResource::make($request->user()->fresh()),
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

    /**
     * Face-api.js descriptors usually match below 0.6 Euclidean distance.
     *
     * @param array<int, float|int|string> $stored
     * @param array<int, float|int|string> $incoming
     */
    private function isMatchingFace(array $stored, array $incoming): bool
    {
        $distance = $this->bestFaceDistance($stored, $incoming);

        return $distance !== null && $distance <= self::FACE_LOGIN_THRESHOLD;
    }

    /**
     * @param \Illuminate\Support\Collection<int, User> $users
     * @param array<int, float|int|string> $incoming
     */
    private function findBestFaceUser($users, array $incoming, bool $hasIdentityHint): ?User
    {
        $bestUser = null;
        $bestDistance = null;
        $secondDistance = null;
        $threshold = $hasIdentityHint
            ? self::FACE_LOGIN_THRESHOLD
            : self::FACE_ONLY_LOGIN_THRESHOLD;
        $margin = $hasIdentityHint
            ? self::FACE_LOGIN_MARGIN
            : self::FACE_ONLY_MARGIN;

        foreach ($users as $user) {
            if (! is_array($user->face_descriptor)) {
                continue;
            }

            $distance = $this->bestFaceDistance($user->face_descriptor, $incoming);
            if ($distance === null) {
                continue;
            }

            if ($bestDistance === null || $distance < $bestDistance) {
                $secondDistance = $bestDistance;
                $bestDistance = $distance;
                $bestUser = $user;
                continue;
            }

            if ($secondDistance === null || $distance < $secondDistance) {
                $secondDistance = $distance;
            }
        }

        if ($bestDistance === null || $bestDistance > $threshold) {
            return null;
        }

        if ($secondDistance !== null && ($secondDistance - $bestDistance) < $margin) {
            throw ValidationException::withMessages([
                'face_descriptor' => ['Wajah belum cukup jelas. Coba ulang dengan cahaya lebih terang.'],
            ]);
        }

        return $bestUser;
    }

    /**
     * @param array<int, mixed> $stored
     * @param array<int, float|int|string> $incoming
     */
    private function bestFaceDistance(array $stored, array $incoming): ?float
    {
        $storedDescriptors = $this->storedFaceDescriptors($stored);

        if (count($incoming) !== 128 || $storedDescriptors === []) {
            return null;
        }

        $bestDistance = null;

        foreach ($storedDescriptors as $storedDescriptor) {
            if (count($storedDescriptor) !== 128) {
                continue;
            }

            $sum = 0.0;

            for ($i = 0; $i < 128; $i++) {
                $difference = (float) $storedDescriptor[$i] - (float) $incoming[$i];
                $sum += $difference * $difference;
            }

            $distance = sqrt($sum);
            if ($bestDistance === null || $distance < $bestDistance) {
                $bestDistance = $distance;
            }
        }

        return $bestDistance;
    }

    private function storedFaceDescriptors(array $stored): array
    {
        if (count($stored) === 128 && ! is_array($stored[0] ?? null)) {
            return [$stored];
        }

        return array_values(array_filter(
            $stored,
            fn ($descriptor) => is_array($descriptor)
        ));
    }
}
