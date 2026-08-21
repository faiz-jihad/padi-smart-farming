<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Harvest\StoreHarvestRequest;
use App\Http\Requests\Api\V1\Harvest\UpdateHarvestRequest;
use App\Http\Resources\HarvestResource;
use App\Models\Harvest;
use App\Services\HarvestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HarvestController extends Controller
{
    public function index(
        Request $request,
        HarvestService $harvests
    ): JsonResponse {
        $harvestData = $harvests->getHarvests($request->user());

        if ($request->filled('crop_season_id')) {
            $harvestData = $harvestData->where(
                'crop_season_id',
                $request->integer('crop_season_id')
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar data panen berhasil diambil',
            'data' => HarvestResource::collection($harvestData),
        ]);
    }

    public function store(
        StoreHarvestRequest $request,
        HarvestService $harvests
    ): JsonResponse {
        $harvest = $harvests->createHarvest(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Data panen berhasil ditambahkan',
            'data' => new HarvestResource($harvest),
        ], 201);
    }

    public function show(
        Request $request,
        Harvest $harvest,
        HarvestService $harvests
    ): JsonResponse {
        $harvest = $harvests->getHarvest(
            $request->user(),
            $harvest
        );

        return response()->json([
            'success' => true,
            'message' => 'Detail data panen berhasil diambil',
            'data' => new HarvestResource($harvest),
        ]);
    }

    public function update(
        UpdateHarvestRequest $request,
        Harvest $harvest,
        HarvestService $harvests
    ): JsonResponse {
        $harvest = $harvests->updateHarvest(
            $request->user(),
            $harvest,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Data panen berhasil diperbarui',
            'data' => new HarvestResource($harvest),
        ]);
    }

    public function destroy(
        Request $request,
        Harvest $harvest,
        HarvestService $harvests
    ): JsonResponse {
        $harvests->deleteHarvest(
            $request->user(),
            $harvest
        );

        return response()->json([
            'success' => true,
            'message' => 'Data panen berhasil dihapus',
            'data' => null,
        ]);
    }
}