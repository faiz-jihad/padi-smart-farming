<?php

namespace App\Http\Controllers;

use App\Http\Resources\HarvestResource;
use App\Services\HarvestService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HarvestController extends Controller
{
    public function index(
        HarvestService $service
    ): AnonymousResourceCollection {
        $harvests = $service->getHarvests();

        return HarvestResource::collection($harvests);
    }
}