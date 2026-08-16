<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function index(
        NotificationService $service
    ): AnonymousResourceCollection {
        $notifications = $service->getNotifications();

        return NotificationResource::collection($notifications);
    }
}