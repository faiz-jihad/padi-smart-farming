<?php

use App\Http\Controllers\FarmerProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/farmers', [FarmerProfileController::class, 'index']);