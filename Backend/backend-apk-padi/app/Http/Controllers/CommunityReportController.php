<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommunityReportResource;
use App\Services\CommunityReportService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommunityReportController extends Controller
{
    public function index(
        CommunityReportService $service
    ): AnonymousResourceCollection {
        $reports = $service->getReports();

        return CommunityReportResource::collection($reports);
    }
}