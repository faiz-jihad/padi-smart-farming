<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\ProvinceResource;
use App\Http\Resources\RegencyResource;
use App\Http\Resources\VillageResource;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    /**
     * Get list of provinces
     */
    public function provinces(): JsonResponse
    {
        $provinces = Province::query()
            ->select(['id', 'code', 'name', 'latitude', 'longitude'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar provinsi berhasil diambil',
            'data'    => ProvinceResource::collection($provinces),
        ]);
    }

    /**
     * Get list of regencies by province_id
     */
    public function regencies(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province_id' => 'required|integer|exists:provinces,id',
        ]);

        $regencies = Regency::query()
            ->select(['id', 'province_id', 'code', 'name', 'type', 'latitude', 'longitude'])
            ->where('province_id', $validated['province_id'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar kabupaten/kota berhasil diambil',
            'data'    => RegencyResource::collection($regencies),
        ]);
    }

    /**
     * Get list of districts by regency_id
     */
    public function districts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'regency_id' => 'required|integer|exists:regencies,id',
        ]);

        $districts = District::query()
            ->select(['id', 'regency_id', 'code', 'name', 'latitude', 'longitude'])
            ->where('regency_id', $validated['regency_id'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar kecamatan berhasil diambil',
            'data'    => DistrictResource::collection($districts),
        ]);
    }

    /**
     * Get list of villages by district_id
     */
    public function villages(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'district_id' => 'required|integer|exists:districts,id',
        ]);

        $villages = Village::query()
            ->select(['id', 'district_id', 'code', 'name', 'type', 'latitude', 'longitude'])
            ->where('district_id', $validated['district_id'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar desa/kelurahan berhasil diambil',
            'data'    => VillageResource::collection($villages),
        ]);
    }

    /**
     * Search administrative regions across hierarchy
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q'     => 'required|string|min:2|max:100',
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $query = $validated['q'];
        $limit = $validated['limit'] ?? 20;

        $districts = District::query()
            ->select(['id', 'regency_id', 'name', 'latitude', 'longitude'])
            ->with([
                'regency:id,province_id,name',
                'regency.province:id,name',
            ])
            ->where('name', 'like', "%{$query}%")
            ->limit($limit)
            ->get()
            ->map(function ($district) {
                return [
                    'id'            => $district->id,
                    'type'          => 'district',
                    'name'          => $district->name,
                    'full_name'     => "Kecamatan {$district->name}, {$district->regency?->name}, {$district->regency?->province?->name}",
                    'district_id'   => $district->id,
                    'regency_id'    => $district->regency_id,
                    'province_id'   => $district->regency?->province_id,
                    'latitude'      => $district->latitude,
                    'longitude'     => $district->longitude,
                ];
            });

        $villages = Village::query()
            ->select(['id', 'district_id', 'name', 'latitude', 'longitude'])
            ->with([
                'district:id,regency_id,name',
                'district.regency:id,province_id,name',
                'district.regency.province:id,name',
            ])
            ->where('name', 'like', "%{$query}%")
            ->limit($limit)
            ->get()
            ->map(function ($village) {
                return [
                    'id'            => $village->id,
                    'type'          => 'village',
                    'name'          => $village->name,
                    'full_name'     => "Desa {$village->name}, Kec. {$village->district?->name}, {$village->district?->regency?->name}",
                    'village_id'    => $village->id,
                    'district_id'   => $village->district_id,
                    'regency_id'    => $village->district?->regency_id,
                    'province_id'   => $village->district?->regency?->province_id,
                    'latitude'      => $village->latitude,
                    'longitude'     => $village->longitude,
                ];
            });

        $results = $districts->concat($villages)->take($limit)->values();

        return response()->json([
            'success' => true,
            'message' => 'Hasil pencarian wilayah',
            'data'    => $results,
        ]);
    }
}
