<?php

use App\Http\Controllers\FarmerProfileController;
use App\Http\Controllers\FarmController;
use Illuminate\Support\Facades\Route;

Route::get('/farmers', [FarmerProfileController::class, 'index']);
Route::get('/farms', [FarmController::class, 'index']);