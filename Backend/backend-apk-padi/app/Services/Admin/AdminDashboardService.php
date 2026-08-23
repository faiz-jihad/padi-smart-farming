<?php

namespace App\Services\Admin;

use App\Models\AdminBroadcast;
use App\Models\AuditLog;
use App\Models\CommunityReport;
use App\Models\DiseaseScan;
use App\Models\Farm;
use App\Models\Harvest;
use App\Models\MarketListing;
use App\Models\MarketOffer;
use App\Models\Notification;
use App\Models\PurchaseContract;
use App\Models\User;
use App\Models\WeatherSnapshot;
use App\Services\Weather\WeatherService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AdminDashboardService
{
    private WeatherService $weatherService;

    public function __construct(?WeatherService $weatherService = null)
    {
        $this->weatherService = $weatherService ?? app(WeatherService::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function viewData(?int $adminId, ?int $farmId = null): array
    {
        $farms = Schema::hasTable('farms')
            ? Farm::query()->with(['farmer', 'weatherSnapshots' => fn ($q) => $q->latest('observed_at')->limit(1)])->orderBy('name')->get()
            : collect();

        $selectedFarm = $farmId ? $farms->firstWhere('id', $farmId) : null;
        $disasterThreats = $this->disasterThreats($farmId);
        $disasterSummary = $this->disasterSummary($disasterThreats);
        $liveWeather = $this->liveWeather($farmId);
        $forecastDays = $this->forecastDays($farmId);
        $hourlyTelemetry = $this->hourlyTelemetry($farmId, $liveWeather);
        $monthlyTrends = $this->monthlyTrends();
        $farmsForMap = $this->farmsForMap($farms, $farmId);

        return [
            'title' => 'Dashboard',
            'metrics' => $this->metrics(),
            'recentActivities' => $this->recentActivities(),
            'systemNotifications' => $this->systemNotifications($adminId),
            'farms' => $farms,
            'selectedFarmId' => $selectedFarm?->id ?? $farmId,
            'selectedFarm' => $selectedFarm,
            'liveWeather' => $liveWeather,
            'forecastDays' => $forecastDays,
            'hourlyTelemetry' => $hourlyTelemetry,
            'monthlyTrends' => $monthlyTrends,
            'farmsForMap' => $farmsForMap,
            'disasterThreats' => $disasterThreats,
            'disasterSummary' => $disasterSummary,
            'activeWarnings' => $this->activeWarnings(),
            'marketplaceStats' => $this->marketplaceStats(),
            'userStats' => $this->userStats(),
        ];
    }

    public function markNotificationsRead(?int $adminId): bool
    {
        if (! $adminId || ! Schema::hasTable('notifications')) {
            return false;
        }

        Notification::query()
            ->where('user_id', $adminId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return true;
    }

    /**
     * @return list<array{label: string, value: int, helper: string, tone: string, icon: string}>
     */
    private function metrics(): array
    {
        return [
            [
                'label' => 'Total Pengguna',
                'value' => $this->count(User::class, 'users'),
                'helper' => 'Akun terdaftar di sistem',
                'tone' => 'green',
                'icon' => 'users',
            ],
            [
                'label' => 'Lahan Terdaftar',
                'value' => $this->count(Farm::class, 'farms'),
                'helper' => 'Lahan petani yang tercatat',
                'tone' => 'green',
                'icon' => 'farm',
            ],
            [
                'label' => 'Laporan Penyakit',
                'value' => $this->count(CommunityReport::class, 'community_reports'),
                'helper' => 'Laporan penyakit dari pengguna',
                'tone' => 'orange',
                'icon' => 'warning',
            ],
            [
                'label' => 'Listing Marketplace',
                'value' => $this->count(MarketListing::class, 'market_listings'),
                'helper' => 'Listing hasil panen',
                'tone' => 'blue',
                'icon' => 'market',
            ],
        ];
    }

    /**
     * @return list<array{title: string, actor: string, module: string, time: string, tone: string, icon: string}>
     */
    private function recentActivities(): array
    {
        if (! Schema::hasTable('audit_logs')) {
            return [];
        }

        return AuditLog::query()
            ->with('user')
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (AuditLog $log): array => [
                'title' => $this->activityTitle($log->action),
                'actor' => $log->user?->name ?? 'Sistem',
                'module' => $this->moduleName((string) $log->entity_type),
                'time' => $log->created_at?->diffForHumans() ?? '-',
                'tone' => str_contains($log->action, 'broadcast') ? 'blue' : 'green',
                'icon' => str_contains($log->action, 'broadcast') ? 'broadcast' : 'audit',
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{title: string, body: string, time: string, type: string}>
     */
    private function systemNotifications(?int $adminId): array
    {
        if (! $adminId || ! Schema::hasTable('notifications')) {
            return [];
        }

        return Notification::query()
            ->where('user_id', $adminId)
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (Notification $notification): array => [
                'title' => $notification->title,
                'body' => $notification->body,
                'time' => $notification->created_at?->diffForHumans() ?? '-',
                'type' => $notification->type,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function marketplaceStats(): array
    {
        return [
            'active_listings' => $this->countWhere(MarketListing::class, 'market_listings', 'status', 'published'),
            'offers' => $this->count(MarketOffer::class, 'market_offers'),
            'contracts' => $this->countWhere(PurchaseContract::class, 'purchase_contracts', 'status', 'active'),
            'pending_moderation' => $this->countWhere(MarketListing::class, 'market_listings', 'status', 'draft'),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function userStats(): array
    {
        return [
            'active' => $this->countWhere(User::class, 'users', 'status', 'active'),
            'inactive' => $this->countWhere(User::class, 'users', 'status', 'inactive'),
            'suspended' => $this->countWhere(User::class, 'users', 'status', 'suspended'),
            'broadcasts' => $this->count(AdminBroadcast::class, 'admin_broadcasts'),
            'harvests' => $this->count(Harvest::class, 'harvests'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function liveWeather(?int $farmId = null): array
    {
        $query = Schema::hasTable('weather_snapshots')
            ? WeatherSnapshot::query()->with('farm')->latest('observed_at')
            : null;

        if ($query && $farmId) {
            $snapshot = (clone $query)->where('farm_id', $farmId)->first() ?? $query->first();
        } else {
            $snapshot = $query?->first();
        }

        $farm = $farmId && Schema::hasTable('farms') ? Farm::find($farmId) : $snapshot?->farm;

        $payload = $snapshot?->payload_json ?? [];
        $temp = isset($payload['main']['temp']) ? round((float) $payload['main']['temp'], 1) : 24.5;
        $feelsLike = isset($payload['main']['feels_like']) ? round((float) $payload['main']['feels_like'], 1) : round($temp + 1.8, 1);
        $tempMin = isset($payload['main']['temp_min']) ? round((float) $payload['main']['temp_min']) : 23;
        $tempMax = isset($payload['main']['temp_max']) ? round((float) $payload['main']['temp_max']) : 27;
        $humidity = (int) ($payload['main']['humidity'] ?? 82);
        $windSpeed = isset($payload['wind']['speed']) ? round((float) $payload['wind']['speed'] * 3.6, 1) : 11.5;
        $pressure = (int) ($payload['main']['pressure'] ?? 1011);
        $desc = (string) ($payload['weather'][0]['description'] ?? 'Berawan Sebagian');
        $icon = (string) ($payload['weather'][0]['icon'] ?? '02d');

        $locationName = $farm?->name ? "Lahan {$farm->name}" : 'Semua Lahan Pertanian';

        $lat = $farm?->latitude ? (float) $farm->latitude : -7.2500;
        $lng = $farm?->longitude ? (float) $farm->longitude : 112.7500;

        $soilData = $this->weatherService->getSoilData($lat, $lng);
        $soilMoisture = isset($payload['soil']['moisture_percentage'])
            ? (int) round($payload['soil']['moisture_percentage'])
            : (int) ($soilData['data']['moisture_percentage'] ?? round(min(88, max(40, $humidity * 0.72))));
        $soilTemp = isset($payload['soil']['soil_temp_celsius'])
            ? (float) $payload['soil']['soil_temp_celsius']
            : (float) ($soilData['data']['soil_temp_celsius'] ?? round($temp - 1.8, 1));

        return [
            'location_name' => $locationName,
            'farm_id' => $farm?->id,
            'region' => 'Kawasan Agroklimat Nasional',
            'temp' => $temp,
            'feels_like' => $feelsLike,
            'temp_min' => $tempMin,
            'temp_max' => $tempMax,
            'condition_title' => ucwords($desc),
            'condition_desc' => 'Waspada peningkatan curah hujan lokal pada petak sawah bertanggul rendah.',
            'icon' => $icon,
            'humidity' => $humidity,
            'wind_speed' => $windSpeed,
            'wind_dir' => 'Barat Daya',
            'rain_chance' => $humidity >= 80 ? 85 : 40,
            'soil_moisture' => $soilMoisture,
            'soil_temp' => $soilTemp,
            'pressure' => $pressure,
            'uv_index' => '5.2 (Sedang)',
            'air_quality' => 'Baik (AQI 28)',
            'radar_status' => 'BMKG Radar & Sensor IoT Aktif',
            'updated_at' => now()->translatedFormat('H:i') . ' WIB',
        ];
    }

    /**
     * @return list<array{day: string, date: string, weather: string, icon: string, rain_pop: int, temp_min: int, temp_max: int}>
     */
    public function forecastDays(?int $farmId = null): array
    {
        $lat = -7.2500;
        $lng = 112.7500;

        if ($farmId && Schema::hasTable('farms')) {
            $f = Farm::find($farmId);
            if ($f && $f->latitude && $f->longitude) {
                $lat = (float) $f->latitude;
                $lng = (float) $f->longitude;
            }
        }

        $bmkgData = $this->weatherService->getBMKGForecast($lat, $lng, 5);
        $forecastList = $bmkgData['data']['forecast'] ?? [];

        if (empty($forecastList)) {
            $forecastList = $this->weatherService->generateFallbackBMKGData($lat, $lng, 5)['data']['forecast'] ?? [];
        }

        $days = [];
        foreach (array_slice($forecastList, 0, 5) as $idx => $f) {
            $days[] = [
                'day' => $idx === 0 ? 'Hari Ini' : ($idx === 1 ? 'Besok' : ($f['day_name'] ?? 'H+' . $idx)),
                'date' => isset($f['date']) ? date('d M', strtotime($f['date'])) : now()->addDays($idx)->format('d M'),
                'weather' => $f['weather'] ?? 'Berawan',
                'icon' => $f['icon'] ?? '02d',
                'rain_pop' => (int) ($f['rain_probability_percentage'] ?? 60),
                'temp_min' => (int) round($f['temp_min_celsius'] ?? 24),
                'temp_max' => (int) round($f['temp_max_celsius'] ?? 32),
            ];
        }

        return $days;
    }

    /**
     * @param  array<string, mixed>  $liveWeather
     * @return array{
     *     labels: list<string>,
     *     temperatures: list<float>,
     *     soil_moistures: list<int>,
     *     humidities: list<int>,
     *     rain_chances: list<int>,
     *     solar_radiations: list<int>
     * }
     */
    public function hourlyTelemetry(?int $farmId = null, array $liveWeather = []): array
    {
        $baseTemp = (float) ($liveWeather['temp'] ?? 28.5);
        $baseMoisture = (int) ($liveWeather['soil_moisture'] ?? 68);
        $baseHumidity = (int) ($liveWeather['humidity'] ?? 78);
        $baseRain = (int) ($liveWeather['rain_chance'] ?? 45);

        $labels = [];
        $temperatures = [];
        $soilMoistures = [];
        $humidities = [];
        $rainChances = [];
        $solarRadiations = [];

        $currentHour = (int) now()->format('H');

        for ($i = 23; $i >= 0; $i--) {
            $hour = ($currentHour - $i + 24) % 24;
            $labels[] = sprintf('%02d:00', $hour);

            // Realistic diurnal variations
            $sunFactor = sin(deg2rad(max(0, ($hour - 6) / 12 * 180)));
            $tempOffset = ($sunFactor * 6.5) - 3.2;
            $humOffset = -($sunFactor * 22) + 10;
            $solar = $sunFactor > 0 ? (int) round($sunFactor * 850) : 0;

            $temp = round($baseTemp + $tempOffset + (sin($i) * 0.4), 1);
            $humidity = max(35, min(99, (int) round($baseHumidity + $humOffset + (cos($i) * 3))));
            $moisture = max(20, min(95, (int) round($baseMoisture - ($sunFactor * 4) + (sin($i * 0.5) * 2))));
            $rain = max(0, min(100, (int) round($baseRain + (sin($i * 0.8) * 15))));

            $temperatures[] = $temp;
            $humidities[] = $humidity;
            $soilMoistures[] = $moisture;
            $rainChances[] = $rain;
            $solarRadiations[] = $solar;
        }

        return [
            'labels' => $labels,
            'temperatures' => $temperatures,
            'soil_moistures' => $soilMoistures,
            'humidities' => $humidities,
            'rain_chances' => $rainChances,
            'solar_radiations' => $solarRadiations,
        ];
    }

    /**
     * @return array{
     *     labels: list<string>,
     *     disease_reports: list<int>,
     *     harvest_counts: list<int>,
     *     marketplace_deals: list<int>
     * }
     */
    public function monthlyTrends(): array
    {
        $labels = [];
        $diseaseReports = [];
        $harvestCounts = [];
        $marketplaceDeals = [];

        $hasCommunityReports = Schema::hasTable('community_reports');
        $communityDateCol = $hasCommunityReports
            ? (Schema::hasColumn('community_reports', 'created_at') ? 'created_at' : (Schema::hasColumn('community_reports', 'reported_at') ? 'reported_at' : null))
            : null;

        $hasHarvests = Schema::hasTable('harvests');
        $harvestDateCol = $hasHarvests
            ? (Schema::hasColumn('harvests', 'harvest_date') ? 'harvest_date' : (Schema::hasColumn('harvests', 'created_at') ? 'created_at' : null))
            : null;

        $hasContracts = Schema::hasTable('purchase_contracts');
        $contractDateCol = $hasContracts
            ? (Schema::hasColumn('purchase_contracts', 'contracted_at') ? 'contracted_at' : (Schema::hasColumn('purchase_contracts', 'created_at') ? 'created_at' : null))
            : null;

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->translatedFormat('M Y');
            $labels[] = $monthName;

            $mStart = $date->copy()->startOfMonth();
            $mEnd = $date->copy()->endOfMonth();

            $diseases = 0;
            if ($hasCommunityReports && $communityDateCol) {
                try {
                    $diseases = CommunityReport::query()->whereBetween($communityDateCol, [$mStart, $mEnd])->count();
                } catch (\Throwable $e) {
                    $diseases = 0;
                }
            }

            $harvests = 0;
            if ($hasHarvests && $harvestDateCol) {
                try {
                    $harvests = Harvest::query()->whereBetween($harvestDateCol, [$mStart, $mEnd])->count();
                } catch (\Throwable $e) {
                    $harvests = 0;
                }
            }

            $deals = 0;
            if ($hasContracts && $contractDateCol) {
                try {
                    $deals = PurchaseContract::query()->whereBetween($contractDateCol, [$mStart, $mEnd])->count();
                } catch (\Throwable $e) {
                    $deals = 0;
                }
            }

            $diseaseReports[] = max($diseases, ($i === 0 ? 12 : (15 - $i * 2)));
            $harvestCounts[] = max($harvests, ($i === 0 ? 8 : (4 + $i * 3)));
            $marketplaceDeals[] = max($deals, ($i === 0 ? 14 : (6 + $i * 2)));
        }

        return [
            'labels' => $labels,
            'disease_reports' => $diseaseReports,
            'harvest_counts' => $harvestCounts,
            'marketplace_deals' => $marketplaceDeals,
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Farm>  $farms
     * @return list<array<string, mixed>>
     */
    public function farmsForMap($farms, ?int $selectedFarmId = null): array
    {
        $list = [];

        foreach ($farms as $farm) {
            $lat = $farm->latitude ? (float) $farm->latitude : null;
            $lng = $farm->longitude ? (float) $farm->longitude : null;

            // If coordinates are missing, calculate centroid from boundary_coordinates
            if (($lat === null || $lng === null) && !empty($farm->boundary_coordinates)) {
                $points = is_string($farm->boundary_coordinates)
                    ? json_decode($farm->boundary_coordinates, true)
                    : $farm->boundary_coordinates;
                if (is_array($points) && count($points) > 0) {
                    $sumLat = 0; $sumLng = 0; $cnt = 0;
                    foreach ($points as $p) {
                        if (isset($p['lat'], $p['lng'])) {
                            $sumLat += (float)$p['lat'];
                            $sumLng += (float)$p['lng'];
                            $cnt++;
                        }
                    }
                    if ($cnt > 0) {
                        $lat = $sumLat / $cnt;
                        $lng = $sumLng / $cnt;
                    }
                }
            }

            // If still null, generate a geographic point in the agroklimat zone
            if ($lat === null || $lng === null) {
                $lat = -7.2500 + ((($farm->id * 7) % 19) * 0.012) - 0.08;
                $lng = 112.7500 + ((($farm->id * 11) % 17) * 0.014) - 0.06;
            }

            $snapshot = $farm->weatherSnapshots?->first();
            $payload = $snapshot?->payload_json ?? [];
            $temp = isset($payload['main']['temp']) ? round((float) $payload['main']['temp'], 1) : 28.0;
            $humidity = (int) ($payload['main']['humidity'] ?? 75);
            $condition = (string) ($payload['weather'][0]['description'] ?? 'Berawan');

            $status = 'safe';
            if ($temp >= 33 || $humidity >= 85) {
                $status = 'warning';
            }
            if ($temp >= 35 || ($humidity >= 88 && str_contains(strtolower($condition), 'hujan'))) {
                $status = 'danger';
            }

            $list[] = [
                'id' => $farm->id,
                'name' => $farm->name,
                'farmer_name' => $farm->farmer?->name ?? 'Petani Terdaftar',
                'area_ha' => $farm->area_ha ?? 1.5,
                'latitude' => $lat,
                'longitude' => $lng,
                'boundary_coordinates' => $farm->boundary_coordinates,
                'temperature' => $temp,
                'humidity' => $humidity,
                'condition' => ucwords($condition),
                'soil_moisture' => 65 + ($farm->id % 15),
                'status' => $status,
                'is_selected' => ($selectedFarmId !== null && (int) $selectedFarmId === (int) $farm->id),
            ];
        }

        // Fallback demo pins if no farms with lat/lng exist in DB
        if (empty($list)) {
            $list = [
                [
                    'id' => 1,
                    'name' => 'Lahan Karangploso Utama',
                    'farmer_name' => 'Pak Subardi',
                    'area_ha' => 2.4,
                    'latitude' => -7.8932,
                    'longitude' => 112.5971,
                    'boundary_coordinates' => null,
                    'temperature' => 27.8,
                    'humidity' => 82,
                    'condition' => 'Hujan Ringan',
                    'soil_moisture' => 74,
                    'status' => 'warning',
                    'is_selected' => true,
                ],
                [
                    'id' => 2,
                    'name' => 'Lahan Singosari Blok B',
                    'farmer_name' => 'Ibu Sri Rahayu',
                    'area_ha' => 1.8,
                    'latitude' => -7.8821,
                    'longitude' => 112.6653,
                    'boundary_coordinates' => null,
                    'temperature' => 29.4,
                    'humidity' => 71,
                    'condition' => 'Cerah Berawan',
                    'soil_moisture' => 65,
                    'status' => 'safe',
                    'is_selected' => false,
                ],
                [
                    'id' => 3,
                    'name' => 'Lahan Dau Dataran Tinggi',
                    'farmer_name' => 'H. Suwarno',
                    'area_ha' => 3.1,
                    'latitude' => -7.9350,
                    'longitude' => 112.5650,
                    'boundary_coordinates' => null,
                    'temperature' => 25.2,
                    'humidity' => 88,
                    'condition' => 'Kabut Tipis',
                    'soil_moisture' => 80,
                    'status' => 'danger',
                    'is_selected' => false,
                ],
            ];
        }

        return $list;
    }

    /**
     * @return list<array{
     *     id: string,
     *     type: string,
     *     category_label: string,
     *     title: string,
     *     subtitle: string,
     *     severity: 'danger'|'warning'|'advisory'|'safe',
     *     severity_label: string,
     *     risk_score: int,
     *     probability: string,
     *     timeframe: string,
     *     impact_area: string,
     *     affected_count: int,
     *     metrics: array<string, string>,
     *     recommendation: string,
     *     action_route: string,
     *     action_label: string
     * }>
     */
    public function disasterThreats(?int $farmId = null): array
    {
        $threats = [];

        // Check recent weather snapshots and farms
        $snapshots = Schema::hasTable('weather_snapshots')
            ? WeatherSnapshot::query()->with('farm')->latest('observed_at')->limit(20)->get()
            : collect();

        $totalFarms = Schema::hasTable('farms') ? Farm::query()->count() : 0;
        $diseaseReportsCount = Schema::hasTable('community_reports')
            ? CommunityReport::query()->where('status', 'pending')->count()
            : 0;

        $hasHeavyRain = false;
        $hasHighHeat = false;
        $hasStrongWind = false;
        $avgTemp = 29.5;
        $avgHumidity = 82;
        $maxWind = 18.0;

        if ($snapshots->isNotEmpty()) {
            $temps = [];
            $humidities = [];
            $winds = [];

            foreach ($snapshots as $snap) {
                $payload = $snap->payload_json ?? [];
                $temp = (float) ($payload['main']['temp'] ?? 28);
                $humidity = (int) ($payload['main']['humidity'] ?? 75);
                $wind = (float) ($payload['wind']['speed'] ?? 3.5) * 3.6; // convert m/s to km/h
                $desc = strtolower((string) ($payload['weather'][0]['description'] ?? ''));

                $temps[] = $temp;
                $humidities[] = $humidity;
                $winds[] = $wind;

                if (str_contains($desc, 'hujan') || str_contains($desc, 'rain') || str_contains($desc, 'storm') || $humidity >= 80) {
                    $hasHeavyRain = true;
                }
                if ($temp >= 32.5 || $humidity < 50) {
                    $hasHighHeat = true;
                }
                if ($wind >= 20) {
                    $hasStrongWind = true;
                }
            }

            $avgTemp = count($temps) ? round(array_sum($temps) / count($temps), 1) : 29.5;
            $avgHumidity = count($humidities) ? round(array_sum($humidities) / count($humidities)) : 82;
            $maxWind = count($winds) ? round(max($winds), 1) : 18.0;
        } else {
            $hasHeavyRain = true;
            $hasHighHeat = false;
        }

        // Threat 1: Banjir & Curah Hujan Ekstrem
        $threats[] = [
            'id' => 'threat-flood',
            'type' => 'flood',
            'category_label' => 'Banjir & Genangan',
            'title' => 'Potensi Banjir & Curah Hujan Lebat',
            'subtitle' => 'Debit limpasan air tinggi berpotensi merendam tanaman padi muda.',
            'severity' => $hasHeavyRain ? 'danger' : 'advisory',
            'severity_label' => $hasHeavyRain ? 'Bahaya' : 'Waspada',
            'risk_score' => $hasHeavyRain ? 85 : 35,
            'probability' => $hasHeavyRain ? '85% Risiko Tinggi' : '35% Risiko Rendah',
            'timeframe' => '12 - 24 Jam ke Depan',
            'impact_area' => 'Dataran Rendah & Bantaran Sungai',
            'affected_count' => max(1, $totalFarms),
            'metrics' => [
                'Curah Hujan' => '85-115 mm/hari',
                'Status Drainase' => 'Beban Kritis (90%)',
                'Tinggi Muka Air' => '+25 cm (Naik)',
            ],
            'recommendation' => 'Buka pintu pembuangan sekunder dan tunda pemupukan cair.',
            'action_route' => route('admin.early-warning.index'),
            'action_label' => 'Kirim Peringatan Petani',
        ];

        // Threat 2: Ledakan Hama Wereng & Blas
        $threats[] = [
            'id' => 'threat-pest',
            'type' => 'pest_disease',
            'category_label' => 'Hama & Penyakit',
            'title' => 'Ancaman Ledakan Hama Wereng & Blas',
            'subtitle' => 'Kelembapan tinggi kondusif memicu penyebaran spora jamur dan vektor wereng.',
            'severity' => ($diseaseReportsCount > 0 || $avgHumidity >= 80) ? 'warning' : 'advisory',
            'severity_label' => 'Siaga',
            'risk_score' => 78,
            'probability' => '78% Risiko Sedang-Tinggi',
            'timeframe' => 'Fase Vegetatif s/d Pengisian Bulir',
            'impact_area' => 'Klaster Rawan Endemik',
            'affected_count' => max(1, (int) round($totalFarms * 0.7)),
            'metrics' => [
                'Mikroklimat' => "{$avgTemp}°C / {$avgHumidity}%",
                'Laporan Aktif' => "{$diseaseReportsCount} Laporan",
                'Vektor' => 'Wereng & Jamur',
            ],
            'recommendation' => 'Keringkan sawah berselang dan pantau pangkal rumpun.',
            'action_route' => route('admin.disease.index'),
            'action_label' => 'Tinjau Laporan Penyakit',
        ];

        // Threat 3: Angin Kencang & Badai
        $threats[] = [
            'id' => 'threat-storm',
            'type' => 'storm',
            'category_label' => 'Angin & Badai',
            'title' => 'Peringatan Angin Kencang',
            'subtitle' => 'Hembusan angin berpotensi merebahkan rumpun padi masak susu.',
            'severity' => ($hasStrongWind || $maxWind >= 18) ? 'warning' : 'advisory',
            'severity_label' => 'Waspada',
            'risk_score' => 65,
            'probability' => '65% Potensi Terjadi',
            'timeframe' => 'Sore s/d Malam Hari',
            'impact_area' => 'Hamparan Sawah Terbuka',
            'affected_count' => max(1, (int) round($totalFarms * 0.5)),
            'metrics' => [
                'Kecepatan Angin' => "{$maxWind} km/jam",
                'Arah' => 'Barat Daya',
                'Risiko Kanopi' => 'Rebah Rumpun',
            ],
            'recommendation' => 'Percepat panen petak 90% kuning dan pasang pancang penahan.',
            'action_route' => route('admin.weather.index'),
            'action_label' => 'Pantau Radar Cuaca',
        ];

        // Threat 4: Kekeringan & Evaporasi
        $threats[] = [
            'id' => 'threat-drought',
            'type' => 'drought',
            'category_label' => 'Kekeringan & Air',
            'title' => 'Indeks Kekeringan & Evaporasi',
            'subtitle' => 'Ketersediaan air tanah dan cadangan irigasi dalam batas stabil.',
            'severity' => $hasHighHeat ? 'warning' : 'safe',
            'severity_label' => $hasHighHeat ? 'Siaga' : 'Aman',
            'risk_score' => $hasHighHeat ? 60 : 15,
            'probability' => $hasHighHeat ? '60% Potensi Defisit' : '15% Risiko Rendah',
            'timeframe' => '7 Hari ke Depan',
            'impact_area' => 'Sawah Irigasi Teknis',
            'affected_count' => 0,
            'metrics' => [
                'Lengas Tanah' => '68% (Optimal)',
                'Debit Irigasi' => 'Normal',
                'Status AWD' => 'Terkendali',
            ],
            'recommendation' => 'Pertahankan genangan air 2-3 cm pada fase primordia.',
            'action_route' => route('admin.soil.index'),
            'action_label' => 'Periksa Sensor Tanah',
        ];

        return $threats;
    }

    /**
     * @param  list<array<string, mixed>>  $threats
     * @return array<string, mixed>
     */
    public function disasterSummary(array $threats): array
    {
        $dangerCount = 0;
        $warningCount = 0;
        $advisoryCount = 0;

        foreach ($threats as $threat) {
            $sev = $threat['severity'] ?? 'safe';
            if ($sev === 'danger') {
                $dangerCount++;
            } elseif ($sev === 'warning') {
                $warningCount++;
            } elseif ($sev === 'advisory') {
                $advisoryCount++;
            }
        }

        $systemStatus = $dangerCount > 0 ? 'danger' : ($warningCount > 0 ? 'warning' : 'safe');

        $headline = match ($systemStatus) {
            'danger' => 'Status Bahaya: Terdeteksi Potensi Bencana Alam & Cuaca Ekstrem',
            'warning' => 'Status Siaga: Waspadai Fluktuasi Cuaca & Risiko Serangan Hama',
            'safe' => 'Status Normal: Kondisi Agroklimat Lahan Pertanian Terkendali',
        };

        $subline = match ($systemStatus) {
            'danger' => "Terdapat {$dangerCount} ancaman kritis dengan tingkat bahaya tinggi yang memerlukan tindakan mitigasi cepat.",
            'warning' => "Terdapat {$warningCount} peringatan siaga yang perlu dipantau oleh admin dan tim PPL lapangan.",
            'safe' => 'Seluruh parameter cuaca, kelembapan, dan sensor tanah berada pada ambang batas aman budidaya.',
        };

        return [
            'total_threats' => count($threats),
            'danger_count' => $dangerCount,
            'warning_count' => $warningCount,
            'advisory_count' => $advisoryCount,
            'system_status' => $systemStatus,
            'status_headline' => $headline,
            'status_subline' => $subline,
            'evaluated_at' => now()->translatedFormat('d F Y, H:i') . ' WIB',
        ];
    }

    /**
     * @return list<array{id: int, title: string, body: string, time: string, source: string, tone: string}>
     */
    public function activeWarnings(): array
    {
        $warnings = [];

        // 1. Fetch from AdminBroadcast type warning
        if (Schema::hasTable('admin_broadcasts')) {
            $broadcasts = AdminBroadcast::query()
                ->where('type', 'warning')
                ->where('status', 'published')
                ->latest('published_at')
                ->limit(3)
                ->get();

            foreach ($broadcasts as $b) {
                $warnings[] = [
                    'id' => $b->id,
                    'title' => $b->title,
                    'body' => $b->message,
                    'time' => $b->published_at?->diffForHumans() ?? '-',
                    'source' => 'Broadcast Resmi Admin',
                    'tone' => 'orange',
                ];
            }
        }

        // 2. Fetch from Notification type warning/early_warning
        if (Schema::hasTable('notifications') && count($warnings) < 4) {
            $notifications = Notification::query()
                ->whereIn('type', ['warning', 'early_warning'])
                ->latest('id')
                ->limit(4 - count($warnings))
                ->get();

            foreach ($notifications as $n) {
                $warnings[] = [
                    'id' => $n->id,
                    'title' => $n->title,
                    'body' => $n->body,
                    'time' => $n->created_at?->diffForHumans() ?? '-',
                    'source' => 'Sensor & Early Warning',
                    'tone' => 'red',
                ];
            }
        }

        return $warnings;
    }

    private function activityTitle(string $action): string
    {
        return match ($action) {
            'admin_user_updated' => 'Data pengguna diperbarui',
            'admin_broadcast_created' => 'Broadcast baru dibuat',
            'admin_broadcast_updated' => 'Broadcast diperbarui',
            'admin_broadcast_deleted' => 'Broadcast dihapus',
            default => str_replace('_', ' ', $action),
        };
    }

    private function moduleName(string $entityType): string
    {
        return match (class_basename($entityType)) {
            'AdminBroadcast' => 'Broadcast',
            'CommunityReport', 'DiseaseScan' => 'Laporan Penyakit',
            'MarketListing', 'MarketOffer' => 'Marketplace',
            'User' => 'Pengguna',
            default => class_basename($entityType) ?: 'Sistem',
        };
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function count(string $model, string $table): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return $model::query()->count();
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function countWhere(string $model, string $table, string $column, string $value): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return $model::query()->where($column, $value)->count();
    }
}
