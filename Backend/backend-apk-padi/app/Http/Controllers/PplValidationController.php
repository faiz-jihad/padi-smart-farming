<?php

namespace App\Http\Controllers;

use App\Http\Resources\PplValidationResource;
use App\Models\PplValidation;

class PplValidationController extends Controller
{
    public function index()
    {
        $validations = PplValidation::with('ppl')->get();

        return PplValidationResource::collection($validations);
    }
}