<?php

namespace App\Http\Controllers;

use App\Http\Resources\HarvestResource;
use App\Models\Harvest;

class HarvestController extends Controller
{
    public function index()
    {
        $harvests = Harvest::with('cropSeason')->get();

        return HarvestResource::collection($harvests);
    }
}