<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    public function getNotifications(): Collection
    {
        return Notification::with('user')->get();
    }
}