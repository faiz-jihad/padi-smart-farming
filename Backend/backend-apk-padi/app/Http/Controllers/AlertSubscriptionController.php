<?php

namespace App\Http\Controllers;

use App\Http\Resources\AlertSubscriptionResource;
use App\Models\AlertSubscription;
use Illuminate\Http\JsonResponse;

class AlertSubscriptionController extends Controller
{
    public function index(): JsonResponse
    {
        $subscriptions = AlertSubscription::query()
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data langganan peringatan berhasil diambil.',
            'data' => [
                'alert_subscriptions' => AlertSubscriptionResource::collection($subscriptions),
            ],
        ]);
    }
}