<?php

namespace App\Providers;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');

        // 1. Rate Limiter: General API Traffic (Maks 120 requests/menit)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak permintaan API. Harap perlambat laju request Anda.',
                ], 429);
            });
        });

        // 2. Rate Limiter: Auth Endpoints (Maks 10 percobaan/menit per IP)
        RateLimiter::for('auth-strict', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan autentikasi. Demi keamanan, silakan coba lagi dalam 1 menit.',
                ], 429);
            });
        });

        // 3. Rate Limiter: AI Disease Scanner & Vision Model (Maks 15 requests/menit)
        RateLimiter::for('ai-scans', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Batas kuota pemindaian AI tercapai (maks 15/menit). Harap tunggu sejenak.',
                ], 429);
            });
        });

        // 4. Rate Limiter: Device Push Notifications (Maks 20 requests/menit)
        RateLimiter::for('push-notifications', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Batas siaran notifikasi tercapai (maks 20/menit).',
                ], 429);
            });
        });

        // 5. Rate Limiter: Dashboard AJAX sync & Polling (Maks 60 requests/menit)
        RateLimiter::for('admin-sync', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak permintaan pembaruan dashboard. Harap tunggu sejenak.',
                ], 429);
            });
        });

        // 6. Rate Limiter: Weather & Soil External API Refresh (Maks 30 requests/menit)
        RateLimiter::for('weather-refresh', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Batas pembaruan API telemetri cuaca tercapai (maks 30/menit). Harap tunggu beberapa detik.',
                ], 429);
            });
        });

        // 7. Rate Limiter: Broadcast Peringatan Dini (Maks 15 requests/menit)
        RateLimiter::for('broadcast-alert', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Batas pengiriman broadcast peringatan tercapai (maks 15/menit).',
                ], 429);
            });
        });

        View::composer('components.admin-navbar', function ($view): void {
            $userId = Auth::id();

            if (! $userId || ! Schema::hasTable('notifications')) {
                $view->with([
                    'adminNavNotifications' => collect(),
                    'adminUnreadNotifications' => 0,
                ]);

                return;
            }

            $notifications = Notification::query()
                ->where('user_id', $userId)
                ->latest('id')
                ->limit(6)
                ->get();

            $view->with([
                'adminNavNotifications' => $notifications,
                'adminUnreadNotifications' => Notification::query()
                    ->where('user_id', $userId)
                    ->whereNull('read_at')
                    ->count(),
            ]);
        });
    }
}
