<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Location\ResolveLocationRequest;
use App\Services\Geography\LocationService;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function __construct(
        private LocationService $locationService
    ) {}

    /**
     * Resolve GPS coordinates to Administrative Hierarchy.
     */
    public function resolve(ResolveLocationRequest $request): JsonResponse
    {
        $lat = (float) $request->input('latitude');
        $lng = (float) $request->input('longitude');

        $result = $this->locationService->resolveCoordinates($lat, $lng);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Koordinat tidak berada di dalam wilayah administratif yang terdaftar',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Wilayah administratif berhasil dideteksi',
            'data'    => $result,
        ]);
    }
}
