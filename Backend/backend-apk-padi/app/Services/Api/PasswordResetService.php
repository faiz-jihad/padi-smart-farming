<?php

namespace App\Services\Api;

use App\Mail\PasswordResetCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetService
{
    private const CODE_TTL = 10;

    private const CACHE_PREFIX = 'password_reset_code:';

    public function mailerIsConfigured(): bool
    {
        $mailer = (string) Config::get('mail.default');

        return ! in_array($mailer, ['array', 'log'], true);
    }

    public function sendResetCode(array $data): bool
    {
        $email = strtolower(trim((string) ($data['email'] ?? '')));

        $user = User::where('email', $email)->first();

        if (! $user) {
            return true;
        }

        $code = (string) random_int(100000, 999999);

        $cacheKey = $this->cacheKey($email);

        Cache::put(
            $cacheKey,
            [
                'code' => Hash::make($code),
                'expires_at' => now()->addMinutes(self::CODE_TTL)->timestamp,
            ],
            now()->addMinutes(self::CODE_TTL)
        );

        try {
            Mail::to($user->email)->send(
                new PasswordResetCodeMail(
                    name: $user->name,
                    code: $code,
                )
            );

            return true;
        } catch (\Throwable $exception) {
            Cache::forget($cacheKey);

            report($exception);

            return false;
        }
    }

    public function verifyCode(string $email, string $code): bool
    {
        $email = strtolower(trim($email));
        $code = trim($code);

        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $reset = Cache::get($this->cacheKey($email));

        if (! is_array($reset)) {
            return false;
        }

        if (
            ! isset($reset['code']) ||
            ! isset($reset['expires_at'])
        ) {
            return false;
        }

        if ((int) $reset['expires_at'] < now()->timestamp) {
            Cache::forget($this->cacheKey($email));

            return false;
        }

        return Hash::check($code, $reset['code']);
    }

    public function reset(array $data): bool
    {
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $code = trim((string) ($data['code'] ?? ''));

        if (! $this->verifyCode($email, $code)) {
            return false;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return false;
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(60),
        ])->save();

        $user->tokens()->delete();

        Cache::forget($this->cacheKey($email));

        return true;
    }

    private function cacheKey(string $email): string
    {
        return self::CACHE_PREFIX . sha1(strtolower(trim($email)));
    }
}
