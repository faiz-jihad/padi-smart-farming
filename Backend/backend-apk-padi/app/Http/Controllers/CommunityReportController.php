<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommunityReportResource;
use App\Services\Api\ApiResourceIndexService;

class CommunityReportController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return CommunityReportResource::collection($resources->communityReports());
    }
}
