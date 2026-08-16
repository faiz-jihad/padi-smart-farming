<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\Api\V1\CropSeason\StoreCropSeasonRequest;
use App\Http\Resources\CropSeasonResource;
use App\Services\CropSeasonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CropSeasonController extends Controller
{
    public function index(
        Request $request,
        CropSeasonService $service
    ): JsonResponse {
        $cropSeasons = $service->getCropSeasons(
            $request->user()
        );

        return ApiResponse::success(
            'Data musim tanam berhasil diambil.',
            [
                'crop_seasons' => CropSeasonResource::collection($cropSeasons),
            ],
        );
    }

    public function store(
        StoreCropSeasonRequest $request,
        CropSeasonService $service
    ): JsonResponse {
        $cropSeason = $service->createCropSeason(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            'Musim tanam berhasil ditambahkan.',
            [
                'crop_season' => CropSeasonResource::make($cropSeason),
            ],
            201,
        );
    }
}