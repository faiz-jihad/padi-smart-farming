<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchaseContractResource;
use App\Services\PurchaseContractService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PurchaseContractController extends Controller
{
    public function index(
        PurchaseContractService $service
    ): AnonymousResourceCollection {
        $contracts = $service->getContracts();

        return PurchaseContractResource::collection($contracts);
    }
}