<?php

namespace App\Http\Controllers;

use App\Http\Resources\DeviceTokenResource;
use App\Models\DeviceToken;

class DeviceTokenController extends Controller
{
    public function index()
    {
        $tokens = DeviceToken::with('user')->get();

        return DeviceTokenResource::collection($tokens);
    }
}