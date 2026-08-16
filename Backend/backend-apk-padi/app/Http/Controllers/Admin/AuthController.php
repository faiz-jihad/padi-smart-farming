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
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login', [
            'title' => 'Login Admin',
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
                ->withErrors(['email' => $result['message'] ?? 'Login admin gagal.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'))
            ->with('status', 'Login admin berhasil.');
    }

    public function logout(Request $request, AdminAuthService $auth): RedirectResponse
    {
        $auth->logout($request);

        return redirect()->route('admin.login')
            ->with('status', 'Sesi admin sudah keluar.');
    }
}
