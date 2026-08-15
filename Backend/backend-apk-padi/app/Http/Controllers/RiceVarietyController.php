<?php

namespace App\Http\Controllers;

use App\Http\Resources\RiceVarietyResource;
use App\Models\RiceVariety;

class RiceVarietyController extends Controller
{
    public function index()
    {
        $varieties = RiceVariety::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return RiceVarietyResource::collection($varieties);
    }
}