<?php

namespace App\Rules;

use App\Models\Farm;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FarmBelongsToFarmer implements ValidationRule
{
    public function __construct(
        private readonly int $farmerId
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = Farm::where('id', $value)
            ->where('farmer_user_id', $this->farmerId)
            ->exists();

        if (!$exists) {
            $fail('Lahan yang dipilih bukan milik petani tersebut.');
        }
    }
}