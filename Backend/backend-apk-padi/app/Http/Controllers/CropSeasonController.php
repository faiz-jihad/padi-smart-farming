<?php

namespace App\Http\Controllers;

use App\Models\CropSeason;

class CropSeasonController extends Controller
{
    public function index()
    {
        $cropSeasons = CropSeason::with('farm')->get();

        return response()->json([
            'success' => true,
            'data' => $cropSeasons,
        ]);
    }
}