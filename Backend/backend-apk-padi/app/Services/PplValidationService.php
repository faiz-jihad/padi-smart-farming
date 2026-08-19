<?php

namespace App\Services;

use App\Models\PplValidation;
use Illuminate\Database\Eloquent\Collection;

class PplValidationService
{
    public function getValidations(): Collection
    {
        return PplValidation::with('ppl')->get();
    }
}