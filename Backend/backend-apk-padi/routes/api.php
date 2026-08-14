<?php

use App\Http\Controllers\FarmerProfileController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\CropSeasonController;
use App\Http\Controllers\FarmActivityController;
use App\Http\Controllers\HarvestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MarketListingController;
use App\Http\Controllers\ListingImageController;
use App\Http\Controllers\MarketOfferController;
use Illuminate\Support\Facades\Route;


Route::get('/farmers', [FarmerProfileController::class, 'index']);
Route::get('/farms', [FarmController::class, 'index']);
Route::get('/crop-seasons', [CropSeasonController::class, 'index']);
Route::get('/farm-activities', [FarmActivityController::class, 'index']);
Route::get('/harvests', [HarvestController::class, 'index']);
Route::get('/notifications', [NotificationController::class, 'index']);
Route::get('/market-listings', [MarketListingController::class, 'index']);
Route::get('/listing-images', [ListingImageController::class, 'index']);
Route::get('/market-offers', [MarketOfferController::class, 'index']);