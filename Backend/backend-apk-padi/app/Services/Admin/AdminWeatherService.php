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
     *
     * @return array<string, mixed>
     */
    public function indexData(Request $request): array
    {
        $search = trim((string) $request->query('search', ''));

        $farmsQuery = Farm::query()
            ->with(['farmer', 'weatherSnapshots' => function ($q): void {
                $q->latest('observed_at');
            }])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('farmer', function ($fq) use ($search): void {
                        $fq->where('name', 'like', "%{$search}%");
                    });
            });

        $farms = $farmsQuery->latest('id')->paginate(12);

        // Auto-initialize weather data for farms that do not have any snapshots yet
        foreach ($farms as $farm) {
            if ($farm->weatherSnapshots->isEmpty()) {
                $this->refreshWeatherData($farm->id);
                $farm->load(['weatherSnapshots' => function ($q): void {
                    $q->latest('observed_at');
                }]);
            }
        }

        $latestSnapshots = WeatherSnapshot::query()
            ->with('farm.farmer')
            ->latest('observed_at')
            ->limit(10)
            ->get();

        $stats = [
            'total_farms' => Farm::query()->count(),
            'farms_with_weather' => WeatherSnapshot::query()->distinct('farm_id')->count('farm_id'),
            'total_snapshots' => WeatherSnapshot::query()->count(),
            'expired_snapshots' => WeatherSnapshot::query()->where('expires_at', '<', now())->count(),
        ];

        return [
            'title' => 'Manajemen Cuaca',
            'farms' => $farms,
            'latestSnapshots' => $latestSnapshots,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
            ],
            'farmsForMap' => Farm::query()->with('farmer')->get(['id', 'name', 'latitude', 'longitude', 'boundary_coordinates', 'area_ha', 'farmer_user_id'])
                ->map(function ($f) {
                    $lat = $f->latitude ? (float) $f->latitude : null;
                    $lng = $f->longitude ? (float) $f->longitude : null;

                    if (($lat === null || $lng === null) && !empty($f->boundary_coordinates)) {
                        $points = is_string($f->boundary_coordinates) ? json_decode($f->boundary_coordinates, true) : $f->boundary_coordinates;
                        if (is_array($points) && count($points) > 0) {
                            $sumLat = 0; $sumLng = 0; $cnt = 0;
                            foreach ($points as $p) {
                                if (isset($p['lat'], $p['lng'])) {
                                    $sumLat += (float) $p['lat'];
                                    $sumLng += (float) $p['lng'];
                                    $cnt++;
                                }
                            }
                            if ($cnt > 0) {
                                $lat = $sumLat / $cnt;
                                $lng = $sumLng / $cnt;
                            }
                        }
                    }

                    if ($lat === null || $lng === null) {
                        $lat = -7.2500 + ((($f->id * 7) % 19) * 0.012) - 0.08;
                        $lng = 112.7500 + ((($f->id * 11) % 17) * 0.014) - 0.06;
                    }

                    return [
                        'id' => $f->id,
                        'name' => $f->name,
                        'farmer' => $f->farmer ? ['name' => $f->farmer->name] : null,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'boundary_coordinates' => $f->boundary_coordinates,
                        'area_ha' => $f->area_ha ?? 1.5,
                    ];
                }),
        ];
    }

    /**
     * Get historical weather data
     *
     * @return array<string, mixed>
     */
    public function historyData(Request $request): array
    {
        $farmId = $request->input('farm_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $query = WeatherSnapshot::query()->with('farm.farmer');

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
        $selectedFarm = $farmId ? Farm::with('farmer')->find($farmId) : null;

        return [
            'snapshots' => $snapshots,
            'selectedFarm' => $selectedFarm,
            'farms' => Farm::with('farmer')->orderBy('name')->get(),
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
            $lat = $farm->latitude ? (float) $farm->latitude : -7.250000;
            $lng = $farm->longitude ? (float) $farm->longitude : 112.750000;

            $weatherData = $this->weatherService->getCurrentWeather(
                $lat,
                $lng,
                ['force_refresh' => true]
            );

            if (! $weatherData['success']) {
                return false;
            }

            $soilData = $this->weatherService->getSoilData($lat, $lng, ['force_refresh' => true]);

            $payload = $weatherData['data'];
            if ($soilData['success'] ?? false) {
                $payload['soil'] = $soilData['data'];
            }

            WeatherSnapshot::updateOrCreate(
                [
                    'farm_id' => $farmId,
                    'observed_at' => now(),
                ],
                [
                    'provider' => $weatherData['provider'] ?? 'system_sensor',
                    'payload_json' => $payload,
                    'expires_at' => now()->addHours(1),
                ]
            );

            // Broadcast real-time WebSocket event
            try {
                broadcast(new \App\Events\WeatherSoilUpdated($farmId, $payload))->toOthers();
            } catch (\Throwable $e) {
                // Graceful fallback if WebSocket/Reverb driver is offline
            }

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Refresh weather data for all farms
     */
    public function refreshAllFarmsWeatherData(): int
    {
        $farms = Farm::query()->get();
        $count = 0;
        foreach ($farms as $farm) {
            if ($this->refreshWeatherData($farm->id)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Export weather data
     */
    public function exportWeatherData(array $filters)
    {
        $query = WeatherSnapshot::query()->with('farm');

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
        $csv = "Farm ID,Farm Name,Provider,Observed At,Temperature,Humidity,Weather,Wind Speed,Expires At\n";

        foreach ($data as $snapshot) {
            $payload = $snapshot->payload_json;
            $csv .= sprintf(
                '"%d","%s","%s","%s","%s","%s","%s","%s","%s"',
                $snapshot->farm_id,
                $snapshot->farm?->name ?? 'N/A',
                $snapshot->provider,
                $snapshot->observed_at,
                $payload['main']['temp'] ?? 'N/A',
                $payload['main']['humidity'] ?? 'N/A',
                $payload['weather'][0]['description'] ?? 'N/A',
                $payload['wind']['speed'] ?? 'N/A',
                $snapshot->expires_at
            )."\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="weather-data.csv"',
        ]);
    }

    public function updateSettings(array $settings): bool
    {
        try {
            Cache::put('weather.provider', $settings['weather_provider'], now()->addMonth());

            if (isset($settings['weather_api_key'])) {
                Cache::put('weather.api_key', $settings['weather_api_key'], now()->addMonth());
            }

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    public function testWeatherConnection(): array
    {
        try {
            $result = $this->weatherService->getWeatherByCity('Surabaya');

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
}
