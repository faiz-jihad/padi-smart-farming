<?php

namespace App\Http\Controllers;

use App\Domain\Farm\Actions\CreateFarmAction;
use App\Helpers\ApiResponse;
use App\Http\Requests\Api\V1\Farm\StoreFarmRequest;
use App\Http\Resources\FarmResource;
use App\Services\Api\FarmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmController extends Controller
{
    public function index(Request $request, FarmService $farms): JsonResponse
    {
        return ApiResponse::success(
            'Data lahan berhasil diambil.',
            [
                'farms' => FarmResource::collection($farms->listForUser($request->user())),
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
