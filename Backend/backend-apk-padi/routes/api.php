<?php

use App\Http\Controllers\AlertSubscriptionController;
use App\Http\Controllers\Api\V1\Admin\AdminOverviewController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\FarmActivityController as ApiV1FarmActivityController;
use App\Http\Controllers\Api\V1\FarmController as ApiV1FarmController;
use App\Http\Controllers\Api\V1\DiseaseScanController;
use App\Http\Controllers\Api\V1\EventController;
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
use App\Http\Controllers\Api\V1\HarvestController;
use App\Http\Controllers\ListingImageController;
use App\Http\Controllers\Api\V1\MarketListingController;
use App\Http\Controllers\MarketOfferController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PartnerFavoriteController;
use App\Http\Controllers\PplValidationController;
use App\Http\Controllers\PurchaseContractController;
use App\Http\Controllers\RiceVarietyController;
use App\Http\Controllers\WeatherSnapshotController;
use App\Http\Controllers\AdminBroadcastController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function (): void {
    Route::get('health', function (): JsonResponse {
        return response()->json([
            'status' => 'ok',
            'system' => 'P.A.D.I. Smart Farming API',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

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

    Route::prefix('auth')->middleware('throttle:auth-strict')->group(function (): void {
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

    Route::middleware(['auth:sanctum', 'account.active'])->group(function (): void {
        Route::get('profile', [ProfileController::class, 'show']);
        Route::patch('profile', [ProfileController::class, 'update']);
        Route::put('profile/password', [ProfileController::class, 'changePassword']);

        Route::get('farms', [ApiV1FarmController::class, 'index']);
        Route::post('farms', [ApiV1FarmController::class, 'store']);
        Route::get('farms/{farm}', [ApiV1FarmController::class, 'show']);
        Route::put('farms/{farm}', [ApiV1FarmController::class, 'update']);
        Route::delete('farms/{farm}', [ApiV1FarmController::class, 'destroy']);
        Route::get('farms/{farm}/planting-calendar', [PlantingCalendarController::class, 'byFarm']);

        Route::get('planting-calendars', [PlantingCalendarController::class, 'index']);
        Route::get('planting-calendars/{plantingCalendar}', [PlantingCalendarController::class, 'show']);
        Route::get('districts/{district}/planting-calendar', [PlantingCalendarController::class, 'byDistrict']);
        Route::post('planting-calendar/recommend-planting-window', [PlantingCalendarController::class, 'recommendPlantingWindow']);

        Route::middleware('role:extension_officer|admin')->group(function (): void {
            Route::post('planting-calendars', [PlantingCalendarController::class, 'store']);
            Route::patch('planting-calendars/{plantingCalendar}', [PlantingCalendarController::class, 'update']);
            Route::put('planting-calendars/{plantingCalendar}', [PlantingCalendarController::class, 'update']);
            Route::delete('planting-calendars/{plantingCalendar}', [PlantingCalendarController::class, 'destroy']);
        });

        Route::get('knowledge-base', [\App\Http\Controllers\Api\V1\KnowledgeController::class, 'index']);
        Route::get('knowledge-base/{slug}', [\App\Http\Controllers\Api\V1\KnowledgeController::class, 'show']);

        Route::get('farmers', [FarmerProfileController::class, 'index']);

        Route::get('farm-activities', [ApiV1FarmActivityController::class, 'index']);
        Route::post('farm-activities', [ApiV1FarmActivityController::class, 'store']);
        Route::get('farm-activities/{farmActivity}', [ApiV1FarmActivityController::class, 'show']);
        Route::patch('farm-activities/{farmActivity}', [ApiV1FarmActivityController::class, 'update']);
        Route::delete('farm-activities/{farmActivity}', [ApiV1FarmActivityController::class, 'destroy']);

        Route::get('harvests', [HarvestController::class, 'index']);
        Route::post('harvests', [HarvestController::class, 'store']);
        Route::get('harvests/{harvest}', [HarvestController::class, 'show']);
        Route::patch('harvests/{harvest}', [HarvestController::class, 'update']);
        Route::delete('harvests/{harvest}', [HarvestController::class, 'destroy']);

        Route::get('rice-varieties', [RiceVarietyController::class, 'index']);
        Route::get('weather-snapshots', [WeatherSnapshotController::class, 'index']);
        Route::get('fertilizer-rules', [FertilizerRuleController::class, 'index']);

        Route::prefix('weather')->group(function (): void {
            Route::post('current', [WeatherController::class, 'currentWeather']);
            Route::post('forecast', [WeatherController::class, 'forecast']);
            Route::post('bmkg-forecast', [WeatherController::class, 'bmkgForecast']);
            Route::get('history', [WeatherController::class, 'history']);
            Route::post('city', [WeatherController::class, 'byCity']);
        });

        Route::prefix('soil-detections')->group(function (): void {
            Route::get('/', [SoilDetectionController::class, 'index']);
            Route::post('/', [SoilDetectionController::class, 'store']);
            Route::get('fetch-api-data', [SoilDetectionController::class, 'fetchApiData']);
            Route::get('/{soilDetection}', [SoilDetectionController::class, 'show']);
            Route::get('/{soilDetection}/irrigation-schedule', [SoilDetectionController::class, 'irrigationSchedule']);
        });

        Route::get('crop-seasons', [CropSeasonController::class, 'index']);
        Route::post('crop-seasons', [CropSeasonController::class, 'store']);

        Route::get('market-listings', [MarketListingController::class, 'index']);
        Route::post('market-listings', [MarketListingController::class, 'store']);
        Route::get('market-listings/{marketListing}', [MarketListingController::class, 'show']);
        Route::patch('market-listings/{marketListing}', [MarketListingController::class, 'update']);
        Route::delete('market-listings/{marketListing}', [MarketListingController::class, 'destroy']);

        Route::get('listing-images', [ListingImageController::class, 'index']);

        Route::get('market-listings/{marketListing}/offers', [MarketOfferController::class, 'listingOffers']);
        Route::get('market-offers', [MarketOfferController::class, 'index']);
        Route::post('market-offers', [MarketOfferController::class, 'store']);
        Route::put('market-offers/{marketOffer}', [MarketOfferController::class, 'update']);

        Route::get('purchase-contracts', [PurchaseContractController::class, 'index']);
        Route::get('contract-payments', [ContractPaymentController::class, 'index']);

        Route::get('admin-broadcasts', [AdminBroadcastController::class, 'index']);
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/send-push', [NotificationController::class, 'sendPush'])
            ->middleware(['role:extension_officer|admin', 'throttle:push-notifications']);
        Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::get('realtime/stream', [\App\Http\Controllers\RealtimeStreamController::class, 'stream']);
        Route::get('ppl-validations', [PplValidationController::class, 'index']);
        Route::get('disease-scans', [DiseaseScanController::class, 'index']);
        Route::post('disease-scans', [DiseaseScanController::class, 'store'])->middleware('throttle:ai-scans');
        Route::get('disease-scans/{diseaseScan}', [DiseaseScanController::class, 'show']);
        Route::get('community-reports', [CommunityReportController::class, 'index']);
        Route::post('community-reports', [CommunityReportController::class, 'store']);
        Route::get('alert-subscriptions', [AlertSubscriptionController::class, 'index']);
        Route::get('device-tokens', [DeviceTokenController::class, 'index']);
        Route::post('device-tokens', [DeviceTokenController::class, 'store']);
        Route::delete('device-tokens', [DeviceTokenController::class, 'destroy']);
        Route::get('events', [EventController::class, 'index']);
        Route::post('events', [EventController::class, 'store'])
            ->middleware('role:extension_officer|admin');
        Route::get('events/{event}', [EventController::class, 'show']);
        Route::post('events/{event}/register', [EventController::class, 'register']);

        Route::middleware('role:admin')->group(function (): void {
            Route::match(['get', 'post', 'patch', 'delete'], 'admin/{resource?}/{id?}', AdminOverviewController::class);
        });
    });
});
