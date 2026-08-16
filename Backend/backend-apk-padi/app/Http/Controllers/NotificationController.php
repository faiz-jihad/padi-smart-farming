<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Services\Api\ApiResourceIndexService;

class NotificationController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return NotificationResource::collection($resources->notifications());
    }
}
