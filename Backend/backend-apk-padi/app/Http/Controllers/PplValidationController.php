<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\PplValidationResource;
use App\Models\CommunityReport;
use App\Models\DiseaseScan;
use App\Models\Farm;
use App\Models\PplValidation;
use App\Models\User;
use App\Services\Admin\AdminNotificationService;
use App\Services\Api\ApiResourceIndexService;
use App\Services\PadiCacheService;
use App\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PplValidationController extends Controller
{
    public function __construct(
        private AdminNotificationService $notif
    ) {}

    /**
     * List PPL validations.
     * - Farmer: hanya validation dari scan mereka sendiri
     * - PPL: semua validation yang ditugaskan ke mereka
     * - Admin: semua
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $validations = PplValidation::query()
            ->with(['scan.farm:id,name', 'scan.farmer:id,name', 'scan.recommendation', 'ppl:id,name'])
            ->when($user->hasRole(UserRole::Farmer->value), function ($q) use ($user) {
                $q->whereHas('scan', fn ($s) => $s->where('farmer_id', $user->id));
            })
            ->when($user->hasRole(UserRole::ExtensionOfficer->value), function ($q) use ($user) {
                $q->where(function ($q2) use ($user) {
                    $q2->where('ppl_id', $user->id)
                       ->orWhereNull('ppl_id');
                });
            })
            ->latest()
            ->get();

        return ApiResponse::success('Daftar validasi PPL berhasil diambil.', [
            'validations' => PplValidationResource::collection($validations),
        ]);
    }

    /**
     * Petani mengirim scan ke PPL untuk divalidasi.
     * POST /api/v1/ppl-validations
     * Body: { scan_id }
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'scan_id' => 'required|integer|exists:disease_scans,id',
            'notes'   => 'nullable|string|max:1000',
            'ppl_id'  => 'nullable|integer|exists:users,id',
        ]);

        $scan = DiseaseScan::with(['farm', 'farmer'])->findOrFail($validated['scan_id']);

        // Pastikan scan milik petani yang sedang login atau admin/penyuluh
        if ($scan->farmer_id !== $user->id && ! $user->hasRole('admin') && ! $user->hasRole('extension_officer')) {
            return ApiResponse::error('Anda tidak memiliki akses ke scan ini.', 403);
        }

        // 1. ATURAN INTEGRITAS: Cegah pelaporan tanaman sehat / normal (tidak ada patogen yang perlu diverifikasi)
        $predictedSlug = strtolower(trim((string) $scan->predicted_class));
        $isHealthy = str_contains($predictedSlug, 'normal')
            || str_contains($predictedSlug, 'sehat')
            || str_contains($predictedSlug, 'healthy');
        if ($isHealthy) {
            return ApiResponse::error(
                'Hasil diagnosa menunjukkan tanaman padi dalam kondisi sehat dan normal. Laporan ke penyuluh dikhususkan untuk tanaman terindikasi gejala penyakit atau anomali.',
                422,
                ['code' => 'PLANT_IS_HEALTHY']
            );
        }

        // 2. ATURAN ANTI-REDUDANSI 1: Cegah pelaporan ulang untuk hasil scan yang sama persis
        $existingExact = PplValidation::where('scan_id', $scan->id)->first();
        if ($existingExact) {
            return ApiResponse::error('Laporan untuk hasil tes diagnosa ini sudah pernah dikirimkan ke penyuluh.', 422, [
                'code' => 'DUPLICATE_SCAN_REPORT',
                'validation' => PplValidationResource::make($existingExact->load(['scan.farm', 'scan.farmer', 'ppl'])),
            ]);
        }

        // 3. ATURAN ANTI-REDUDANSI 2: Cegah redudansi masalah deteksi penyakit yang sama di lahan yang sama
        // Jika petani sudah memiliki laporan aktif ('pending' atau 'needs_revisit') untuk penyakit yang sama pada lahan yang sama,
        // atau jika kasus tersebut baru saja diverifikasi ('validated') dalam 48 jam terakhir,
        // tolak pembuatan tiket duplikat untuk mencegah spam antrean verifikasi penyuluh lapangan.
        $activeDuplicateQuery = PplValidation::query()
            ->whereHas('scan', function ($q) use ($scan) {
                $q->where('farmer_id', $scan->farmer_id)
                  ->where('predicted_class', $scan->predicted_class);

                if (! empty($scan->farm_id)) {
                    $q->where('farm_id', $scan->farm_id);
                }
            })
            ->where(function ($q) {
                $q->whereIn('status', ['pending', 'needs_revisit'])
                  ->orWhere(function ($q2) {
                      $q2->where('status', 'validated')
                         ->where('validated_at', '>=', now()->subHours(48));
                  });
            })
            ->latest();

        $activeDuplicate = $activeDuplicateQuery->first();

        if ($activeDuplicate) {
            $farmName = $scan->farm?->name ?? 'lahan ini';
            $diseaseName = $scan->predicted_class ?? 'penyakit ini';
            $statusText = match ($activeDuplicate->status) {
                'pending' => 'sedang dalam antrean verifikasi lapangan',
                'needs_revisit' => 'dijadwalkan untuk kunjungan ulang lapangan',
                'validated' => 'telah divalidasi oleh penyuluh dalam 48 jam terakhir',
                default => 'sedang dalam penanganan aktif penyuluh',
            };

            return ApiResponse::error(
                "Masalah deteksi {$diseaseName} pada {$farmName} sudah pernah dilaporkan dan saat ini {$statusText}. Untuk menghindari redudansi laporan pada masalah yang sama, mohon menunggu proses tindak lanjut penyuluh.",
                422,
                [
                    'code' => 'DUPLICATE_ACTIVE_PROBLEM',
                    'validation' => PplValidationResource::make($activeDuplicate->load(['scan.farm', 'scan.farmer', 'ppl'])),
                ]
            );
        }

        $validation = PplValidation::create([
            'scan_id' => $scan->id,
            'ppl_id'  => $validated['ppl_id'] ?? null, // Bisa ditugaskan ke PPL tertentu atau antrean umum
            'status'  => 'pending',
            'notes'   => $validated['notes'] ?? null,
        ]);

        // Kirim notifikasi ke semua PPL aktif
        $disease = $scan->predicted_class ?? 'Tidak diketahui';
        $confidence = $scan->confidence
            ? round((float) $scan->confidence * 100, 1) . '%'
            : '-';
        $notesSnippet = ! empty($validated['notes'])
            ? "\nCatatan petani: \"{$validated['notes']}\""
            : '';

        $this->notif->notifyExtensionOfficers(
            title: 'Kasus Baru Menunggu Validasi',
            body: "Petani {$user->name} melaporkan dugaan {$disease} (keyakinan AI: {$confidence}).{$notesSnippet} Mohon validasi lapangan.",
            type: 'ppl_case',
            data: [
                'validation_id' => $validation->id,
                'scan_id'       => $scan->id,
                'farmer_name'   => $user->name,
                'farmer_notes'  => $validated['notes'] ?? null,
                'disease'       => $disease,
                'confidence'    => $confidence,
                'action_url'    => "/ppl-cases/{$validation->id}",
            ]
        );

        // Notifikasi konfirmasi ke Petani
        $farmName = $scan->farm?->name ?? 'Lahan Sawah';
        $this->notif->notifyUser(
            user: $user,
            title: 'Laporan Dikirim ke Penyuluh',
            body: "Laporan dugaan {$disease} untuk {$farmName} telah berhasil dikirim ke petugas penyuluh pertanian lapangan (PPL).",
            type: 'ppl_case',
            data: [
                'validation_id' => $validation->id,
                'scan_id'       => $scan->id,
                'status'        => 'pending',
                'action_url'    => "/ppl-cases",
            ]
        );

        // Notifikasi ke Admin agar dashboard web admin langsung sinkron mencatat laporan masuk
        $this->notif->notifyAdmins(
            title: 'Laporan Kasus PPL Baru',
            body: "Petani {$user->name} melaporkan dugaan {$disease} untuk {$farmName} (Akurasi AI: {$confidence}). Mohon pantau atau tugaskan penyuluh.",
            type: 'ppl_case_new',
            data: [
                'validation_id' => $validation->id,
                'scan_id'       => $scan->id,
                'farmer_name'   => $user->name,
                'disease'       => $disease,
                'confidence'    => $confidence,
                'action_url'    => '/admin/disease',
            ]
        );

        return ApiResponse::success('Kasus berhasil dikirim ke penyuluh. Anda akan mendapat notifikasi setelah divalidasi.', [
            'validation' => PplValidationResource::make($validation->load(['scan.farm', 'scan.farmer', 'ppl'])),
        ], 201);
    }

    /**
     * Detail satu validasi.
     */
    public function show(Request $request, PplValidation $pplValidation): JsonResponse
    {
        $user = $request->user();

        // Access control
        $scanFarmerId = $pplValidation->scan?->farmer_id;
        $isPplAssigned = $pplValidation->ppl_id === $user->id;
        $isFarmer = $scanFarmerId === $user->id;

        if (!$isPplAssigned && !$isFarmer && ! $user->hasRole('admin') && ! $user->hasRole('extension_officer')) {
            return ApiResponse::error('Akses ditolak.', 403);
        }

        return ApiResponse::success('Detail validasi PPL berhasil diambil.', [
            'validation' => PplValidationResource::make(
                $pplValidation->load(['scan.farm:id,name', 'scan.farmer:id,name', 'scan.recommendation', 'ppl:id,name'])
            ),
        ]);
    }

    /**
     * PPL memperbarui status validasi.
     * PATCH /api/v1/ppl-validations/{id}
     * Body: { status, notes }
     */
    public function update(Request $request, PplValidation $pplValidation): JsonResponse
    {
        $user = $request->user();

        // Hanya PPL atau admin yang boleh update
        if (! $user->hasRole('extension_officer') && ! $user->hasRole('admin')) {
            return ApiResponse::error('Hanya penyuluh yang dapat memvalidasi kasus.', 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,validated,rejected,needs_revisit',
            'notes'  => 'nullable|string|max:1000',
        ]);

        // Assign PPL jika belum ada
        if (is_null($pplValidation->ppl_id)) {
            $validated['ppl_id'] = $user->id;
        }

        if (in_array($validated['status'], ['validated', 'rejected'])) {
            $validated['validated_at'] = now();
        }

        $pplValidation->update($validated);

        $scan = $pplValidation->scan()->with(['farmer:id,name', 'farm:id,name,farmer_user_id,latitude,longitude'])->first();

        if ($scan) {
            if ($validated['status'] === 'validated') {
                $this->verifyEarlyWarningReport($scan, $this->notif);
            } elseif ($validated['status'] === 'rejected') {
                $this->rejectEarlyWarningReport($scan);
            }
        }

        // Kirim notifikasi ke petani pemilik scan
        if ($scan && $scan->farmer) {
            $statusLabel = match ($validated['status']) {
                'validated'     => 'Divalidasi',
                'rejected'      => 'Tidak terkonfirmasi',
                'needs_revisit' => 'Perlu Pemeriksaan Ulang',
                default         => 'Diperbarui',
            };

            $this->notif->notifyUser(
                user: $scan->farmer,
                title: "Kasus Anda Telah {$statusLabel}",
                body: "Penyuluh {$user->name} telah memverifikasi lapangan untuk dugaan {$scan->predicted_class}. " .
                      ($validated['notes'] ? "Catatan: {$validated['notes']}" : 'Buka aplikasi untuk melihat detail.'),
                type: 'ppl_result',
                data: [
                    'validation_id' => $pplValidation->id,
                    'scan_id'       => $pplValidation->scan_id,
                    'status'        => $validated['status'],
                    'action_url'    => '/ppl-cases',
                ]
            );

            // Beri tahu Admin bahwa penyuluh telah menyelesaikan / mengubah status validasi
            $this->notif->notifyAdmins(
                title: 'Kasus PPL Diperbarui oleh Penyuluh',
                body: "Penyuluh {$user->name} telah mengubah status kasus #{$pplValidation->id} ({$scan->predicted_class}) menjadi '{$validated['status']}'.",
                type: 'ppl_case_update',
                data: [
                    'validation_id' => $pplValidation->id,
                    'scan_id'       => $pplValidation->scan_id,
                    'status'        => $validated['status'],
                    'action_url'    => '/admin/disease',
                ]
            );
        }

        return ApiResponse::success('Validasi PPL berhasil diperbarui.', [
            'validation' => PplValidationResource::make(
                $pplValidation->fresh(['scan.farm:id,name', 'scan.farmer:id,name', 'ppl:id,name'])
            ),
        ]);
    }

    private function verifyEarlyWarningReport(DiseaseScan $scan, AdminNotificationService $notificationService): void
    {
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
                'reported_at' => now(),
            ]
        );

        $report->update([
            'status' => 'verified',
            'radius_km' => $report->radius_km ?: $this->earlyWarningRadiusKm($scan),
            'reported_at' => $report->reported_at ?: now(),
        ]);

        PadiCacheService::invalidateRadarCache();

        $diseaseName = $scan->predicted_class ?? 'Penyakit Padi';
        foreach ($this->nearbyFarmerRecipients($report) as $recipient) {
            $distanceText = number_format((float) $recipient['distance_km'], 1, ',', '.');

            $notificationService->notifyUser(
                (int) $recipient['farmer_id'],
                "Early Warning Tervalidasi: {$diseaseName}",
                "PPL memvalidasi {$diseaseName} sekitar {$distanceText} km dari {$recipient['farm_name']}. Periksa daun dan lakukan pencegahan awal.",
                'early_warning',
                [
                    'report_id' => $report->id,
                    'scan_id' => $scan->id,
                    'disease' => $diseaseName,
                    'distance_km' => round((float) $recipient['distance_km'], 2),
                    'radius_km' => (float) $report->radius_km,
                    'risk_level' => $this->riskLevel(
                        (float) $recipient['distance_km'],
                        (float) $report->radius_km
                    ),
                    'status' => 'verified',
                ]
            );
        }
    }

    private function rejectEarlyWarningReport(DiseaseScan $scan): void
    {
        $updated = CommunityReport::query()
            ->where('scan_id', $scan->id)
            ->update(['status' => 'rejected']);

        if ($updated > 0) {
            PadiCacheService::invalidateRadarCache();
        }
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
