<?php

namespace App\Services;

use App\Models\PurchaseContract;
use Illuminate\Database\Eloquent\Collection;

class PurchaseContractService
{
    public function getContracts(): Collection
    {
        return PurchaseContract::with([
            'listing',
            'farmer',
            'partner',
            'offer',
        ])->get();
    }
}