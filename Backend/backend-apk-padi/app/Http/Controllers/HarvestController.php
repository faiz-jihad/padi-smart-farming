<?php

namespace App\Http\Controllers;

use App\Http\Resources\HarvestResource;
use App\Services\Api\ApiResourceIndexService;

class HarvestController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return HarvestResource::collection($resources->harvests());
    }
}
