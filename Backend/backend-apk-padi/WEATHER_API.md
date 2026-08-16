# Weather API Implementation

## Ringkasan

Telah ditambahkan sistem Weather API lengkap ke backend untuk mengintegrasikan data cuaca dari pihak ketiga (OpenWeatherMap) dan menyediakan akses ke admin panel.

## Komponen yang Ditambahkan

### 1. Services

**File:** `app/Services/Weather/WeatherService.php`

- Menghandle integrasi dengan API cuaca eksternal (OpenWeatherMap)
- Fungsi utama:
    - `getCurrentWeather()` - Ambil cuaca saat ini berdasarkan lat/long
    - `getWeatherForecast()` - Ambil prakiraan cuaca 5 hari
    - `getWeatherByCity()` - Ambil cuaca berdasarkan nama kota
    - `parseWeatherData()` - Normalisasi data cuaca
    - `clearCache()` - Hapus cache cuaca
- Menggunakan Laravel HTTP client dan caching

**File:** `app/Services/Admin/AdminWeatherService.php`

- Mengelola operasi cuaca di admin panel
- Fungsi utama:
    - `indexData()` - Data untuk dashboard cuaca
    - `historyData()` - Riwayat data cuaca dengan filter
    - `refreshWeatherData()` - Perbarui data cuaca farm
    - `exportWeatherData()` - Export ke CSV/JSON
    - `updateSettings()` - Update pengaturan API
    - `testWeatherConnection()` - Test koneksi API

### 2. Controllers

**API Controllers:**

- **File:** `app/Http/Controllers/Api/V1/WeatherController.php`
- Endpoint:
    - `POST /api/v1/weather/current` - Ambil cuaca saat ini
    - `POST /api/v1/weather/forecast` - Ambil prakiraan cuaca
    - `GET /api/v1/weather/history` - Riwayat cuaca
    - `POST /api/v1/weather/city` - Cuaca berdasarkan kota

**Admin Controllers:**

- **File:** `app/Http/Controllers/Admin/WeatherController.php`
- Routes:
    - `GET /admin/weather` - Dashboard cuaca
    - `GET /admin/weather/history` - Riwayat cuaca
    - `POST /admin/weather/refresh` - Perbarui data
    - `POST /admin/weather/export` - Export data
    - `GET /admin/weather/settings` - Pengaturan
    - `PATCH /admin/weather/settings` - Update pengaturan
    - `POST /admin/weather/test-connection` - Test koneksi
    - `POST /admin/weather/clear-cache` - Hapus cache

### 3. Form Requests

**File:** `app/Http/Requests/Api/V1/GetWeatherRequest.php`

- Validasi request cuaca
- Rules:
    - `farm_id` - Required, integer, exists di farms table
    - `units` - Optional, metric atau imperial
    - `lang` - Optional, bahasa (default: id)
    - `force_refresh` - Optional, boolean

### 4. Configuration

**File:** `config/services.php`

- Tambahan konfigurasi:
    ```php
    'weather' => [
        'provider' => env('WEATHER_PROVIDER', 'openweathermap'),
        'api_key' => env('WEATHER_API_KEY'),
        'base_url' => env('WEATHER_BASE_URL', 'https://api.openweathermap.org/data/2.5'),
        'timeout' => env('WEATHER_TIMEOUT', 10),
    ],
    ```

### 5. Routes

**API Routes (routes/api.php):**

```php
Route::prefix('weather')->group(function (): void {
    Route::post('current', [WeatherController::class, 'currentWeather']);
    Route::post('forecast', [WeatherController::class, 'forecast']);
    Route::get('history', [WeatherController::class, 'history']);
    Route::post('city', [WeatherController::class, 'byCity']);
});
```

**Web Routes (routes/web.php):**

```php
Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');
Route::get('/weather/history', [WeatherController::class, 'history'])->name('weather.history');
Route::post('/weather/refresh', [WeatherController::class, 'refresh'])->name('weather.refresh');
Route::post('/weather/export', [WeatherController::class, 'export'])->name('weather.export');
Route::get('/weather/settings', [WeatherController::class, 'settings'])->name('weather.settings');
Route::patch('/weather/settings', [WeatherController::class, 'updateSettings'])->name('weather.settings.update');
Route::post('/weather/test-connection', [WeatherController::class, 'testConnection'])->name('weather.test-connection');
Route::post('/weather/clear-cache', [WeatherController::class, 'clearCache'])->name('weather.clear-cache');
```

## Setup Environment

Tambahkan variabel berikut ke `.env`:

```env
WEATHER_PROVIDER=openweathermap
WEATHER_API_KEY=your_openweathermap_api_key
WEATHER_BASE_URL=https://api.openweathermap.org/data/2.5
WEATHER_TIMEOUT=10
```

Dapatkan API key dari: https://openweathermap.org/api

## Database

Sistem menggunakan model `WeatherSnapshot` yang sudah ada dengan struktur:

- `farm_id` - Foreign key ke farms
- `provider` - Penyedia API (openweathermap)
- `observed_at` - Waktu observasi
- `payload_json` - Data JSON lengkap dari API
- `expires_at` - Waktu kadaluarsa data

## Usage

### API Usage (Mobile/Client)

```bash
# Ambil cuaca saat ini
POST /api/v1/weather/current
{
    "farm_id": 1,
    "units": "metric",
    "lang": "id"
}

# Ambil prakiraan
POST /api/v1/weather/forecast
{
    "farm_id": 1,
    "units": "metric"
}

# Riwayat cuaca
GET /api/v1/weather/history?farm_id=1&limit=30

# Cuaca berdasarkan kota
POST /api/v1/weather/city
{
    "city": "Jakarta",
    "units": "metric"
}
```

### Admin Panel

- Dashboard: `/admin/weather` - Melihat data cuaca terkini
- History: `/admin/weather/history` - Riwayat dengan filter
- Settings: `/admin/weather/settings` - Konfigurasi API
- Export: POST ke `/admin/weather/export` - Export CSV/JSON
- Refresh: POST ke `/admin/weather/refresh` - Update data manual
- Test Connection: POST ke `/admin/weather/test-connection` - Tes koneksi API
- Clear Cache: POST ke `/admin/weather/clear-cache` - Bersihkan cache

## Features

✅ Integrasi dengan OpenWeatherMap API
✅ Caching otomatis (1 jam untuk current, 30 menit untuk forecast)
✅ Penyimpanan data cuaca ke database
✅ Admin panel dashboard dengan statistik
✅ Riwayat cuaca dengan filter tanggal dan farm
✅ Export data ke CSV/JSON
✅ Tes koneksi API
✅ Validasi form request
✅ Error handling yang proper
✅ Support untuk multiple units (metric/imperial)
✅ Support untuk berbagai bahasa

## Next Steps

1. Buat Blade views untuk admin panel:
    - `resources/views/admin/weather/index.blade.php`
    - `resources/views/admin/weather/history.blade.php`
    - `resources/views/admin/weather/settings.blade.php`

2. Tambahkan migration jika diperlukan kolom tambahan di WeatherSnapshot

3. Integrasikan dengan sistem notifikasi/early warning untuk alert cuaca ekstrem

4. Tambahkan scheduled job untuk refresh otomatis data cuaca
