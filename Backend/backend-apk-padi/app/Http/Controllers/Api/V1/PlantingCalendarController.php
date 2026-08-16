<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlantingCalendarResource;
use App\Models\District;
use App\Models\Farm;
use App\Models\PlantingCalendar;
use App\Services\Agriculture\PlantingCalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlantingCalendarController extends Controller
{
    public function __construct(
        private PlantingCalendarService $plantingCalendarService
    ) {}

    /**
     * List all planting calendars with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province_id' => 'sometimes|integer|exists:provinces,id',
            'regency_id'  => 'sometimes|integer|exists:regencies,id',
            'district_id' => 'sometimes|integer|exists:districts,id',
            'village_id'  => 'sometimes|integer|exists:villages,id',
            'season'      => 'sometimes|in:rainy,dry,transition',
            'year'        => 'sometimes|integer|min:2020|max:2035',
            'status'      => 'sometimes|in:draft,active,inactive',
        ]);

        $query = PlantingCalendar::with(['province', 'regency', 'district', 'village']);

        if (isset($validated['province_id'])) {
            $query->where('province_id', $validated['province_id']);
        }
        if (isset($validated['regency_id'])) {
            $query->where('regency_id', $validated['regency_id']);
        }
        if (isset($validated['district_id'])) {
            $query->where('district_id', $validated['district_id']);
        }
        if (isset($validated['village_id'])) {
            $query->where('village_id', $validated['village_id']);
        }
        if (isset($validated['season'])) {
            $query->where('season', $validated['season']);
        }
        if (isset($validated['year'])) {
            $query->where('year', $validated['year']);
        }
        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $calendars = $query->orderBy('planting_start', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar kalender tanam berhasil diambil',
            'data'    => PlantingCalendarResource::collection($calendars),
        ]);
    }

    /**
     * Get active planting calendar for a specific district (with hierarchical fallback)
     */
    public function byDistrict(Request $request, District $district): JsonResponse
    {
        $year = $request->input('year') ? (int) $request->input('year') : null;
        $season = $request->input('season');

        $calendar = $this->plantingCalendarService->getForLocation(
            districtId: $district->id,
            regencyId: $district->regency_id,
            year: $year,
            season: $season
        );

        if (!$calendar) {
            return response()->json([
                'success' => false,
                'message' => "Kalender tanam untuk Kecamatan {$district->name} belum tersedia",
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kalender tanam kecamatan berhasil diambil',
            'data'    => $calendar,
        ]);
    }

    /**
     * Get active planting calendar recommendation for a specific Farm
     */
    public function byFarm(Request $request, Farm $farm): JsonResponse
    {
        $year = $request->input('year') ? (int) $request->input('year') : null;
        $season = $request->input('season');

        $calendar = $this->plantingCalendarService->getForFarm($farm, $year, $season);

        if (!$calendar) {
            return response()->json([
                'success' => false,
                'message' => "Rekomendasi kalender tanam untuk lahan {$farm->name} belum tersedia",
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rekomendasi kalender tanam lahan berhasil diambil',
            'data'    => $calendar,
        ]);
    }
}
