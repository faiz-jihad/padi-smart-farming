<?php

namespace App\Http\Controllers;

use App\Http\Resources\PplValidationResource;
use App\Services\Api\ApiResourceIndexService;

class PplValidationController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return PplValidationResource::collection($resources->pplValidations());
    }
}
