<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContractPaymentResource;
use App\Services\ContractPaymentService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContractPaymentController extends Controller
{
    public function index(
        ContractPaymentService $service
    ): AnonymousResourceCollection {
        $payments = $service->getPayments();

        return ContractPaymentResource::collection($payments);
    }
}