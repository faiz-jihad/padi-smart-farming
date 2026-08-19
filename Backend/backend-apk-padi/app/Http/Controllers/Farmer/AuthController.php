<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('farmer')->check()) {
            return redirect()->route('farmer.website.index');
        }

        return view('farmer.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('farmer')->attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::guard('farmer')->user();

            // Ensure the logged-in user is actually a farmer
            if ($user->role !== 'farmer') {
                Auth::guard('farmer')->logout();

                return back()->withErrors([
                    'email' => 'Akun ini bukan akun petani.',
                ]);
            }

            if ($user->status !== 'active') {
                Auth::guard('farmer')->logout();

                return back()->withErrors([
                    'email' => 'Akun Anda belum aktif atau sedang ditangguhkan.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended(route('farmer.website.index'));
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak sesuai.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('farmer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('farmer.login');
    }
}
