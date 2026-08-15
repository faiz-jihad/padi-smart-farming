<?php

namespace App\Http\Controllers;

use App\Models\AlertSubscription;

class AlertSubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = AlertSubscription::with([
            'farmer',
            'farm',
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $subscriptions,
        ]);
    }
}