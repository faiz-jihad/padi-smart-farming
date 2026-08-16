<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuditLogResource;
use App\Services\Api\ApiResourceIndexService;

class AuditLogController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return AuditLogResource::collection($resources->auditLogs());
    }
}
