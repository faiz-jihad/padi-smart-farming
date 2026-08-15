<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommunityReportResource;
use App\Models\CommunityReport;

class CommunityReportController extends Controller
{
    public function index()
    {
        $reports = CommunityReport::with('farmer')->get();

        return CommunityReportResource::collection($reports);
    }
}