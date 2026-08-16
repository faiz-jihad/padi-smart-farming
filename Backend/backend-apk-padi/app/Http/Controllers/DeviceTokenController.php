<?php

namespace App\Http\Controllers;

use App\Http\Resources\DeviceTokenResource;
use App\Services\Api\ApiResourceIndexService;

class DeviceTokenController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return DeviceTokenResource::collection($resources->deviceTokens());
    }
}
