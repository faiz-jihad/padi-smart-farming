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
                return back()->with('status', 'Data cuaca berhasil diperbarui');
            }

            return back()->with('error', 'Gagal memperbarui data cuaca');
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
            'weatherApiKey' => config('services.weather.api_key') ? '***' : 'Not set',
        ]);
    }

    /**
     * Update weather settings
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'weather_provider' => 'required|string|in:openweathermap',
            'weather_api_key' => 'sometimes|string|min:10',
        ]);

        try {
            $this->weatherService->updateSettings($validated);
            return back()->with('status', 'Pengaturan cuaca berhasil diperbarui');
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
            return back()->with('status', 'Cache cuaca berhasil dihapus');
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
                    ->select('id', 'name', 'latitude', 'longitude', 'farmer_id')
                    ->get(),
            ],
        ]);
    }
}
