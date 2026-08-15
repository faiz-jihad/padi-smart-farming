<?php

namespace App\Http\Controllers;

use App\Http\Resources\WeatherSnapshotResource;
use App\Services\Api\ApiResourceIndexService;

class WeatherSnapshotController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return WeatherSnapshotResource::collection($resources->weatherSnapshots());
    }
}
