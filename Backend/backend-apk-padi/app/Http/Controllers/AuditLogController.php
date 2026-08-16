<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuditLogResource;
use App\Services\AuditLogService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends Controller
{
    public function index(
        AuditLogService $service
    ): AnonymousResourceCollection {
        $logs = $service->getLogs();

        return AuditLogResource::collection($logs);
    }
}