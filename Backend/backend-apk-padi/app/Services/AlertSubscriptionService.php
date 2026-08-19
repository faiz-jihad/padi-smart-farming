<?php

namespace App\Services;

use App\Models\AlertSubscription;
use Illuminate\Database\Eloquent\Collection;

class AlertSubscriptionService
{
    public function getSubscriptions(): Collection
    {
        return AlertSubscription::query()
            ->latest('id')
            ->get();
    }
}