<?php

namespace App\Http\Controllers;

use App\Models\FarmActivity;

class FarmActivityController extends Controller
{
    public function index()
    {
        $activities = FarmActivity::with('cropSeason')->get();

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }
}