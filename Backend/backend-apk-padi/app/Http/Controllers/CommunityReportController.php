<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\V1\CommunityReport\StoreCommunityReportRequest;
use App\Http\Resources\CommunityReportResource;
use App\Models\CommunityReport;
use App\Models\Farm;
use App\Services\Admin\AdminNotificationService;
use App\Services\Api\ApiResourceIndexService;
use App\Services\PadiCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommunityReportController extends Controller
{
    public function index(Request $request, ApiResourceIndexService $resources)
    {
        $user = $request->user();

        if ($user && ! $user->hasRole('admin') && ! $user->hasRole('extension_officer')) {
            return CommunityReportResource::collection(
                $this->nearbyReportsForFarmer((int) $user->id)
            );
        }

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
        PadiCacheService::invalidateRadarCache();

        $diseaseName = $report->scan?->predicted_class ?? 'Penyakit Padi';
        $farmerName = $report->farmer?->name ?? 'Petani Hamparan';

        $notificationService->notifyExtensionOfficers(
            "Laporan Masuk: {$diseaseName}",
            "{$farmerName} menyiarkan indikasi {$diseaseName} dalam radius {$report->radius_km} km. Diperlukan peninjauan.",
            'ppl_validation',
            ['report_id' => $report->id, 'radius_km' => (float) $report->radius_km]
        );

        foreach ($this->nearbyFarmerRecipients($report) as $recipient) {
            $distanceText = number_format((float) $recipient['distance_km'], 1, ',', '.');

            $notificationService->notifyUser(
                (int) $recipient['farmer_id'],
                "Radar Penyakit Terdekat: {$diseaseName}",
                "Ada laporan {$diseaseName} sekitar {$distanceText} km dari {$recipient['farm_name']}. Periksa daun saat patroli lahan.",
                'early_warning',
                [
                    'report_id' => $report->id,
                    'disease' => $diseaseName,
                    'distance_km' => round((float) $recipient['distance_km'], 2),
                    'radius_km' => (float) $report->radius_km,
                    'risk_level' => $this->riskLevel(
                        (float) $recipient['distance_km'],
                        (float) $report->radius_km
                    ),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Kondisi berhasil dilaporkan dan dikirim ke petani sekitar radius siaran.',
            'data' => new CommunityReportResource($report),
        ], 201);
    }

    private function nearbyReportsForFarmer(int $farmerId): Collection
    {
        $farms = Farm::query()
            ->where('farmer_user_id', $farmerId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'latitude', 'longitude']);

        if ($farms->isEmpty()) {
            return collect();
        }

        return CommunityReport::query()
            ->with(['farmer:id,name,phone', 'scan:id,predicted_class,confidence,image_url'])
            ->where('farmer_id', '!=', $farmerId)
            ->whereIn('status', ['pending', 'verified'])
            ->where('reported_at', '>=', now()->subDays(14))
            ->latest('reported_at')
            ->limit(100)
            ->get()
            ->filter(function (CommunityReport $report) use ($farms): bool {
                if ($report->latitude === null || $report->longitude === null) {
                    return false;
                }

                foreach ($farms as $farm) {
                    $distanceKm = $this->distanceKm(
                        (float) $farm->latitude,
                        (float) $farm->longitude,
                        (float) $report->latitude,
                        (float) $report->longitude
                    );

                    if ($distanceKm <= $this->effectiveRadiusKm($report)) {
                        return true;
                    }
                }

                return false;
            })
            ->values()
            ->take(50);
    }

    private function nearbyFarmerRecipients(CommunityReport $report): Collection
    {
        if ($report->latitude === null || $report->longitude === null) {
            return collect();
        }

        $radiusKm = $this->effectiveRadiusKm($report);

        return Farm::query()
            ->where('farmer_user_id', '!=', $report->farmer_id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'farmer_user_id', 'name', 'latitude', 'longitude'])
            ->map(function (Farm $farm) use ($report): array {
                return [
                    'farmer_id' => (int) $farm->farmer_user_id,
                    'farm_name' => $farm->name,
                    'distance_km' => $this->distanceKm(
                        (float) $farm->latitude,
                        (float) $farm->longitude,
                        (float) $report->latitude,
                        (float) $report->longitude
                    ),
                ];
            })
            ->filter(fn (array $item): bool => $item['distance_km'] <= $radiusKm)
            ->sortBy('distance_km')
            ->unique('farmer_id')
            ->values();
    }

    private function effectiveRadiusKm(CommunityReport $report): float
    {
        return max(1.0, min((float) $report->radius_km, 20.0));
    }

    private function riskLevel(float $distanceKm, float $radiusKm): string
    {
        if ($distanceKm <= 1.0 || $distanceKm <= ($radiusKm * 0.25)) {
            return 'siaga';
        }

        if ($distanceKm <= 5.0 || $distanceKm <= ($radiusKm * 0.6)) {
            return 'waspada';
        }

        return 'pantau';
    }

    private function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(max(0.0, 1 - $a)));
    }
}
