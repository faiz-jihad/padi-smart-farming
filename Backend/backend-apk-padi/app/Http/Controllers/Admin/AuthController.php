<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginAdminRequest;
use App\Services\Admin\AdminAuditLogger;
use App\Services\Admin\AdminAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        $user = Auth::user();

        if (app(AdminAuthService::class)->canAccessAdmin($user)) {
            if ($user?->role === \App\Enums\UserRole::Admin->value) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('admin.agriculture.index');
        }

        return view('admin.auth.login', [
            'title' => 'Login Admin & PPL',
        ]);
    }

    public function login(
        LoginAdminRequest $request,
        AdminAuthService $auth,
        AdminAuditLogger $audit,
    ): RedirectResponse {
        $result = $auth->attempt($request->validated(), $request, $audit);

        if (! $result['ok']) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => $result['message'] ?? 'Login gagal.']);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $targetRoute = ($user?->role === \App\Enums\UserRole::Admin->value)
            ? route('admin.dashboard')
            : route('admin.agriculture.index');

        return redirect()->intended($targetRoute)
            ->with('status', 'Login berhasil.');
    }

    public function logout(Request $request, AdminAuthService $auth): RedirectResponse
    {
        $auth->logout($request);

        return redirect()->route('admin.login')
            ->with('status', 'Sesi admin sudah keluar.');
    }
}
