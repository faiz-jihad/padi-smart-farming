<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\VerifyResetCodeRequest;
use App\Services\Api\PasswordResetService;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    public function forgot(
        ForgotPasswordRequest $request,
        PasswordResetService $passwords
    ): JsonResponse {
        if (! $passwords->mailerIsConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Pemulihan password belum aktif. Konfigurasikan mailer Laravel terlebih dahulu.',
            ], 503);
        }

        $sent = $passwords->sendResetCode($request->validated());

        if (! $sent) {
            return response()->json([
                'success' => false,
                'message' => 'Kode reset password gagal dikirim. Silakan coba lagi.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode reset password telah dikirim ke email.',
            'data' => null,
        ]);
    }

    public function verify(
        VerifyResetCodeRequest $request,
        PasswordResetService $passwords
    ): JsonResponse {
        $data = $request->validated();

        $valid = $passwords->verifyCode(
            $data['email'],
            $data['code']
        );

        if (! $valid) {
            return response()->json([
                'success' => false,
                'message' => 'Kode reset password tidak valid atau sudah kedaluwarsa.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode verifikasi valid.',
            'data' => null,
        ]);
    }

    public function reset(
        ResetPasswordRequest $request,
        PasswordResetService $passwords
    ): JsonResponse {
        $status = $passwords->reset($request->validated());

        if (! $status) {
            return response()->json([
                'success' => false,
                'message' => 'Kode reset password tidak valid atau sudah kedaluwarsa.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset. Silakan login kembali.',
            'data' => null,
        ]);
    }
}
