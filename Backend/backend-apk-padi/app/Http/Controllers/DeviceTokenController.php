<?php

namespace App\Http\Controllers;

use App\Http\Resources\DeviceTokenResource;
use App\Services\DeviceTokenService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DeviceTokenController extends Controller
{
    public function index(
        DeviceTokenService $service
    ): AnonymousResourceCollection {
        $tokens = $service->getTokens();

        return DeviceTokenResource::collection($tokens);
    }
}