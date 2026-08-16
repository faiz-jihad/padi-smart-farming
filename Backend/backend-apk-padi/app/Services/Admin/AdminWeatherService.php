<?php

namespace App\Services\Admin;

use App\Models\Farm;
use App\Models\WeatherSnapshot;
use App\Services\Weather\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminWeatherService
{
    public function __construct(
        private WeatherService $weatherService
    ) {}

    /**
     * Get data for weather dashboard
     */
    public function indexData(Request $request): array
    {
        $farms = Farm::with('weatherSnapshots')
            ->paginate(15);

        $latestSnapshots = WeatherSnapshot::latest('observed_at')
            ->limit(10)
            ->get();

        $stats = [
            'total_farms' => Farm::count(),
            'farms_with_weather' => WeatherSnapshot::distinct('farm_id')->count('farm_id'),
            'total_snapshots' => WeatherSnapshot::count(),
            'expired_snapshots' => WeatherSnapshot::where('expires_at', '<', now())->count(),
        ];

        return [
            'farms' => $farms,
            'latestSnapshots' => $latestSnapshots,
            'stats' => $stats,
        ];
    }

    /**
     * Get historical weather data
     */
    public function historyData(Request $request): array
    {
        $farmId = $request->input('farm_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $query = WeatherSnapshot::query();

        if ($farmId) {
            $query->where('farm_id', $farmId);
        }

        if ($fromDate) {
            $query->whereDate('observed_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('observed_at', '<=', $toDate);
        }

        $snapshots = $query->latest('observed_at')->paginate(20);

        return [
            'snapshots' => $snapshots,
            'farms' => Farm::all(),
            'filters' => [
                'farm_id' => $farmId,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
        ];
    }

    /**
     * Refresh weather data for a specific farm
     */
    public function refreshWeatherData(int $farmId): bool
    {
        $farm = Farm::findOrFail($farmId);

        try {
            $weatherData = $this->weatherService->getCurrentWeather(
                $farm->latitude,
                $farm->longitude,
                ['force_refresh' => true]
            );

            if (!$weatherData['success']) {
                return false;
            }

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

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Export weather data
     */
    public function exportWeatherData(array $filters)
    {
        $query = WeatherSnapshot::query();

        if (isset($filters['farm_id'])) {
            $query->where('farm_id', $filters['farm_id']);
        }

        if (isset($filters['from_date'])) {
            $query->whereDate('observed_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->whereDate('observed_at', '<=', $filters['to_date']);
        }

        $data = $query->latest('observed_at')->get();

        if (($filters['format'] ?? 'csv') === 'json') {
            return response()->json($data->toArray(), 200, [], JSON_PRETTY_PRINT);
        }

        // CSV format
        $csv = "Farm ID,Provider,Observed At,Temperature,Humidity,Weather,Wind Speed,Expires At\n";

        foreach ($data as $snapshot) {
            $payload = $snapshot->payload_json;
            $csv .= sprintf(
                '%d,%s,%s,%s,%s,%s,%s,%s',
                $snapshot->farm_id,
                $snapshot->provider,
                $snapshot->observed_at,
                $payload['main']['temp'] ?? 'N/A',
                $payload['main']['humidity'] ?? 'N/A',
                $payload['weather'][0]['main'] ?? 'N/A',
                $payload['wind']['speed'] ?? 'N/A',
                $snapshot->expires_at
            ) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="weather-data.csv"',
        ]);
    }

    /**
     * Update weather settings
     */
    public function updateSettings(array $settings): bool
    {
        try {
            // Store settings in cache or config
            Cache::put('weather.provider', $settings['weather_provider'], now()->addMonth());

            if (isset($settings['weather_api_key'])) {
                // In production, this should be stored securely
                Cache::put('weather.api_key', $settings['weather_api_key'], now()->addMonth());
            }

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Test weather API connection
     */
    public function testWeatherConnection(): array
    {
        try {
            // Try to get weather for a test location (e.g., Jakarta)
            $result = $this->weatherService->getWeatherByCity('Jakarta');

            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'API connection successful',
                    'data' => $result,
                ];
            }

            return [
                'success' => false,
                'message' => $result['error'] ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get weather statistics
     */
    public function getWeatherStats(): array
    {
        return [
            'total_snapshots' => WeatherSnapshot::count(),
            'latest_snapshot' => WeatherSnapshot::latest('observed_at')->first(),
            'farms_with_data' => WeatherSnapshot::distinct('farm_id')->count('farm_id'),
            'providers_used' => WeatherSnapshot::distinct('provider')->pluck('provider')->toArray(),
            'expired_count' => WeatherSnapshot::where('expires_at', '<', now())->count(),
            'expiring_soon' => WeatherSnapshot::whereBetween('expires_at', [
                now(),
                now()->addHours(1)
            ])->count(),
        ];
    }
}
