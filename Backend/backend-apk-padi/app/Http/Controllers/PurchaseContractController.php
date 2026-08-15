<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchaseContractResource;
use App\Models\PurchaseContract;

class PurchaseContractController extends Controller
{
    public function index()
    {
        $contracts = PurchaseContract::with([
            'listing',
            'farmer',
            'partner',
            'offer',
        ])->get();

        return PurchaseContractResource::collection($contracts);
    }
}