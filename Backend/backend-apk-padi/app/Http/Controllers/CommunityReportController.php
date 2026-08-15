<?php

namespace App\Http\Controllers;

use App\Models\CommunityReport;

class CommunityReportController extends Controller
{
    public function index()
    {
        $reports = CommunityReport::with('farmer')->get();

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }
}