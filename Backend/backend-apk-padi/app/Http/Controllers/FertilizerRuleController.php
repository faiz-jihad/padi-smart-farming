<?php

namespace App\Http\Controllers;

use App\Http\Resources\FertilizerRuleResource;
use App\Services\Api\ApiResourceIndexService;

class FertilizerRuleController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return FertilizerRuleResource::collection($resources->fertilizerRules());
    }
}
