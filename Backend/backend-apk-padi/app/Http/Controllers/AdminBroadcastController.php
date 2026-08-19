<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminBroadcastResource;
use App\Services\Api\ApiResourceIndexService;

class AdminBroadcastController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return AdminBroadcastResource::collection($resources->adminBroadcasts());
    }
}
