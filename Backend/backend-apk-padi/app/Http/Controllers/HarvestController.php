<?php

namespace App\Http\Controllers;

use App\Domain\Harvest\Actions\CreateHarvestAction;
use App\Http\Requests\Api\V1\Harvest\StoreHarvestRequest;
use App\Http\Resources\HarvestResource;
use App\Services\Api\ApiResourceIndexService;
use Illuminate\Http\JsonResponse;

class HarvestController extends Controller
{
    public function index(ApiResourceIndexService $resources): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data panen berhasil diambil.',
            'data' => [
                'harvests' => HarvestResource::collection(
                    $resources->harvests()
                ),
            ],
        ]);
    }

    public function store(
        StoreHarvestRequest $request,
        CreateHarvestAction $action
    ): JsonResponse {
        $harvest = $action->execute(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Catatan panen berhasil disimpan.',
            'data' => [
                'harvest' => HarvestResource::make($harvest),
            ],
        ], 201);
    }
}
