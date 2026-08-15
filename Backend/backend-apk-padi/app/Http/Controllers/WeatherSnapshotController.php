<?php

namespace App\Http\Controllers;

use App\Http\Resources\WeatherSnapshotResource;
use App\Models\WeatherSnapshot;

class WeatherSnapshotController extends Controller
{
    public function index()
    {
        $snapshots = WeatherSnapshot::with('farm')
            ->latest('observed_at')
            ->get();

        return WeatherSnapshotResource::collection($snapshots);
    }
}