<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\V1\CommunityReport\StoreCommunityReportRequest;
use App\Http\Resources\CommunityReportResource;
use App\Models\CommunityReport;
use App\Services\Api\ApiResourceIndexService;

class CommunityReportController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return CommunityReportResource::collection(
            $resources->communityReports()
        );
    }

    public function store(StoreCommunityReportRequest $request)
    {
        $report = CommunityReport::create([
            'scan_id' => $request->integer('scan_id'),
            'farmer_id' => $request->user()->id,
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'radius_km' => $request->input('radius_km'),
            'consent_given' => true,
            'status' => 'pending',
            'reported_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kondisi berhasil dilaporkan.',
            'data' => new CommunityReportResource(
                $report->load(['farmer', 'scan'])
            ),
        ], 201);
    }
}
