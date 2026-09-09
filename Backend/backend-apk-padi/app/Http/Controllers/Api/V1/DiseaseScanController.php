<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DiseaseScan\StoreDiseaseScanRequest;
use App\Http\Resources\DiseaseScanResource;
use App\Models\CommunityReport;
use App\Models\DiseaseScan;
use App\Services\Admin\AdminNotificationService;
use App\Services\DiseaseDetectionService;
use App\Services\PadiCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DiseaseScanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $scans = DiseaseScan::query()
            ->when(! $user->hasRole('admin'), fn ($query) => $query->where('farmer_id', $user->id))
            ->with(['farm:id,name,area_ha', 'recommendation', 'pplValidation.ppl:id,name'])
            ->latest('scanned_at')
            ->paginate((int) $request->integer('per_page', 15));

        return ApiResponse::success('Riwayat scan penyakit berhasil diambil.', [
            'scans' => DiseaseScanResource::collection($scans->items()),
            'meta' => [
                'current_page' => $scans->currentPage(),
                'last_page' => $scans->lastPage(),
                'per_page' => $scans->perPage(),
                'total' => $scans->total(),
            ],
        ]);
    }

    public function store(
        StoreDiseaseScanRequest $request,
        DiseaseDetectionService $service,
        AdminNotificationService $notificationService
    ): JsonResponse
    {
        try {
            $scan = $service->scan(
                $request->user()->id,
                $request->file('image'),
                $request->validated()
            );
        } catch (\InvalidArgumentException $error) {
            return ApiResponse::error($error->getMessage(), 422);
        } catch (RuntimeException $error) {
            return ApiResponse::error($error->getMessage(), 503);
        }

        $this->createEarlyWarningCandidate($scan, $notificationService);

        return ApiResponse::success('Foto tanaman berhasil diperiksa.', [
            'scan' => DiseaseScanResource::make($scan),
        ], 201);
    }

    public function show(Request $request, DiseaseScan $diseaseScan): JsonResponse
    {
        $user = $request->user();

        if ($diseaseScan->farmer_id !== $user->id && ! $user->hasRole('admin')) {
            abort(403, 'Anda tidak memiliki akses ke data scan ini.');
        }

        return ApiResponse::success('Detail scan penyakit berhasil diambil.', [
            'scan' => DiseaseScanResource::make($diseaseScan->load(['farm:id,name,area_ha', 'recommendation', 'pplValidation.ppl:id,name'])),
        ]);
    }

    public function feedback(Request $request, DiseaseScan $diseaseScan, DiseaseDetectionService $service): JsonResponse
    {
        $user = $request->user();

        if ($diseaseScan->farmer_id !== $user->id && ! $user->hasRole('admin')) {
            abort(403, 'Anda tidak memiliki akses ke data scan ini.');
        }

        $validated = $request->validate([
            'status' => 'required|in:confirmed,corrected',
            'corrected_class' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $updatedScan = $service->submitFeedback(
            $diseaseScan,
            $validated['status'],
            $validated['corrected_class'] ?? null,
            $validated['notes'] ?? null
        );

        return ApiResponse::success('Terima kasih! Umpan balik Anda telah dipelajari oleh sistem untuk meningkatkan akurasi diagnosa berikutnya.', [
            'scan' => DiseaseScanResource::make($updatedScan->load('farm')),
            'is_learned' => true,
        ]);
    }

    private function createEarlyWarningCandidate(DiseaseScan $scan, AdminNotificationService $notificationService): void
    {
        $scan->loadMissing(['farm:id,name,farmer_user_id,latitude,longitude', 'farmer:id,name']);

        if (! $this->isReportableDiseaseScan($scan)) {
            return;
        }

        $farm = $scan->farm;
        if (! $farm || $farm->latitude === null || $farm->longitude === null) {
            return;
        }

        $report = CommunityReport::firstOrCreate(
            ['scan_id' => $scan->id],
            [
                'farmer_id' => $scan->farmer_id,
                'latitude' => $farm->latitude,
                'longitude' => $farm->longitude,
                'radius_km' => $this->earlyWarningRadiusKm($scan),
                'consent_given' => true,
                'status' => 'pending',
                'reported_at' => now(),
            ]
        );

        if (! $report->wasRecentlyCreated) {
            return;
        }

        PadiCacheService::invalidateRadarCache();

        $diseaseName = $scan->predicted_class ?? 'Penyakit Padi';
        $farmName = $farm->name ?? 'Lahan Sawah';
        $confidence = round(((float) $scan->confidence) * 100, 1);

        $notificationService->notifyExtensionOfficers(
            "Kandidat Early Warning: {$diseaseName}",
            "Scan {$farmName} mendeteksi {$diseaseName} ({$confidence}%). Perlu validasi PPL sebelum status menjadi terverifikasi.",
            'early_warning_candidate',
            [
                'report_id' => $report->id,
                'scan_id' => $scan->id,
                'farm_id' => $farm->id,
                'disease' => $diseaseName,
                'confidence' => (float) $scan->confidence,
                'status' => 'pending',
            ]
        );
    }

    private function isReportableDiseaseScan(DiseaseScan $scan): bool
    {
        $predictedClass = strtolower(trim((string) $scan->predicted_class));
        $qualityStatus = strtolower(trim((string) $scan->quality_status));
        $confidence = (float) $scan->confidence;

        if ($confidence < 0.70) {
            return false;
        }

        foreach (['healthy', 'normal', 'sehat', 'invalid', 'unknown'] as $blocked) {
            if ($predictedClass === $blocked || str_contains($qualityStatus, $blocked)) {
                return false;
            }
        }

        return $predictedClass !== '';
    }

    private function earlyWarningRadiusKm(DiseaseScan $scan): float
    {
        $confidence = (float) $scan->confidence;

        if ($confidence >= 0.90) {
            return 5.0;
        }

        if ($confidence >= 0.80) {
            return 3.0;
        }

        return 1.5;
    }
}
