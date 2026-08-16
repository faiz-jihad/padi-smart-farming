<?php

namespace App\Http\Controllers;

use App\Http\Resources\PplValidationResource;
use App\Services\PplValidationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PplValidationController extends Controller
{
    public function index(
        PplValidationService $service
    ): AnonymousResourceCollection {
        $validations = $service->getValidations();

        return PplValidationResource::collection($validations);
    }
}