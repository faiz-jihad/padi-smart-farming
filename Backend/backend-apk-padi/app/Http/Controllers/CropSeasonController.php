<?php

namespace App\Http\Controllers;

use App\Domain\CropSeason\Actions\CreateCropSeasonAction;
use App\Helpers\ApiResponse;
use App\Http\Requests\Api\V1\CropSeason\StoreCropSeasonRequest;
use App\Http\Resources\CropSeasonResource;
use App\Models\CropSeason;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CropSeasonController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cropSeasons = CropSeason::query()
            ->whereHas('farm', function ($query) use ($request): void {
                $query->where('farmer_user_id', $request->user()->id);
            })
            ->latest('id')
            ->get();

        return ApiResponse::success(
            'Data musim tanam berhasil diambil.',
            [
                'crop_seasons' => CropSeasonResource::collection($cropSeasons),
            ],
        );
    }

    public function store(
        StoreCropSeasonRequest $request,
        CreateCropSeasonAction $action
    ): JsonResponse {
        $cropSeason = $action->execute(
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