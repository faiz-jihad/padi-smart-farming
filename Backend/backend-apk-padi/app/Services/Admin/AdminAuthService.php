<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminAuthService
{
    public function canAccessAdmin(?User $user): bool
    {
        return $user !== null
            && in_array($user->role, [UserRole::Admin->value, UserRole::ExtensionOfficer->value, 'ppl'], true)
            && $user->status === UserStatus::Active->value;
    }

    /**
     * @param  array{email: string, password: string, remember?: bool}  $credentials
     * @return array{ok: bool, message?: string, retry_after?: int}
     */
    public function attempt(array $credentials, Request $request, AdminAuditLogger $audit): array
    {
        $email = Str::lower($credentials['email']);
        $throttleKey = $this->throttleKey($email, $request->ip() ?? 'unknown');

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return [
                'ok' => false,
                'message' => 'Terlalu banyak percobaan login. Coba lagi dalam '
                    .RateLimiter::availableIn($throttleKey).' detik.',
                'retry_after' => RateLimiter::availableIn($throttleKey),
            ];
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return ['ok' => false, 'message' => 'Email atau password tidak valid.'];
        }

        if (! $this->canAccessAdmin($user)) {
            RateLimiter::hit($throttleKey, 60);

            return ['ok' => false, 'message' => 'Akun ini tidak memiliki akses panel internal aktif.'];
        }

        RateLimiter::clear($throttleKey);
        Auth::login($user, (bool) ($credentials['remember'] ?? false));

        $user->forceFill(['last_login_at' => now()])->save();
        $audit->write('admin_login', $user, null, ['last_login_at' => $user->last_login_at], $request);

        return ['ok' => true];
    }

    public function logout(Request $request): void
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function throttleKey(string $email, string $ipAddress): string
    {
        return Str::transliterate($email.'|admin-web|'.$ipAddress);
    }
}
