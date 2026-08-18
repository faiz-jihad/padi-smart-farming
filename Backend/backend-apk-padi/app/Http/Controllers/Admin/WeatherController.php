<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminWeatherService;
use App\Services\Weather\WeatherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeatherController extends Controller
{
    public function __construct(
        private AdminWeatherService $weatherService,
        private WeatherService $externalWeatherService
    ) {}

    /**
     * Display weather management dashboard
     */
    public function index(Request $request): View
    {
        return view('admin.weather.index', $this->weatherService->indexData($request));
    }

    /**
     * Display weather snapshots history
     */
    public function history(Request $request): View
    {
        return view('admin.weather.history', $this->weatherService->historyData($request));
    }

    /**
     * Refresh weather data for a farm
     */
    public function refresh(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'farm_id' => 'required|integer|exists:farms,id',
        ]);

        try {
            $result = $this->weatherService->refreshWeatherData($validated['farm_id']);

            if ($result) {
                return back()->with('status', 'Data cuaca berhasil diperbarui.');
            }

            return back()->with('error', 'Gagal memperbarui data cuaca.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Refresh weather data for all farms
     */
    public function refreshAll(Request $request): RedirectResponse
    {
        try {
            $count = $this->weatherService->refreshAllFarmsWeatherData();

            return back()->with('status', "Data cuaca untuk {$count} lahan berhasil diperbarui.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Export weather data
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'farm_id' => 'sometimes|integer|exists:farms,id',
            'from_date' => 'sometimes|date',
            'to_date' => 'sometimes|date',
            'format' => 'sometimes|in:csv,json',
        ]);

        return $this->weatherService->exportWeatherData($validated);
    }

    /**
     * Display weather settings
     */
    public function settings(): View
    {
        return view('admin.weather.settings', [
            'provider' => config('services.weather.provider'),
            'weatherApiKey' => config('services.weather.api_key') ? '***' : 'Belum diatur',
        ]);
    }

    /**
     * Update weather settings
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'weather_provider' => 'required|string|in:openweathermap,agromonitoring,bmkg',
            'weather_api_key' => 'nullable|string|min:10',
        ]);

        try {
            $this->weatherService->updateSettings($validated);

            return back()->with('status', 'Pengaturan cuaca berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * Test weather API connection
     */
    public function testConnection(Request $request): RedirectResponse
    {
        try {
            $result = $this->weatherService->testWeatherConnection();

            if ($result['success']) {
                return back()->with('status', 'Koneksi API cuaca berhasil: ' . $result['message']);
            }

            return back()->with('error', 'Koneksi API cuaca gagal: ' . $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Error testing connection: ' . $e->getMessage());
        }
    }

    /**
     * Clear weather cache
     */
    public function clearCache(): RedirectResponse
    {
        try {
            $this->externalWeatherService->clearCache();

            return back()->with('status', 'Cache cuaca berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus cache: ' . $e->getMessage());
        }
    }

    /**
     * Display weather map with all farms
     */
    public function map(): View
    {
        return view('admin.weather.map', [
            'farms' => [
                'data' => \App\Models\Farm::with('weatherSnapshots', 'farmer')
                    ->select('id', 'name', 'latitude', 'longitude', 'boundary_coordinates', 'area_ha', 'farmer_user_id')
                    ->get(),
            ],
        ]);
    }

    /**
     * Inspect weather and geolocation soil metrics for any picked point on map
     */
    public function inspectLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];

        $weather = $this->externalWeatherService->getCurrentWeather($lat, $lng);
        $soil = $this->externalWeatherService->getSoilData($lat, $lng);

        $parsedWeather = $weather['success'] ? $this->externalWeatherService->parseWeatherData($weather['data']) : null;

        return response()->json([
            'success' => true,
            'latitude' => $lat,
            'longitude' => $lng,
            'weather' => $parsedWeather,
            'weather_raw' => $weather['data'] ?? null,
            'soil' => $soil['success'] ? $soil['data'] : null,
            'provider' => $weather['provider'] ?? 'system_sensor',
        ]);
    }
}
