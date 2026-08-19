<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Village;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MapController extends Controller
{
    /**
     * Get GeoJSON FeatureCollection for district boundaries in a regency.
     */
    public function districts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'regency_id' => 'required|integer|exists:regencies,id',
        ]);

        $regencyId = (int) $validated['regency_id'];
        $cacheKey = "map:districts:regency:{$regencyId}";

        $featureCollection = Cache::remember($cacheKey, now()->addHours(24), function () use ($regencyId) {
            $districts = District::with('boundary')
                ->where('regency_id', $regencyId)
                ->get();

            $features = [];

            foreach ($districts as $district) {
                if (!$district->boundary || empty($district->boundary->geometry)) {
                    continue;
                }

                $geometry = json_decode($district->boundary->geometry, true);
                if (!$geometry) {
                    continue;
                }

                $features[] = [
                    'type'       => 'Feature',
                    'id'         => $district->id,
                    'properties' => [
                        'district_id'   => $district->id,
                        'regency_id'    => $district->regency_id,
                        'code'          => $district->code,
                        'name'          => $district->name,
                        'latitude'      => $district->latitude,
                        'longitude'     => $district->longitude,
                        'bbox'          => $district->boundary->bbox,
                    ],
                    'geometry'   => $geometry,
                ];
            }

            return [
                'type'     => 'FeatureCollection',
                'features' => $features,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'GeoJSON batas kecamatan berhasil diambil',
            'data'    => $featureCollection,
        ]);
    }

    /**
     * Get GeoJSON FeatureCollection for village boundaries in a district.
     */
    public function villages(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'district_id' => 'required|integer|exists:districts,id',
        ]);

        $districtId = (int) $validated['district_id'];
        $cacheKey = "map:villages:district:{$districtId}";

        $featureCollection = Cache::remember($cacheKey, now()->addHours(24), function () use ($districtId) {
            $villages = Village::with('boundary')
                ->where('district_id', $districtId)
                ->get();

            $features = [];

            foreach ($villages as $village) {
                if (!$village->boundary || empty($village->boundary->geometry)) {
                    continue;
                }

                $geometry = json_decode($village->boundary->geometry, true);
                if (!$geometry) {
                    continue;
                }

                $features[] = [
                    'type'       => 'Feature',
                    'id'         => $village->id,
                    'properties' => [
                        'village_id'    => $village->id,
                        'district_id'   => $village->district_id,
                        'code'          => $village->code,
                        'name'          => $village->name,
                        'type'          => $village->type?->value,
                        'latitude'      => $village->latitude,
                        'longitude'     => $village->longitude,
                        'bbox'          => $village->boundary->bbox,
                    ],
                    'geometry'   => $geometry,
                ];
            }

            return [
                'type'     => 'FeatureCollection',
                'features' => $features,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'GeoJSON batas desa/kelurahan berhasil diambil',
            'data'    => $featureCollection,
        ]);
    }
}
