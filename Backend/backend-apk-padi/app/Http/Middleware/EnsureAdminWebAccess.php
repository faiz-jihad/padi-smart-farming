<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminWebAccess
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        if ($user->status !== UserStatus::Active->value) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->withErrors([
                    'email' => 'Silakan login memakai akun internal aktif.',
                ]);
        }

        if (! in_array($user->role, [
            UserRole::Admin->value,
            UserRole::ExtensionOfficer->value,
        ], true)) {
            abort(403);
        }

        return $next($request);
    }
}
