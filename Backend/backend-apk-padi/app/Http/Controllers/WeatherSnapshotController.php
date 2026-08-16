<?php

namespace App\Http\Controllers;

use App\Http\Resources\WeatherSnapshotResource;
use App\Services\WeatherSnapshotService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WeatherSnapshotController extends Controller
{
    public function index(
        WeatherSnapshotService $service
    ): AnonymousResourceCollection {
        $snapshots = $service->getSnapshots();

        return WeatherSnapshotResource::collection($snapshots);
    }
}