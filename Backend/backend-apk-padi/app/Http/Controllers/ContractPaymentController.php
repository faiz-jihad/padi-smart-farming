<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContractPaymentResource;
use App\Services\Api\ApiResourceIndexService;

class ContractPaymentController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return ContractPaymentResource::collection($resources->contractPayments());
    }
}
