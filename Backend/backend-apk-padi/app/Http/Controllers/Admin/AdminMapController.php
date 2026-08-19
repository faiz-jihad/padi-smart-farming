<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use App\Services\Geo\AdministrativeBoundaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminMapController extends Controller
{
    public function __construct(
        private readonly AdministrativeBoundaryService $geoService
    ) {}

    // ─────────────────────────────────────────────
    // GeoJSON Endpoints (return FeatureCollections)
    // ─────────────────────────────────────────────

    /**
     * GET /admin/map/geo/provinces
     * Returns GeoJSON FeatureCollection of all 38 province polygons.
     */
    public function provincesBoundaries(): JsonResponse
    {
        $geojson = $this->geoService->getProvincesGeoJson();

        return response()->json($geojson)
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * GET /admin/map/geo/regencies?province_id={id}
     * Returns GeoJSON FeatureCollection of regency polygons for the given province.
     */
    public function regenciesBoundaries(Request $request): JsonResponse
    {
        $provinceId = (int) $request->input('province_id', 0);

        if (!$provinceId) {
            // Default to Jawa Barat if not specified
            $prov = Province::where('code', '32')->first();
            $provinceId = $prov?->id ?? 0;
        }

        if (!$provinceId) {
            return response()->json(['type' => 'FeatureCollection', 'features' => []], 200);
        }

        $geojson = $this->geoService->getRegenciesGeoJson($provinceId);

        return response()->json($geojson)
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * GET /admin/map/geo/province/{province}
     */
    public function singleProvince(Province $province): JsonResponse
    {
        $feature = $this->geoService->getSingleProvinceGeoJson($province->id);

        if (!$feature) {
            return response()->json(['error' => 'Boundary not found'], 404);
        }

        return response()->json($feature);
    }

    /**
     * GET /admin/map/geo/regency/{regency}
     */
    public function singleRegency(Regency $regency): JsonResponse
    {
        $feature = $this->geoService->getSingleRegencyGeoJson($regency->id);

        if (!$feature) {
            return response()->json(['error' => 'Boundary not found'], 404);
        }

        return response()->json($feature);
    }

    /**
     * GET /admin/map/geo/districts?regency_id={id}
     * Returns GeoJSON FeatureCollection of district polygons for the given regency.
     */
    public function districtsBoundaries(Request $request): JsonResponse
    {
        $regencyId = (int) $request->input('regency_id', 0);

        if (!$regencyId) {
            // Default to Indramayu if not specified
            $regency   = Regency::where('code', '3212')->first();
            $regencyId = $regency?->id ?? 0;
        }

        if (!$regencyId) {
            return response()->json(['type' => 'FeatureCollection', 'features' => []], 200);
        }

        $geojson = $this->geoService->getDistrictsGeoJson($regencyId);

        return response()->json($geojson)
            ->header('Cache-Control', 'public, max-age=900');
    }

    /**
     * GET /admin/map/geo/villages?district_id={id}
     * Returns GeoJSON FeatureCollection of village polygons for the given district.
     */
    public function villagesBoundaries(Request $request): JsonResponse
    {
        $districtId = (int) $request->input('district_id', 0);

        if (!$districtId) {
            return response()->json(['type' => 'FeatureCollection', 'features' => []], 200);
        }

        $geojson = $this->geoService->getVillagesGeoJson($districtId);

        return response()->json($geojson)
            ->header('Cache-Control', 'public, max-age=900');
    }

    /**
     * GET /admin/map/geo/farms?village_id={id}
     * Returns GeoJSON FeatureCollection of farm polygons/points for the given village.
     */
    public function farmsBoundaries(Request $request): JsonResponse
    {
        $villageId = (int) $request->input('village_id', 0);

        if (!$villageId) {
            return response()->json(['type' => 'FeatureCollection', 'features' => []], 200);
        }

        $geojson = $this->geoService->getFarmsGeoJson($villageId);

        return response()->json($geojson);
    }

    // ─────────────────────────────────────────────
    // Summary Endpoints (analytics side-panel data)
    // ─────────────────────────────────────────────

    /**
     * GET /admin/map/districts/{district}/summary
     * Returns aggregated statistics for a district (kecamatan).
     */
    public function districtSummary(District $district): JsonResponse
    {
        $summary = $this->geoService->getDistrictSummary($district);

        return response()->json($summary);
    }

    /**
     * GET /admin/map/villages/{village}/summary
     * Returns aggregated statistics for a village (desa).
     */
    public function villageSummary(Village $village): JsonResponse
    {
        $summary = $this->geoService->getVillageSummary($village);

        return response()->json($summary);
    }

    // ─────────────────────────────────────────────
    // Region Metadata Endpoints
    // ─────────────────────────────────────────────

    /**
     * GET /admin/map/provinces
     * Returns list of all 38 provinces.
     */
    public function provinces(): JsonResponse
    {
        $provinces = Province::select('id', 'name', 'code', 'latitude', 'longitude')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $provinces]);
    }

    /**
     * GET /admin/map/regencies
     * Returns list of all regencies with name, code, lat, lng.
     */
    public function regencies(Request $request): JsonResponse
    {
        $query = Regency::select('id', 'name', 'code', 'latitude', 'longitude', 'province_id')
            ->with('province:id,name,code');

        if ($provinceId = $request->input('province_id')) {
            $query->where('province_id', $provinceId);
        }

        $regencies = $query->orderBy('name')->get();

        return response()->json(['data' => $regencies]);
    }
}
