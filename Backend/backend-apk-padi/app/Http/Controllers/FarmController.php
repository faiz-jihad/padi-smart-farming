<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Api\V1\Farm\StoreFarmRequest;
use App\Http\Resources\FarmResource;
use App\Services\FarmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmController extends Controller
{
    public function index(
        Request $request,
        FarmService $service
    ): JsonResponse {
        $farms = $service->getFarms($request->user());

        return ApiResponse::success(
            'Data lahan berhasil diambil.',
            [
                'farms' => FarmResource::collection($farms),
            ],
        );
    }

    public function store(
        StoreFarmRequest $request,
        FarmService $service
    ): JsonResponse {
        $farm = $service->createFarm(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            'Lahan berhasil ditambahkan.',
            [
                'farm' => FarmResource::make($farm),
            ],
            201,
        );
    }
}