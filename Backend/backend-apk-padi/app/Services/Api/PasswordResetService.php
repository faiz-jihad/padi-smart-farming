<?php

namespace App\Services\Api;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetService
{
    public function mailerIsConfigured(): bool
    {
        $mailer = (string) Config::get('mail.default');

        return ! in_array($mailer, ['array', 'log'], true);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function sendResetLink(array $data): string
    {
        return Password::sendResetLink($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function reset(array $data): string
    {
        return Password::reset(
            $data,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );
    }
}
