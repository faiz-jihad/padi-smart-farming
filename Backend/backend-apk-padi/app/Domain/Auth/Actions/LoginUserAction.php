<?php

namespace App\Domain\Auth\Actions;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginUserAction
{
    /**
     * @param array{email: string, password: string, device_name?: string} $data
     * @return array{user: User, token: string}
     */
    public function execute(array $data, string $ipAddress): array
    {
        $key = $this->throttleKey($data['email'], $ipAddress);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$seconds} detik.",
            ], 429));
        }

        $user = User::query()
            ->where('email', $data['email'])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($key, 60);

            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Email atau password tidak valid.',
            ], 401));
        }

        if ($user->status !== UserStatus::Active->value) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Akun belum aktif atau sedang ditangguhkan.',
            ], 403));
        }

        RateLimiter::clear($key);

        $user->forceFill(['last_login_at' => now()])->save();

        $token = $user
            ->createToken($data['device_name'] ?? 'P.A.D.I Mobile')
            ->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    private function throttleKey(string $email, string $ipAddress): string
    {
        return Str::transliterate(Str::lower($email).'|'.$ipAddress);
    }
}
