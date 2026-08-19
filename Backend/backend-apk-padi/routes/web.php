<?php

use App\Http\Controllers\Admin\AgricultureController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiseaseController;
use App\Http\Controllers\Admin\EarlyWarningController;
use App\Http\Controllers\Admin\FarmerPublicProfileAdminController;
use App\Http\Controllers\Admin\MarketplaceController;
use App\Http\Controllers\Admin\SoilController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WeatherController;
use App\Http\Controllers\Farmer\AuthController as FarmerAuthController;
use App\Http\Controllers\Farmer\ProfileWebsiteController;
use App\Http\Controllers\Public\FarmerPublicProfileController;
use Illuminate\Support\Facades\Route;

// ─── Public Subdomain Routes ─────────────────────────────────────────────────
// Must be matched first so {subdomain}.domain/ is resolved before apex '/' routes
Route::domain('{subdomain}.' . config('domains.base', 'localhost'))
    ->name('farmer.public.')
    ->group(function (): void {
        Route::get('/', [FarmerPublicProfileController::class, 'show'])->name('show');
    });

// ─── Public Direct Path Route (Local Dev / Fallback) ─────────────────────────
Route::get('/profile/{subdomain}', [FarmerPublicProfileController::class, 'show'])
    ->name('farmer.public.direct');


// ─── Default Redirects (Apex domain only) ───────────────────────────────────
Route::redirect('/', '/admin');
Route::redirect('/login', '/admin/login')->name('login');

// ─── Admin Auth ─────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
});

Route::post('/admin/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('admin.logout');

// ─── Admin Panel ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin.web'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/notifications/read', [DashboardController::class, 'markNotificationsRead'])
            ->name('notifications.read');

        Route::get('/agriculture', [AgricultureController::class, 'index'])->name('agriculture.index');
        Route::post('/agriculture', [AgricultureController::class, 'store'])->name('agriculture.store');
        Route::patch('/agriculture/{farm}', [AgricultureController::class, 'update'])->name('agriculture.update');
        Route::delete('/agriculture/{farm}', [AgricultureController::class, 'destroy'])->name('agriculture.destroy');

        Route::get('/disease', [DiseaseController::class, 'index'])->name('disease.index');
        Route::patch('/disease/reports/{report}', [DiseaseController::class, 'updateReport'])->name('disease.reports.update');

        Route::get('/early-warning', [EarlyWarningController::class, 'index'])->name('early-warning.index');
        Route::post('/early-warning', [EarlyWarningController::class, 'store'])->name('early-warning.store');

        // Weather Management Routes
        Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');
        Route::get('/weather/map', [WeatherController::class, 'map'])->name('weather.map');
        Route::get('/weather/inspect', [WeatherController::class, 'inspectLocation'])->name('weather.inspect');
        Route::get('/weather/history', [WeatherController::class, 'history'])->name('weather.history');
        Route::post('/weather/refresh', [WeatherController::class, 'refresh'])->name('weather.refresh');
        Route::post('/weather/refresh-all', [WeatherController::class, 'refreshAll'])->name('weather.refresh-all');
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

        // Knowledge Base Routes
        Route::get('/knowledge', [\App\Http\Controllers\Admin\KnowledgeController::class, 'index'])->name('knowledge.index');
        Route::get('/knowledge/create', [\App\Http\Controllers\Admin\KnowledgeController::class, 'create'])->name('knowledge.create');
        Route::post('/knowledge', [\App\Http\Controllers\Admin\KnowledgeController::class, 'store'])->name('knowledge.store');
        Route::get('/knowledge/{slug}', [\App\Http\Controllers\Admin\KnowledgeController::class, 'show'])->name('knowledge.show');
        Route::get('/knowledge/{article}/edit', [\App\Http\Controllers\Admin\KnowledgeController::class, 'edit'])->name('knowledge.edit');
        Route::patch('/knowledge/{article}', [\App\Http\Controllers\Admin\KnowledgeController::class, 'update'])->name('knowledge.update');
        Route::delete('/knowledge/{article}', [\App\Http\Controllers\Admin\KnowledgeController::class, 'destroy'])->name('knowledge.destroy');

        // ─── Admin-Only Sub-Routes ──────────────────────────────────────────
        Route::middleware('role:admin')->group(function (): void {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

            Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
            Route::get('/marketplace/create', [MarketplaceController::class, 'create'])->name('marketplace.create');
            Route::post('/marketplace', [MarketplaceController::class, 'store'])->name('marketplace.store');
            Route::get('/marketplace/{listing}/edit', [MarketplaceController::class, 'edit'])->name('marketplace.edit');
            Route::patch('/marketplace/listings/{listing}', [MarketplaceController::class, 'updateListing'])->name('marketplace.listings.update');
            Route::delete('/marketplace/listings/{listing}', [MarketplaceController::class, 'destroy'])->name('marketplace.listings.destroy');
            Route::patch('/marketplace/offers/{offer}', [MarketplaceController::class, 'updateOffer'])->name('marketplace.offers.update');

            Route::get('/broadcast', [BroadcastController::class, 'index'])->name('broadcast.index');
            Route::post('/broadcast', [BroadcastController::class, 'store'])->name('broadcast.store');
            Route::patch('/broadcast/{broadcast}', [BroadcastController::class, 'update'])->name('broadcast.update');
            Route::delete('/broadcast/{broadcast}', [BroadcastController::class, 'destroy'])->name('broadcast.destroy');

            Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');

            // Farmer Public Profile Management (Admin)
            Route::get('/farmer-profiles', [FarmerPublicProfileAdminController::class, 'index'])->name('farmer-profiles.index');
            Route::get('/farmer-profiles/create', [FarmerPublicProfileAdminController::class, 'create'])->name('farmer-profiles.create');
            Route::post('/farmer-profiles', [FarmerPublicProfileAdminController::class, 'store'])->name('farmer-profiles.store');
            Route::get('/farmer-profiles/{farmerProfile:subdomain}/edit', [FarmerPublicProfileAdminController::class, 'edit'])->name('farmer-profiles.edit');
            Route::patch('/farmer-profiles/{farmerProfile:subdomain}', [FarmerPublicProfileAdminController::class, 'update'])->name('farmer-profiles.update');
            Route::delete('/farmer-profiles/{farmerProfile:subdomain}', [FarmerPublicProfileAdminController::class, 'destroy'])->name('farmer-profiles.destroy');
            Route::post('/farmer-profiles/{farmerProfile:subdomain}/verify', [FarmerPublicProfileAdminController::class, 'verify'])->name('farmer-profiles.verify');
            Route::post('/farmer-profiles/{farmerProfile:subdomain}/reject', [FarmerPublicProfileAdminController::class, 'reject'])->name('farmer-profiles.reject');
            Route::post('/farmer-profiles/{farmerProfile:subdomain}/suspend', [FarmerPublicProfileAdminController::class, 'suspend'])->name('farmer-profiles.suspend');
            Route::post('/farmer-profiles/{farmerProfile:subdomain}/restore', [FarmerPublicProfileAdminController::class, 'restore'])->name('farmer-profiles.restore');
            Route::post('/farmer-profiles/{farmerProfile:subdomain}/listings', [FarmerPublicProfileAdminController::class, 'storeListing'])->name('farmer-profiles.listings.store');
            Route::patch('/farmer-profiles/{farmerProfile:subdomain}/listings/{listing}', [FarmerPublicProfileAdminController::class, 'updateListing'])->name('farmer-profiles.listings.update');
            Route::delete('/farmer-profiles/{farmerProfile:subdomain}/listings/{listing}', [FarmerPublicProfileAdminController::class, 'destroyListing'])->name('farmer-profiles.listings.destroy');
            Route::post('/farmer-profiles/{farmerProfile:subdomain}/gallery', [FarmerPublicProfileAdminController::class, 'storeGallery'])->name('farmer-profiles.gallery.store');
            Route::delete('/farmer-profiles/{farmerProfile:subdomain}/gallery/{gallery}', [FarmerPublicProfileAdminController::class, 'destroyGallery'])->name('farmer-profiles.gallery.destroy');
        });
    });



// ─── Farmer Panel Auth ───────────────────────────────────────────────────────
Route::middleware('guest:farmer')->group(function (): void {
    Route::get('/farmer/login', [FarmerAuthController::class, 'showLogin'])->name('farmer.login');
    Route::post('/farmer/login', [FarmerAuthController::class, 'login'])->name('farmer.login.submit');
});

Route::post('/farmer/logout', [FarmerAuthController::class, 'logout'])
    ->middleware('auth:farmer')
    ->name('farmer.logout');

// ─── Farmer Panel — Website Management ───────────────────────────────────────
Route::middleware(['auth:farmer', 'farmer.web'])
    ->prefix('farmer')
    ->name('farmer.')
    ->group(function (): void {
        // "Website Saya" dashboard
        Route::get('/website', [ProfileWebsiteController::class, 'index'])->name('website.index');

        // Edit profile details
        Route::get('/website/edit', [ProfileWebsiteController::class, 'edit'])->name('website.edit');
        Route::post('/website/edit', [ProfileWebsiteController::class, 'update'])->name('website.update');

        // Gallery routes
        Route::post('/website/gallery', [ProfileWebsiteController::class, 'storeGallery'])->name('website.gallery.store');
        Route::delete('/website/gallery/{gallery}', [ProfileWebsiteController::class, 'destroyGallery'])->name('website.gallery.destroy');

        // Privacy / section controls
        Route::get('/website/sections', [ProfileWebsiteController::class, 'sections'])->name('website.sections');
        Route::post('/website/sections', [ProfileWebsiteController::class, 'updateSections'])->name('website.sections.update');

        // Template selection
        Route::get('/website/template', [ProfileWebsiteController::class, 'template'])->name('website.template');
        Route::post('/website/template', [ProfileWebsiteController::class, 'selectTemplate'])->name('website.template.select');

        // Subdomain
        Route::get('/website/subdomain/check', [ProfileWebsiteController::class, 'checkSubdomain'])->name('website.subdomain.check');
        Route::post('/website/subdomain', [ProfileWebsiteController::class, 'updateSubdomain'])->name('website.subdomain.update');

        // Preview (authenticated only)
        Route::get('/website/preview', [ProfileWebsiteController::class, 'preview'])->name('website.preview');

        // Publish / Unpublish
        Route::post('/website/publish', [ProfileWebsiteController::class, 'publish'])->name('website.publish');
        Route::post('/website/unpublish', [ProfileWebsiteController::class, 'unpublish'])->name('website.unpublish');
    });

