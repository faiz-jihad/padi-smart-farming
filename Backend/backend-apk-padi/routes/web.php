<?php

use App\Http\Controllers\Admin\AgricultureController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiseaseController;
use App\Http\Controllers\Admin\EarlyWarningController;
use App\Http\Controllers\Admin\MarketplaceController;
use App\Http\Controllers\Admin\SoilController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WeatherController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');
Route::redirect('/login', '/admin/login')->name('login');

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
});

Route::post('/admin/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('admin.logout');

Route::middleware(['auth', 'admin.web'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/notifications/read', [DashboardController::class, 'markNotificationsRead'])
            ->name('notifications.read');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/agriculture', [AgricultureController::class, 'index'])->name('agriculture.index');

        Route::get('/disease', [DiseaseController::class, 'index'])->name('disease.index');
        Route::patch('/disease/reports/{report}', [DiseaseController::class, 'updateReport'])->name('disease.reports.update');

        Route::get('/early-warning', [EarlyWarningController::class, 'index'])->name('early-warning.index');
        Route::post('/early-warning', [EarlyWarningController::class, 'store'])->name('early-warning.store');

        Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
        Route::patch('/marketplace/listings/{listing}', [MarketplaceController::class, 'updateListing'])->name('marketplace.listings.update');
        Route::patch('/marketplace/offers/{offer}', [MarketplaceController::class, 'updateOffer'])->name('marketplace.offers.update');

        Route::get('/broadcast', [BroadcastController::class, 'index'])->name('broadcast.index');
        Route::post('/broadcast', [BroadcastController::class, 'store'])->name('broadcast.store');
        Route::patch('/broadcast/{broadcast}', [BroadcastController::class, 'update'])->name('broadcast.update');
        Route::delete('/broadcast/{broadcast}', [BroadcastController::class, 'destroy'])->name('broadcast.destroy');

        Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');

        // Weather Management Routes
        Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');
        Route::get('/weather/map', [WeatherController::class, 'map'])->name('weather.map');
        Route::get('/weather/inspect', [WeatherController::class, 'inspectLocation'])->name('weather.inspect');
        Route::get('/weather/history', [WeatherController::class, 'history'])->name('weather.history');
        Route::post('/weather/refresh', [WeatherController::class, 'refresh'])->name('weather.refresh');
        Route::post('/weather/export', [WeatherController::class, 'export'])->name('weather.export');
        Route::get('/weather/settings', [WeatherController::class, 'settings'])->name('weather.settings');
        Route::patch('/weather/settings', [WeatherController::class, 'updateSettings'])->name('weather.settings.update');
        Route::post('/weather/test-connection', [WeatherController::class, 'testConnection'])->name('weather.test-connection');
        Route::post('/weather/clear-cache', [WeatherController::class, 'clearCache'])->name('weather.clear-cache');

        // Soil Detection Routes
        Route::get('/soil', [SoilController::class, 'index'])->name('soil.index');
        Route::get('/soil/create', [SoilController::class, 'create'])->name('soil.create');
        Route::post('/soil', [SoilController::class, 'store'])->name('soil.store');
        Route::post('/soil/export', [SoilController::class, 'export'])->name('soil.export');
        Route::get('/soil/{soil}', [SoilController::class, 'show'])->name('soil.show');
        Route::delete('/soil/{soil}', [SoilController::class, 'destroy'])->name('soil.destroy');
    });
