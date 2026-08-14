<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->status !== UserStatus::Active->value) {
            return response()->json([
                'success' => false,
                'message' => 'Akun belum aktif atau sedang ditangguhkan.',
            ], 403);
        }

        return $next($request);
    }
}
