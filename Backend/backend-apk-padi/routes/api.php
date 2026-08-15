<?php

use App\Http\Controllers\AlertSubscriptionController;
use App\Http\Controllers\Api\V1\Admin\AdminOverviewController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\CommunityReportController;
use App\Http\Controllers\ContractPaymentController;
use App\Http\Controllers\CropSeasonController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\FarmActivityController;
use App\Http\Controllers\FarmController;
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
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
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

    Route::middleware(['auth:sanctum', 'account.active'])->group(function (): void {
        Route::get('profile', [ProfileController::class, 'show']);
        Route::patch('profile', [ProfileController::class, 'update']);
        Route::put('profile/password', [ProfileController::class, 'changePassword']);

        Route::get('farms', [FarmController::class, 'index']);
        Route::post('farms', [FarmController::class, 'store']);

        Route::get('farmers', [FarmerProfileController::class, 'index']);

        Route::get('farm-activities', [FarmActivityController::class, 'index']);
        Route::get('harvests', [HarvestController::class, 'index']);
        Route::get('rice-varieties', [RiceVarietyController::class, 'index']);
        Route::get('weather-snapshots', [WeatherSnapshotController::class, 'index']);
        Route::get('fertilizer-rules', [FertilizerRuleController::class, 'index']);

        Route::get('crop-seasons', [CropSeasonController::class, 'index']);
        Route::post('crop-seasons', [CropSeasonController::class, 'store']);

        Route::get('market-listings', [MarketListingController::class, 'index']);
        Route::get('listing-images', [ListingImageController::class, 'index']);
        Route::get('market-offers', [MarketOfferController::class, 'index']);
        Route::get('purchase-contracts', [PurchaseContractController::class, 'index']);
        Route::get('contract-payments', [ContractPaymentController::class, 'index']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('ppl-validations', [PplValidationController::class, 'index']);
        Route::get('community-reports', [CommunityReportController::class, 'index']);
        Route::get('alert-subscriptions', [AlertSubscriptionController::class, 'index']);
        Route::get('device-tokens', [DeviceTokenController::class, 'index']);
        Route::get('partner-favorites', [PartnerFavoriteController::class, 'index']);

        Route::middleware('role:admin')->group(function (): void {
            Route::match(['get', 'post', 'patch', 'delete'], 'admin/{resource?}/{id?}', AdminOverviewController::class);
        });
    });
});
