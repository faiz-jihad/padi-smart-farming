<?php

namespace App\Http\Controllers;

use App\Models\ContractPayment;

class ContractPaymentController extends Controller
{
    public function index()
    {
        $payments = ContractPayment::with('contract')->get();

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }
}