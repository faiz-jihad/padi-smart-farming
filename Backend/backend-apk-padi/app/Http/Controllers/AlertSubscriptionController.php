<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\AlertSubscriptionResource;
use App\Services\AlertSubscriptionService;
use Illuminate\Http\JsonResponse;

class AlertSubscriptionController extends Controller
{
    public function index(
        AlertSubscriptionService $service
    ): JsonResponse {
        $subscriptions = $service->getSubscriptions();

        return ApiResponse::success(
            'Data langganan peringatan berhasil diambil.',
            [
                'alert_subscriptions' => AlertSubscriptionResource::collection($subscriptions),
            ],
        );
    }
}