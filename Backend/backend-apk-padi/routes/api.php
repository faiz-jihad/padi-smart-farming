<?php

use App\Http\Controllers\FarmerProfileController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\CropSeasonController;
use Illuminate\Support\Facades\Route;


Route::get('/farmers', [FarmerProfileController::class, 'index']);
Route::get('/farms', [FarmController::class, 'index']);
Route::get('/crop-seasons', [CropSeasonController::class, 'index']);
