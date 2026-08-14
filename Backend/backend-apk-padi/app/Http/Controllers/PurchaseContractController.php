<?php

namespace App\Http\Controllers;

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

        return response()->json([
            'success' => true,
            'data' => $contracts,
        ]);
    }
}