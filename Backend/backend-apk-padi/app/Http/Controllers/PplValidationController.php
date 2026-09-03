<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\PplValidationResource;
use App\Models\DiseaseScan;
use App\Models\PplValidation;
use App\Models\User;
use App\Services\Admin\AdminNotificationService;
use App\Services\Api\ApiResourceIndexService;
use App\Enums\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        ]);

        $scan = DiseaseScan::findOrFail($validated['scan_id']);

        // Pastikan scan milik petani yang sedang login
        if ($scan->farmer_id !== $user->id && ! $user->hasRole('admin')) {
            return ApiResponse::error('Anda tidak memiliki akses ke scan ini.', 403);
        }

        // Cegah duplikat submission untuk scan yang sama
        $existing = PplValidation::where('scan_id', $scan->id)->first();
        if ($existing) {
            return ApiResponse::success('Kasus ini sudah pernah dikirim ke penyuluh sebelumnya.', [
                'validation' => PplValidationResource::make($existing->load(['scan.farm', 'scan.farmer', 'ppl'])),
            ]);
        }

        $validation = PplValidation::create([
            'scan_id' => $scan->id,
            'ppl_id'  => null, // Belum ditugaskan ke PPL tertentu
            'status'  => 'pending',
            'notes'   => null,
        ]);

        // Kirim notifikasi ke semua PPL aktif
        $disease = $scan->predicted_class ?? 'Tidak diketahui';
        $confidence = $scan->confidence
            ? round((float) $scan->confidence * 100, 1) . '%'
            : '-';

        $this->notif->notifyExtensionOfficers(
            title: 'Kasus Baru Menunggu Validasi',
            body: "Petani {$user->name} melaporkan dugaan {$disease} (keyakinan AI: {$confidence}). Mohon validasi lapangan.",
            type: 'ppl_case',
            data: [
                'validation_id' => $validation->id,
                'scan_id'       => $scan->id,
                'farmer_name'   => $user->name,
                'disease'       => $disease,
                'confidence'    => $confidence,
                'action_url'    => "/ppl-cases/{$validation->id}",
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

        // Kirim notifikasi ke petani pemilik scan
        $scan = $pplValidation->scan()->with('farmer:id,name')->first();
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
                    'action_url'    => '/plant-check/history',
                ]
            );
        }

        return ApiResponse::success('Validasi PPL berhasil diperbarui.', [
            'validation' => PplValidationResource::make(
                $pplValidation->fresh(['scan.farm:id,name', 'scan.farmer:id,name', 'ppl:id,name'])
            ),
        ]);
    }
}

