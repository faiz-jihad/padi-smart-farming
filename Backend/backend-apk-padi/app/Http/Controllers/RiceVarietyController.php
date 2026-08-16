<?php

namespace App\Http\Controllers;

use App\Http\Resources\RiceVarietyResource;
use App\Services\Api\ApiResourceIndexService;

class RiceVarietyController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return RiceVarietyResource::collection($resources->riceVarieties());
    }
}
