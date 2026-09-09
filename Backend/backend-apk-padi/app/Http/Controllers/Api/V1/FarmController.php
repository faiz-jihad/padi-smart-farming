<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Farm\StoreFarmRequest;
use App\Http\Requests\Api\V1\Farm\UpdateFarmRequest;
use App\Http\Resources\FarmResource;
use App\Models\Farm;
use App\Services\FarmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmController extends Controller
{
    public function __construct(
        private FarmService $farmService
    ) {}

    /**
     * List current user's farms
     */
    public function index(Request $request): JsonResponse
    {
        $farms = $this->farmService->getFarms($request->user());
        $user = $request->user();

        $farms = Farm::query()
            ->when(! $user->hasRole('admin'), function ($query) use ($user): void {
                $query->where('farmer_user_id', $user->id);
            })
            ->with(['soilType', 'irrigationType', 'province', 'regency', 'district', 'village'])
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar lahan berhasil diambil',
            'data'    => FarmResource::collection($farms),
        ]);
    }

    /**
     * Store a new farm with auto-resolving region from GPS if not provided
     */
    public function store(StoreFarmRequest $request): JsonResponse
{
    $data = $request->validated();

    /*
     * Backward compatibility:
     * Accept soil type by ID, code, or name.
     */
    if (!empty($data['soil_type'])) {
        $soilType = \App\Models\SoilType::query()
            ->where('code', $data['soil_type'])
            ->orWhere('name', $data['soil_type'])
            ->orWhere('id', $data['soil_type'])
            ->first();

        if ($soilType) {
            $data['soil_type_id'] = $soilType->id;
            $data['soil_type'] = $soilType->code;
        }
    } elseif (!empty($data['soil_type_id'])) {
        $soilType = \App\Models\SoilType::find($data['soil_type_id']);
        if ($soilType) {
            $data['soil_type'] = $soilType->code;
        }
    }

    /*
     * Backward compatibility:
     * Accept irrigation type by ID, code, or name.
     * The database relation uses irrigation_type_id.
     */
    if (!empty($data['irrigation_type'])) {
        $cleanCode = strtolower(trim(str_replace(['irigasi_', 'irigasi-'], '', $data['irrigation_type'])));
        $irrigationType = \App\Models\IrrigationType::query()
            ->where('code', $data['irrigation_type'])
            ->orWhere('code', $cleanCode)
            ->orWhere('name', $data['irrigation_type'])
            ->orWhere('id', $data['irrigation_type'])
            ->first();

        if (!$irrigationType) {
            $irrigationType = \App\Models\IrrigationType::first();
        }

        if ($irrigationType) {
            $data['irrigation_type_id'] = $irrigationType->id;
            $data['irrigation_type'] = $irrigationType->code;
        }
    } elseif (!empty($data['irrigation_type_id'])) {
        $irrigationType = \App\Models\IrrigationType::find(
            $data['irrigation_type_id']
        );

        if ($irrigationType) {
            $data['irrigation_type'] = $irrigationType->code;
        }
    }

    $farm = $this->farmService->createFarm(
        $request->user(),
        $data
    );

    $farm->load(['soilType', 'irrigationType']);

    return response()->json([
        'success' => true,
        'message' => 'Lahan berhasil ditambahkan.',
        'data' => new FarmResource($farm),
    ], 201);
}

    /**
     * Show farm detail
     */
    public function show(Request $request, Farm $farm): JsonResponse
    {
        $this->authorizeFarm($request->user(), $farm);

        $farm->load(['soilType', 'irrigationType', 'province', 'regency', 'district', 'village']);

        return response()->json([
            'success' => true,
            'message' => 'Detail lahan berhasil diambil',
            'data'    => new FarmResource($farm),
        ]);
    }

    /**
     * Update farm
     */
    public function update(
        UpdateFarmRequest $request,
        Farm $farm
    ): JsonResponse {
        $this->authorizeFarm($request->user(), $farm);

        $data = $request->validated();

        /*
        * Backward compatibility:
        * Accept soil type by ID, code, or name.
        */
        if (array_key_exists('soil_type', $data) || array_key_exists('soil_type_id', $data)) {
            if (!empty($data['soil_type'])) {
                $soilType = \App\Models\SoilType::query()
                    ->where('code', $data['soil_type'])
                    ->orWhere('name', $data['soil_type'])
                    ->orWhere('id', $data['soil_type'])
                    ->first();

                if ($soilType) {
                    $data['soil_type_id'] = $soilType->id;
                    $data['soil_type'] = $soilType->code;
                }
            } elseif (!empty($data['soil_type_id'])) {
                $soilType = \App\Models\SoilType::find($data['soil_type_id']);
                if ($soilType) {
                    $data['soil_type'] = $soilType->code;
                }
            } else {
                $data['soil_type_id'] = null;
                $data['soil_type'] = null;
            }
        }

        /*
        * Backward compatibility:
        * Accept irrigation type by ID, code, or name.
        */
        if (!empty($data['irrigation_type'])) {
            $irrigationType = \App\Models\IrrigationType::query()
                ->where('code', $data['irrigation_type'])
                ->orWhere('name', $data['irrigation_type'])
                ->orWhere('id', $data['irrigation_type'])
                ->first();

            if ($irrigationType) {
                $data['irrigation_type_id'] = $irrigationType->id;
                $data['irrigation_type'] = $irrigationType->code;
            }
        } elseif (!empty($data['irrigation_type_id'])) {
            $irrigationType = \App\Models\IrrigationType::find(
                $data['irrigation_type_id']
            );

            if ($irrigationType) {
                $data['irrigation_type'] = $irrigationType->code;
            }
        }

        $farm = $this->farmService->updateFarm($farm, $data);

        $farm->load(['soilType', 'irrigationType']);

        return response()->json([
            'success' => true,
            'message' => 'Lahan berhasil diperbarui.',
            'data' => new FarmResource($farm),
        ]);
    }

    /**
     * Delete farm
     */
    public function destroy(Request $request, Farm $farm): JsonResponse
    {
        $this->authorizeFarm($request->user(), $farm);

        $this->farmService->deleteFarm($farm);

        return response()->json([
            'success' => true,
            'message' => 'Lahan berhasil dihapus',
            'data'    => null,
        ]);
    }

    private function authorizeFarm($user, Farm $farm): void
    {
        if ($farm->farmer_user_id !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Anda tidak memiliki akses ke data lahan ini');
        }
    }
}
