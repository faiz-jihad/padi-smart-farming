<?php

namespace App\Http\Middleware;

use App\Models\GovernmentSubscription;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GovernmentTokenMiddleware
{
    /**
     * Handle an incoming request for B2G Government API
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization', '');
        
        if (! preg_match('/Bearer\s+([0-9]{6})/i', $authHeader, $matches)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Otentikasi gagal. Header Authorization Bearer dengan 6-digit token angka wajib disertakan.',
                'error_code' => 'INVALID_TOKEN_FORMAT',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $rawToken = $matches[1];
        $tokenHash = hash('sha256', $rawToken);

        $subscription = GovernmentSubscription::where('token_hash', $tokenHash)->first();

        if (! $subscription) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Token akses pemerintah tidak ditemukan atau tidak valid.',
                'error_code' => 'INVALID_TOKEN',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($subscription->revoked_at !== null || $subscription->status === GovernmentSubscription::STATUS_REVOKED) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Akses ditolak. Token akses subscription telah dicabut (revoked).',
                'error_code' => 'TOKEN_REVOKED',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($subscription->expires_at !== null && $subscription->expires_at->isPast()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Akses ditolak. Masa berlaku subscription telah berakhir (expired pada ' . $subscription->expires_at->toIso8601String() . ').',
                'error_code' => 'TOKEN_EXPIRED',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($subscription->status !== GovernmentSubscription::STATUS_ACTIVE) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Akses ditolak. Status subscription saat ini tidak aktif (' . $subscription->status . ').',
                'error_code' => 'SUBSCRIPTION_INACTIVE',
            ], Response::HTTP_FORBIDDEN);
        }

        $request->attributes->set('government_subscription', $subscription);

        return $next($request);
    }
}
