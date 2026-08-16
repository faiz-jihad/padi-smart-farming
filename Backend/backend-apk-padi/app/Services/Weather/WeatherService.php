<?php

namespace App\Services\Weather;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    protected string $provider;
    protected ?string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->provider = Cache::get('weather.provider', config('services.weather.provider', 'openweathermap'));
        $this->apiKey = Cache::get('weather.api_key', config('services.weather.api_key'));

        if ($this->provider === 'agromonitoring') {
            $this->baseUrl = config('services.weather.agromonitoring_base_url', 'https://api.agromonitoring.com/1.0');
        } else {
            $this->baseUrl = config('services.weather.base_url', 'https://api.openweathermap.org/data/2.5');
        }
    }

    /**
     * Fetch current weather for a specific location
     */
    public function getCurrentWeather(float $latitude, float $longitude, array $options = []): array
    {
        $cacheKey = "weather.current.{$this->provider}.{$latitude}.{$longitude}";

        if (Cache::has($cacheKey) && ! ($options['force_refresh'] ?? false)) {
            return Cache::get($cacheKey);
        }

        $response = $this->makeRequest('current', [
            'lat' => $latitude,
            'lon' => $longitude,
            'units' => $options['units'] ?? 'metric',
            'lang' => $options['lang'] ?? 'id',
        ]);

        if ($response['success']) {
            Cache::put($cacheKey, $response, 3600);
        }

        return $response;
    }

    /**
     * Fetch weather forecast for a location
     */
    public function getWeatherForecast(float $latitude, float $longitude, int $days = 5, array $options = []): array
    {
        $cacheKey = "weather.forecast.{$this->provider}.{$latitude}.{$longitude}.{$days}";

        if (Cache::has($cacheKey) && ! ($options['force_refresh'] ?? false)) {
            return Cache::get($cacheKey);
        }

        $response = $this->makeRequest('forecast', [
            'lat' => $latitude,
            'lon' => $longitude,
            'cnt' => $days * 8, // 8 forecasts per day (3-hourly)
            'units' => $options['units'] ?? 'metric',
            'lang' => $options['lang'] ?? 'id',
        ]);

        if ($response['success']) {
            Cache::put($cacheKey, $response, 1800);
        }

        return $response;
    }

    /**
     * Get weather by city name
     */
    public function getWeatherByCity(string $cityName, array $options = []): array
    {
        $cacheKey = "weather.city.{$this->provider}.{$cityName}";

        if (Cache::has($cacheKey) && ! ($options['force_refresh'] ?? false)) {
            return Cache::get($cacheKey);
        }

        $response = $this->makeRequest('city', [
            'q' => $cityName,
            'units' => $options['units'] ?? 'metric',
            'lang' => $options['lang'] ?? 'id',
        ]);

        if ($response['success']) {
            Cache::put($cacheKey, $response, 3600);
        }

        return $response;
    }

    /**
     * Fetch live soil data from AgroMonitoring API (/soil)
     */
    public function getSoilData(float $latitude, float $longitude, array $options = []): array
    {
        $cacheKey = "weather.soil.{$latitude}.{$longitude}";

        if (Cache::has($cacheKey) && ! ($options['force_refresh'] ?? false)) {
            return Cache::get($cacheKey);
        }

        // If provider is AgroMonitoring and API key is set
        if ($this->provider === 'agromonitoring' && ! empty($this->apiKey)) {
            try {
                $url = 'https://api.agromonitoring.com/1.0/soil';
                $response = Http::get($url, [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'appid' => $this->apiKey,
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $t10 = isset($json['t10']) ? round($json['t10'] - 273.15, 1) : null;
                    $t0 = isset($json['t0']) ? round($json['t0'] - 273.15, 1) : null;
                    $moisture = isset($json['moisture']) ? round($json['moisture'] * 100, 1) : null;

                    $res = [
                        'success' => true,
                        'provider' => 'agromonitoring',
                        'data' => [
                            'soil_temp_celsius' => $t10,
                            'surface_temp_celsius' => $t0,
                            'moisture_percentage' => $moisture,
                            'timestamp' => $json['dt'] ?? time(),
                            'raw' => $json,
                        ],
                    ];
                    Cache::put($cacheKey, $res, 3600);

                    return $res;
                }
            } catch (\Exception $e) {
                // fall through to fallback
            }
        }

        // Fallback simulation based on current weather or realistic defaults
        $currentWeather = $this->getCurrentWeather($latitude, $longitude, $options);
        $temp = $currentWeather['data']['main']['temp'] ?? 28.5;
        $humidity = $currentWeather['data']['main']['humidity'] ?? 75;

        // Soil temp is slightly cooler than air temp, moisture correlates with humidity
        $estimatedSoilTemp = round($temp - 1.5, 1);
        $estimatedMoisture = round(min(85, max(30, $humidity * 0.65)), 1);

        $res = [
            'success' => true,
            'provider' => 'simulated',
            'data' => [
                'soil_temp_celsius' => $estimatedSoilTemp,
                'surface_temp_celsius' => $temp,
                'moisture_percentage' => $estimatedMoisture,
                'timestamp' => time(),
                'raw' => null,
            ],
        ];

        Cache::put($cacheKey, $res, 1800);

        return $res;
    }

    /**
     * Make HTTP request to weather API
     */
    protected function makeRequest(string $endpoint, array $params = []): array
    {
        try {
            if (empty($this->apiKey)) {
                return [
                    'success' => false,
                    'error' => 'WEATHER_API_KEY tidak dikonfigurasi. Silakan isi di menu Pengaturan Cuaca.',
                    'provider' => $this->provider,
                ];
            }

            $url = $this->buildUrl($endpoint);
            $params['appid'] = $this->apiKey;

            $response = Http::timeout((int) config('services.weather.timeout', 10))
                ->get($url, $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'provider' => $this->provider,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'API cuaca merespons HTTP ' . $response->status() . ': ' . ($response->json('message') ?? $response->body()),
                'status' => $response->status(),
                'provider' => $this->provider,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Koneksi ke API cuaca gagal: ' . $e->getMessage(),
                'provider' => $this->provider,
            ];
        }
    }

    /**
     * Build API URL based on endpoint
     */
    protected function buildUrl(string $endpoint): string
    {
        $endpoints = [
            'current' => $this->baseUrl . '/weather',
            'forecast' => $this->baseUrl . '/forecast',
            'city' => $this->baseUrl . '/weather',
        ];

        return $endpoints[$endpoint] ?? $this->baseUrl . '/weather';
    }

    /**
     * Parse weather data to standardized format
     */
    public function parseWeatherData(array $rawData): array
    {
        return [
            'temperature' => $rawData['main']['temp'] ?? null,
            'feels_like' => $rawData['main']['feels_like'] ?? null,
            'temp_min' => $rawData['main']['temp_min'] ?? null,
            'temp_max' => $rawData['main']['temp_max'] ?? null,
            'pressure' => $rawData['main']['pressure'] ?? null,
            'humidity' => $rawData['main']['humidity'] ?? null,
            'weather' => $rawData['weather'][0]['main'] ?? null,
            'description' => $rawData['weather'][0]['description'] ?? null,
            'wind_speed' => $rawData['wind']['speed'] ?? null,
            'wind_deg' => $rawData['wind']['deg'] ?? null,
            'clouds' => $rawData['clouds']['all'] ?? null,
            'rain' => $rawData['rain']['1h'] ?? 0,
            'visibility' => $rawData['visibility'] ?? null,
            'uvi' => $rawData['uvi'] ?? null,
            'timestamp' => $rawData['dt'] ?? null,
        ];
    }

    /**
     * Clear weather cache
     */
    public function clearCache(): bool
    {
        try {
            if (Cache::supportsTags()) {
                Cache::tags(['weather'])->flush();
            } else {
                Cache::flush();
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
