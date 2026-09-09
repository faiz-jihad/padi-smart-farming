<?php

namespace App\Domain\Auth\Actions;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    /**
     * @param array{name: string, email: string, phone: string, password: string, account_type: string, device_name?: string, pin?: string, face_descriptor?: array<int, float>, face_descriptors?: array<int, array<int, float>>} $data
     * @return array{user: User, token: string}
     */
    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'face_descriptor' => $this->faceDescriptors($data),
                'face_registered_at' => $this->faceDescriptors($data) !== null ? now() : null,
                'pin_hash' => isset($data['pin']) ? Hash::make($data['pin']) : null,
                'role' => $this->legacyRoleValue($data['account_type']),
                'status' => UserStatus::Active->value,
                'verification_status' => 'verified',
                'last_login_at' => now(),
            ]);

            try {
                $user->assignRole($data['account_type']);
            } catch (\Throwable $e) {
                // Keep resilient if Spatie permission table is not seeded
            }

            $token = $user
                ->createToken($data['device_name'] ?? 'P.A.D.I Mobile')
                ->plainTextToken;

            return ['user' => $user, 'token' => $token];
        });
    }

    private function legacyRoleValue(string $accountType): string
    {
        return match ($accountType) {
            'buyer' => 'buyer',
            'partner' => 'buyer',
            'extension_officer' => 'ppl',
            default => $accountType,
        };
    }

    private function faceDescriptors(array $data): ?array
    {
        if (isset($data['face_descriptors'])) {
            return $data['face_descriptors'];
        }

        if (isset($data['face_descriptor'])) {
            return [$data['face_descriptor']];
        }

        return null;
    }
}
