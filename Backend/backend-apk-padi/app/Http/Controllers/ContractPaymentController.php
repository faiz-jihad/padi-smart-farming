<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContractPaymentResource;
use App\Models\ContractPayment;

class ContractPaymentController extends Controller
{
    public function index()
    {
        $payments = ContractPayment::with('contract')->get();

        return ContractPaymentResource::collection($payments);
    }
}