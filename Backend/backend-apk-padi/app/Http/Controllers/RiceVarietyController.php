<?php

namespace App\Http\Controllers;

use App\Http\Resources\RiceVarietyResource;
use App\Services\RiceVarietyService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RiceVarietyController extends Controller
{
    public function index(
        RiceVarietyService $service
    ): AnonymousResourceCollection {
        $varieties = $service->getActiveVarieties();

        return RiceVarietyResource::collection($varieties);
    }
}