<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminBroadcastResource;
use App\Services\AdminBroadcastService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminBroadcastController extends Controller
{
    public function index(
        AdminBroadcastService $service
    ): AnonymousResourceCollection {
        $broadcasts = $service->getBroadcasts();

        return AdminBroadcastResource::collection($broadcasts);
    }
}