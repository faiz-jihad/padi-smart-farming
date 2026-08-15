<?php

namespace App\Providers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
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
