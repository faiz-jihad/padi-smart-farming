<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContractPaymentResource;
use App\Services\Api\ApiResourceIndexService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContractPaymentController extends Controller
{
    public function index(Request $request, ApiResourceIndexService $resources): AnonymousResourceCollection
    {
        return ContractPaymentResource::collection($resources->contractPayments($request->user()));
    }
}
