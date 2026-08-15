<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Services\Api\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function forgot(ForgotPasswordRequest $request, PasswordResetService $passwords): JsonResponse
    {
        if (! $passwords->mailerIsConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Pemulihan password belum aktif. Konfigurasikan mailer Laravel terlebih dahulu.',
            ], 503);
        }

        $status = $passwords->sendResetLink($request->validated());

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

    public function reset(ResetPasswordRequest $request, PasswordResetService $passwords): JsonResponse
    {
        if (! $passwords->mailerIsConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Reset password belum aktif. Konfigurasikan mailer Laravel terlebih dahulu.',
            ], 503);
        }

        $status = $passwords->reset($request->validated());

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
}
