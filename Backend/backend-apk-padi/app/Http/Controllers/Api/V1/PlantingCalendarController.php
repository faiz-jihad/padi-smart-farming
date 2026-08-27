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
        $user = $request->user();
        $isAdmin = $user && ($user->role === 'admin' || (method_exists($user, 'hasRole') && $user->hasRole('admin')));
        $isOfficer = $user && ($user->role === 'extension_officer' || (method_exists($user, 'hasRole') && $user->hasRole('extension_officer')));

        if (! $isAdmin && ! $isOfficer && (! $user || $farm->farmer_user_id !== $user->id)) {
            abort(403, 'Anda tidak memiliki akses ke data lahan ini.');
        }

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

    /**
     * Calculate interactive Best Planting Window & Growth Stage Timeline
     */
    public function recommendPlantingWindow(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'farm_id' => 'nullable|integer|exists:farms,id',
            'planned_date' => 'nullable|date',
            'variety_id' => 'nullable|integer|exists:rice_varieties,id',
        ]);

        $recommendation = $this->plantingCalendarService->calculateBestPlantingWindow(
            farmId: $validated['farm_id'] ?? null,
            plannedDateStr: $validated['planned_date'] ?? null,
            varietyId: $validated['variety_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Rekomendasi waktu tanam ideal & timeline pertumbuhan berhasil dihitung',
            'data' => $recommendation,
        ]);
    }

    /**
     * Store a newly created planting calendar / recommendation.
     */
    public function store(\App\Http\Requests\Api\V1\PlantingCalendar\StorePlantingCalendarRequest $request): JsonResponse
    {
        $calendar = $this->plantingCalendarService->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Kalender tanam berhasil ditambahkan',
            'data'    => PlantingCalendarResource::make($calendar),
        ], 201);
    }

    /**
     * Display the specified planting calendar.
     */
    public function show(PlantingCalendar $plantingCalendar): JsonResponse
    {
        $plantingCalendar->loadMissing(['province', 'regency', 'district', 'village']);

        return response()->json([
            'success' => true,
            'message' => 'Detail kalender tanam berhasil diambil',
            'data'    => PlantingCalendarResource::make($plantingCalendar),
        ]);
    }

    /**
     * Update the specified planting calendar in storage.
     */
    public function update(\App\Http\Requests\Api\V1\PlantingCalendar\UpdatePlantingCalendarRequest $request, PlantingCalendar $plantingCalendar): JsonResponse
    {
        $calendar = $this->plantingCalendarService->update($plantingCalendar, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Kalender tanam berhasil diperbarui',
            'data'    => PlantingCalendarResource::make($calendar),
        ]);
    }

    /**
     * Remove the specified planting calendar from storage.
     */
    public function destroy(PlantingCalendar $plantingCalendar): JsonResponse
    {
        $this->plantingCalendarService->delete($plantingCalendar);

        return response()->json([
            'success' => true,
            'message' => 'Kalender tanam berhasil dihapus',
        ]);
    }
}
