<?php

namespace App\Http\Controllers;

use App\Http\Resources\FertilizerRuleResource;
use App\Services\FertilizerRuleService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FertilizerRuleController extends Controller
{
    public function index(
        FertilizerRuleService $service
    ): AnonymousResourceCollection {
        $rules = $service->getRules();

        return FertilizerRuleResource::collection($rules);
    }
}