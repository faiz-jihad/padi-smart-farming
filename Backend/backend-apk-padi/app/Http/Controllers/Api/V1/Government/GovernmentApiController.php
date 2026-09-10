<?php

namespace App\Http\Controllers\Api\V1\Government;

use App\Http\Controllers\Controller;
use App\Http\Resources\Government\GovernmentActivityResource;
use App\Http\Resources\Government\GovernmentDiseaseResource;
use App\Http\Resources\Government\GovernmentInsightResource;
use App\Http\Resources\Government\GovernmentOverviewResource;
use App\Http\Resources\Government\GovernmentProductivityResource;
use App\Services\Government\GovernmentDataService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GovernmentApiController extends Controller
{
    public function __construct(
        protected GovernmentDataService $governmentDataService
    ) {}

    /**
     * GET /api/v1/government/overview
     */
    public function overview(Request $request): JsonResponse
    {
        $overview = $this->governmentDataService->getOverviewData();

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan data B2G berhasil diambil.',
            'data'    => new GovernmentOverviewResource($overview),
            'meta'    => [
                'generated_at' => Carbon::now()->toIso8601String(),
                'source'       => 'P.A.D.I. Smart Farming B2G Data Platform',
            ],
        ]);
    }

    /**
     * GET /api/v1/government/activities
     */
    public function activities(Request $request): JsonResponse
    {
        $perPage = min(100, max(5, $request->integer('per_page', 15)));
        $activities = $this->governmentDataService->getActivitiesData($request, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar riwayat aktivitas pertanian berhasil diambil.',
            'data'    => GovernmentActivityResource::collection($activities->items()),
            'pagination' => [
                'current_page' => $activities->currentPage(),
                'last_page'    => $activities->lastPage(),
                'per_page'     => $activities->perPage(),
                'total'        => $activities->total(),
            ],
            'meta' => [
                'filters' => [
                    'start_date'    => $request->query('start_date'),
                    'end_date'      => $request->query('end_date'),
                    'region'        => $request->query('region'),
                    'commodity'     => $request->query('commodity'),
                    'activity_type' => $request->query('activity_type'),
                ],
                'generated_at' => Carbon::now()->toIso8601String(),
                'source'       => 'P.A.D.I. Smart Farming B2G Data Platform',
            ],
        ]);
    }

    /**
     * GET /api/v1/government/diseases
     */
    public function diseases(Request $request): JsonResponse
    {
        $perPage = min(100, max(5, $request->integer('per_page', 15)));
        $diseases = $this->governmentDataService->getDiseasesData($request, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data pemantauan penyakit tanaman berhasil diambil.',
            'data'    => GovernmentDiseaseResource::collection($diseases->items()),
            'pagination' => [
                'current_page' => $diseases->currentPage(),
                'last_page'    => $diseases->lastPage(),
                'per_page'     => $diseases->perPage(),
                'total'        => $diseases->total(),
            ],
            'meta' => [
                'filters' => [
                    'start_date' => $request->query('start_date'),
                    'end_date'   => $request->query('end_date'),
                    'region'     => $request->query('region'),
                    'disease'    => $request->query('disease'),
                ],
                'generated_at' => Carbon::now()->toIso8601String(),
                'source'       => 'P.A.D.I. Smart Farming B2G Data Platform',
            ],
        ]);
    }

    /**
     * GET /api/v1/government/productivity
     */
    public function productivity(Request $request): JsonResponse
    {
        $perPage = min(100, max(5, $request->integer('per_page', 15)));
        $productivity = $this->governmentDataService->getProductivityData($request, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data estimasi produktivitas pertanian berhasil diambil.',
            'data'    => GovernmentProductivityResource::collection($productivity->items()),
            'pagination' => [
                'current_page' => $productivity->currentPage(),
                'last_page'    => $productivity->lastPage(),
                'per_page'     => $productivity->perPage(),
                'total'        => $productivity->total(),
            ],
            'meta' => [
                'filters' => [
                    'region'    => $request->query('region'),
                    'commodity' => $request->query('commodity'),
                ],
                'generated_at' => Carbon::now()->toIso8601String(),
                'source'       => 'P.A.D.I. Smart Farming B2G Data Platform',
            ],
        ]);
    }

    /**
     * GET /api/v1/government/insights
     */
    public function insights(Request $request): JsonResponse
    {
        $insights = $this->governmentDataService->getInsightsData();

        return response()->json([
            'success' => true,
            'message' => 'Data agregasi insight pertanian berhasil diambil.',
            'data'    => new GovernmentInsightResource($insights),
            'meta'    => [
                'generated_at' => Carbon::now()->toIso8601String(),
                'source'       => 'P.A.D.I. Smart Farming B2G Data Platform',
            ],
        ]);
    }
}
