<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DiseaseScan\StoreDiseaseScanRequest;
use App\Http\Resources\DiseaseScanResource;
use App\Models\DiseaseScan;
use App\Services\DiseaseDetectionService;
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
            ->with(['farm:id,name,area_ha', 'recommendation'])
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

    public function store(StoreDiseaseScanRequest $request, DiseaseDetectionService $service): JsonResponse
    {
        try {
            $scan = $service->scan(
                $request->user()->id,
                $request->file('image'),
                $request->validated()
            );
        } catch (RuntimeException $error) {
            return ApiResponse::error($error->getMessage(), 503);
        }

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
            'scan' => DiseaseScanResource::make($diseaseScan->load('farm')),
        ]);
    }
}
