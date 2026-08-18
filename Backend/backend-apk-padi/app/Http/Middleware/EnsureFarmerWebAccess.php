<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureFarmerWebAccess
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('farmer')->user();

        if (
            ! $user
            || $user->role !== UserRole::Farmer->value
            || $user->status !== UserStatus::Active->value
        ) {
            Auth::guard('farmer')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('farmer.login')
                ->withErrors(['email' => 'Silakan login menggunakan akun petani aktif.']);
        }

        return $next($request);
    }
}
