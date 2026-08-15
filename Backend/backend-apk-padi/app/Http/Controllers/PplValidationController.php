<?php

namespace App\Http\Controllers;

use App\Models\PplValidation;

class PplValidationController extends Controller
{
    public function index()
    {
        $validations = PplValidation::with('ppl')->get();

        return response()->json([
            'success' => true,
            'data' => $validations,
        ]);
    }
}