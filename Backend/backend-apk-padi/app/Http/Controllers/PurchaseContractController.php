<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchaseContractResource;
use App\Services\Api\ApiResourceIndexService;

class PurchaseContractController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return PurchaseContractResource::collection($resources->purchaseContracts());
    }
}
