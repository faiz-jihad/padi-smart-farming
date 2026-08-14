<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        if (! $this->mailerIsConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Pemulihan password belum aktif. Konfigurasikan mailer Laravel terlebih dahulu.',
            ], 503);
        }

        $status = Password::sendResetLink($request->validated());

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan reset password belum dapat diproses.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tautan reset password telah dikirim.',
            'data' => null,
        ]);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        if (! $this->mailerIsConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Reset password belum aktif. Konfigurasikan mailer Laravel terlebih dahulu.',
            ], 503);
        }

        $status = Password::reset(
            $request->validated(),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'success' => false,
                'message' => 'Token reset password tidak valid atau sudah kedaluwarsa.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset. Silakan login kembali.',
            'data' => null,
        ]);
    }

    private function mailerIsConfigured(): bool
    {
        $mailer = (string) Config::get('mail.default');

        return ! in_array($mailer, ['array', 'log'], true);
    }
}
