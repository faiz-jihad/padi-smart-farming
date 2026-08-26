<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\V1\CommunityReport\StoreCommunityReportRequest;
use App\Http\Resources\CommunityReportResource;
use App\Models\CommunityReport;
use App\Services\Admin\AdminNotificationService;
use App\Services\Api\ApiResourceIndexService;
use App\Services\PadiCacheService;

class CommunityReportController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return CommunityReportResource::collection(
            $resources->communityReports()
        );
    }

    public function store(StoreCommunityReportRequest $request, AdminNotificationService $notificationService)
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

        $report->load(['farmer', 'scan']);

        // 1. Invalidate Redis & Radar cache
        PadiCacheService::invalidateRadarCache();

        // 2. Real-time Role Notification: Notify PPL for field verification
        $diseaseName = $report->scan?->predicted_class ?? 'Penyakit Padi';
        $farmerName = $report->farmer?->name ?? 'Petani Hamparan';

        $notificationService->notifyExtensionOfficers(
            "⚠️ Laporan Masuk: {$diseaseName}",
            "{$farmerName} menyiarkan indikasi {$diseaseName} dalam radius {$report->radius_km} km. Diperlukan peninjauan.",
            'ppl_validation',
            ['report_id' => $report->id, 'radius_km' => $report->radius_km]
        );

        // 3. Real-time Notification to neighboring Farmers
        $notificationService->notifyFarmers(
            "📡 Radar Peringatan: {$diseaseName}",
            "Peringatan dini serangan {$diseaseName} terdeteksi di sekitar hamparan Anda (Radius {$report->radius_km} km).",
            'early_warning',
            ['report_id' => $report->id, 'disease' => $diseaseName]
        );

        return response()->json([
            'success' => true,
            'message' => 'Kondisi berhasil dilaporkan dan disiarkan secara real-time.',
            'data' => new CommunityReportResource($report),
        ], 201);
    }
}
