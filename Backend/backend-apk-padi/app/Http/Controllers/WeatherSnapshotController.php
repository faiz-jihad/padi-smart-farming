<?php

namespace App\Http\Controllers;

use App\Models\WeatherSnapshot;

class WeatherSnapshotController extends Controller
{
    public function index()
    {
        $snapshots = WeatherSnapshot::with('farm')
            ->latest('observed_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $snapshots,
        ]);
    }
}