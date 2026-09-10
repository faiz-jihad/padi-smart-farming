<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Services\Government\GovernmentPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransNotificationController extends Controller
{
    public function __construct(
        protected GovernmentPaymentService $paymentService
    ) {}

    /**
     * Handle incoming payment notification from Midtrans
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        
        $result = $this->paymentService->handleNotification($payload);

        if (! $result['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message'],
            ], 404);
        }

        return response()->json([
            'status'  => 'ok',
            'message' => $result['message'],
        ]);
    }
}
