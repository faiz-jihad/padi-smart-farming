<?php

namespace App\Http\Controllers;

use App\Http\Resources\AlertSubscriptionResource;
use App\Services\Api\ApiResourceIndexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertSubscriptionController extends Controller
{
    public function index(Request $request, ApiResourceIndexService $resources): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data langganan peringatan berhasil diambil.',
            'data' => [
                'alert_subscriptions' => AlertSubscriptionResource::collection(
                    $resources->alertSubscriptions($request->user())
                ),
            ],
        ]);
    }
}
