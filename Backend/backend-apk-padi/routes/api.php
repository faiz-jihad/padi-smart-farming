<?php

use App\Http\Controllers\AlertSubscriptionController;
use App\Http\Controllers\Api\V1\Admin\AdminOverviewController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\FarmActivityController as ApiV1FarmActivityController;
use App\Http\Controllers\Api\V1\FarmController as ApiV1FarmController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\MapController;
use App\Http\Controllers\Api\V1\PlantingCalendarController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RegionController;
use App\Http\Controllers\Api\V1\SoilDetectionController;
use App\Http\Controllers\Api\V1\WeatherController;
use App\Http\Controllers\CommunityReportController;
use App\Http\Controllers\ContractPaymentController;
use App\Http\Controllers\CropSeasonController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\FarmerProfileController;
use App\Http\Controllers\FertilizerRuleController;
use App\Http\Controllers\HarvestController;
use App\Http\Controllers\ListingImageController;
use App\Http\Controllers\MarketListingController;
use App\Http\Controllers\MarketOfferController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PartnerFavoriteController;
use App\Http\Controllers\PplValidationController;
use App\Http\Controllers\PurchaseContractController;
use App\Http\Controllers\RiceVarietyController;
use App\Http\Controllers\WeatherSnapshotController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - P.A.D.I. Smart Farming System
|--------------------------------------------------------------------------
|
| API V1 endpoints for mobile, web, GIS, weather, soil detection, 
| marketplace, and administrative features.
|
*/

Route::prefix('v1')->group(function (): void {
    // ─────────────────────────────────────────────
    // Health Check Endpoint
    // ─────────────────────────────────────────────
    Route::get('health', function (): JsonResponse {
        return response()->json([
            'status' => 'ok',
            'system' => 'P.A.D.I. Smart Farming API',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    // ─────────────────────────────────────────────
    // Public Region, Location & Map GIS API
    // ─────────────────────────────────────────────
    Route::prefix('regions')->group(function (): void {
        Route::get('provinces', [RegionController::class, 'provinces']);
        Route::get('regencies', [RegionController::class, 'regencies']);
        Route::get('districts', [RegionController::class, 'districts']);
        Route::get('villages', [RegionController::class, 'villages']);
        Route::get('search', [RegionController::class, 'search']);
    });

    Route::prefix('location')->group(function (): void {
        Route::get('resolve', [LocationController::class, 'resolve']);
    });

    Route::prefix('maps')->group(function (): void {
        Route::get('districts', [MapController::class, 'districts']);
        Route::get('villages', [MapController::class, 'villages']);
    });

    // ─────────────────────────────────────────────
    // Authentication API
    // ─────────────────────────────────────────────
    Route::prefix('auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [PasswordResetController::class, 'forgot']);
        Route::post('reset-password', [PasswordResetController::class, 'reset']);

        Route::middleware(['auth:sanctum', 'account.active'])->group(function (): void {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
        });
    });

    // ─────────────────────────────────────────────
    // Protected API Endpoints (Auth Required)
    // ─────────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'account.active'])->group(function (): void {
        // User Profile Management
        Route::get('profile', [ProfileController::class, 'show']);
        Route::patch('profile', [ProfileController::class, 'update']);
        Route::put('profile/password', [ProfileController::class, 'changePassword']);

        // Farm CRUD (V1)
        Route::get('farms', [ApiV1FarmController::class, 'index']);
        Route::post('farms', [ApiV1FarmController::class, 'store']);
        Route::get('farms/{farm}', [ApiV1FarmController::class, 'show']);
        Route::put('farms/{farm}', [ApiV1FarmController::class, 'update']);
        Route::delete('farms/{farm}', [ApiV1FarmController::class, 'destroy']);
        Route::get('farms/{farm}/planting-calendar', [PlantingCalendarController::class, 'byFarm']);

        // Planting Calendars & Best Planting Time Advisor API
        Route::get('planting-calendars', [PlantingCalendarController::class, 'index']);
        Route::get('districts/{district}/planting-calendar', [PlantingCalendarController::class, 'byDistrict']);
        Route::post('planting-calendar/recommend-planting-window', [PlantingCalendarController::class, 'recommendPlantingWindow']);

        // Knowledge Base API
        Route::get('knowledge-base', [\App\Http\Controllers\Api\V1\KnowledgeController::class, 'index']);
        Route::get('knowledge-base/{slug}', [\App\Http\Controllers\Api\V1\KnowledgeController::class, 'show']);

        // Farmer Profiles & Resources
        Route::get('farmers', [FarmerProfileController::class, 'index']);
        // Farm Activities CRUD (V1)
        Route::get('farm-activities', [ApiV1FarmActivityController::class, 'index']);
        Route::post('farm-activities', [ApiV1FarmActivityController::class, 'store']);
        Route::get('farm-activities/{farmActivity}', [ApiV1FarmActivityController::class, 'show']);
        Route::patch('farm-activities/{farmActivity}', [ApiV1FarmActivityController::class, 'update']);
        Route::delete('farm-activities/{farmActivity}', [ApiV1FarmActivityController::class, 'destroy']);
        Route::get('harvests', [HarvestController::class, 'index']);
        Route::get('rice-varieties', [RiceVarietyController::class, 'index']);
        Route::get('weather-snapshots', [WeatherSnapshotController::class, 'index']);
        Route::get('fertilizer-rules', [FertilizerRuleController::class, 'index']);

        // Weather Service API (OpenWeather, AgroMonitoring & BMKG)
        Route::prefix('weather')->group(function (): void {
            Route::post('current', [WeatherController::class, 'currentWeather']);
            Route::post('forecast', [WeatherController::class, 'forecast']);
            Route::post('bmkg-forecast', [WeatherController::class, 'bmkgForecast']);
            Route::get('history', [WeatherController::class, 'history']);
            Route::post('city', [WeatherController::class, 'byCity']);
        });

        // Soil Detection AI & Irrigation Schedule API
        Route::prefix('soil-detections')->group(function (): void {
            Route::get('/', [SoilDetectionController::class, 'index']);
            Route::post('/', [SoilDetectionController::class, 'store']);
            Route::get('fetch-api-data', [SoilDetectionController::class, 'fetchApiData']);
            Route::get('/{soilDetection}', [SoilDetectionController::class, 'show']);
            Route::get('/{soilDetection}/irrigation-schedule', [SoilDetectionController::class, 'irrigationSchedule']);
        });

        // Crop Seasons
        Route::get('crop-seasons', [CropSeasonController::class, 'index']);
        Route::post('crop-seasons', [CropSeasonController::class, 'store']);

        // Marketplace API
        Route::get('market-listings', [MarketListingController::class, 'index']);
        Route::get('listing-images', [ListingImageController::class, 'index']);
        Route::get('market-offers', [MarketOfferController::class, 'index']);
        Route::get('purchase-contracts', [PurchaseContractController::class, 'index']);
        Route::get('contract-payments', [ContractPaymentController::class, 'index']);

        // System Services & Notifications
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/send-push', [NotificationController::class, 'sendPush']);
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::get('ppl-validations', [PplValidationController::class, 'index']);
        Route::get('community-reports', [CommunityReportController::class, 'index']);
        Route::get('alert-subscriptions', [AlertSubscriptionController::class, 'index']);
        Route::get('device-tokens', [DeviceTokenController::class, 'index']);
        Route::post('device-tokens', [DeviceTokenController::class, 'store']);
        Route::delete('device-tokens', [DeviceTokenController::class, 'destroy']);
        Route::get('partner-favorites', [PartnerFavoriteController::class, 'index']);

        // Admin Management Endpoint
        Route::middleware('role:admin')->group(function (): void {
            Route::match(['get', 'post', 'patch', 'delete'], 'admin/{resource?}/{id?}', AdminOverviewController::class);
        });
    });
});
