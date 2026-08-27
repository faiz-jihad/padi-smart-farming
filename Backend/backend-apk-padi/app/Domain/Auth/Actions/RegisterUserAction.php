<?php

namespace App\Domain\Auth\Actions;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterUserAction
{
    /**
     * @param array{name: string, email: string, phone: string, password: string, account_type: string, device_name?: string} $data
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
}
