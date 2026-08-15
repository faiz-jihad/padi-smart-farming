<?php

namespace App\Http\Controllers;

use App\Domain\Farm\Actions\CreateFarmAction;
use App\Helpers\ApiResponse;
use App\Http\Requests\Api\V1\Farm\StoreFarmRequest;
use App\Http\Resources\FarmResource;
use App\Models\Farm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $farms = Farm::query()
            ->where('farmer_user_id', $request->user()->id)
            ->latest('id')
            ->get();

        return ApiResponse::success(
            'Data lahan berhasil diambil.',
            [
                'farms' => FarmResource::collection($farms),
            ],
        );
    }

    public function store(
        StoreFarmRequest $request,
        CreateFarmAction $action
    ): JsonResponse {
        $farm = $action->execute(
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
