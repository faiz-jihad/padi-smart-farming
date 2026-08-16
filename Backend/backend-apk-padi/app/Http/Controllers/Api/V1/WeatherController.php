<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GetWeatherRequest;
use App\Http\Resources\WeatherSnapshotResource;
use App\Models\Farm;
use App\Models\WeatherSnapshot;
use App\Services\Weather\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function __construct(
        private WeatherService $weatherService
    ) {}

    /**
     * Get current weather for a farm
     */
    public function currentWeather(GetWeatherRequest $request): JsonResponse
    {
        $farmId = $request->input('farm_id');
        $farm = Farm::findOrFail($farmId);

        // Get weather data
        $weatherData = $this->weatherService->getCurrentWeather(
            $farm->latitude,
            $farm->longitude,
            [
                'units' => $request->input('units', 'metric'),
                'lang' => $request->input('lang', 'id'),
                'force_refresh' => $request->boolean('force_refresh'),
            ]
        );

        if (!$weatherData['success']) {
            return response()->json([
                'success' => false,
                'message' => $weatherData['error'] ?? 'Gagal mengambil data cuaca',
                'data' => null,
            ], 400);
        }

        // Store weather snapshot
        $parsed = $this->weatherService->parseWeatherData($weatherData['data']);

        WeatherSnapshot::updateOrCreate(
            [
                'farm_id' => $farmId,
                'observed_at' => now(),
            ],
            [
                'provider' => $weatherData['provider'],
                'payload_json' => $weatherData['data'],
                'expires_at' => now()->addHours(1),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Data cuaca berhasil diambil',
            'data' => $parsed,
        ]);
    }

    /**
     * Get weather forecast for a farm
     */
    public function forecast(GetWeatherRequest $request): JsonResponse
    {
        $farmId = $request->input('farm_id');
        $farm = Farm::findOrFail($farmId);

        $weatherData = $this->weatherService->getWeatherForecast(
            $farm->latitude,
            $farm->longitude,
            days: 5,
            options: [
                'units' => $request->input('units', 'metric'),
                'lang' => $request->input('lang', 'id'),
                'force_refresh' => $request->boolean('force_refresh'),
            ]
        );

        if (!$weatherData['success']) {
            return response()->json([
                'success' => false,
                'message' => $weatherData['error'] ?? 'Gagal mengambil prakiraan cuaca',
                'data' => null,
            ], 400);
        }

        $forecasts = collect($weatherData['data']['list'] ?? [])->map(function ($item) {
            return $this->weatherService->parseWeatherData($item);
        });

        return response()->json([
            'success' => true,
            'message' => 'Prakiraan cuaca berhasil diambil',
            'data' => [
                'city' => $weatherData['data']['city']['name'] ?? null,
                'country' => $weatherData['data']['city']['country'] ?? null,
                'forecasts' => $forecasts,
            ],
        ]);
    }

    /**
     * Get weather snapshots history
     */
    public function history(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'farm_id' => 'required|integer|exists:farms,id',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        $snapshots = WeatherSnapshot::query()
            ->where('farm_id', $validated['farm_id'])
            ->latest('observed_at')
            ->limit($validated['limit'] ?? 30)
            ->get();

        return response()->json([
            'success' => true,
            'data' => WeatherSnapshotResource::collection($snapshots),
        ]);
    }

    /**
     * Get weather by city name
     */
    public function byCity(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'city' => 'required|string|max:100',
            'units' => 'sometimes|in:metric,imperial',
            'lang' => 'sometimes|string|max:10',
        ]);

        $weatherData = $this->weatherService->getWeatherByCity(
            $validated['city'],
            [
                'units' => $validated['units'] ?? 'metric',
                'lang' => $validated['lang'] ?? 'id',
            ]
        );

        if (!$weatherData['success']) {
            return response()->json([
                'success' => false,
                'message' => $weatherData['error'] ?? 'Gagal mengambil data cuaca',
                'data' => null,
            ], 400);
        }

        $parsed = $this->weatherService->parseWeatherData($weatherData['data']);

        return response()->json([
            'success' => true,
            'message' => 'Data cuaca berhasil diambil',
            'data' => $parsed,
        ]);
    }
}
