<?php

namespace App\Http\Controllers;

use App\Models\Harvest;

class HarvestController extends Controller
{
    public function index()
    {
        $harvests = Harvest::with('cropSeason')->get();

        return response()->json([
            'success' => true,
            'data' => $harvests,
        ]);
    }
}