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

        // Rate Limiter: Dashboard AJAX sync & Polling (Maks 60 requests per menit)
        RateLimiter::for('admin-sync', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak permintaan pembaruan dashboard. Harap tunggu sejenak.',
                ], 429);
            });
        });

        // Rate Limiter: Weather & Soil External API Refresh (Maks 30 requests per menit)
        RateLimiter::for('weather-refresh', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Batas pembaruan API telemetri cuaca tercapai (maks 30/menit). Harap tunggu beberapa detik.',
                ], 429);
            });
        });

        // Rate Limiter: Broadcast Peringatan Dini (Maks 15 requests per menit)
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
