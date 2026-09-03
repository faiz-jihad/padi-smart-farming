<?php

namespace App\Services\Irrigation;

use App\Models\Farm;
use App\Models\IrrigationSchedule;
use App\Models\SoilDetection;
use App\Services\Soil\SoilDetectionService;
use App\Services\Weather\WeatherService;

/**
 * Service untuk membandingkan 3 Sumber Informasi Irigasi:
 * 1. Rekomendasi Sistem (Sensor / Satelit / Aturan SRI & AWD)
 * 2. Jadwal Lapangan / Manual (Raksa Bumi / Petugas Lapangan)
 * 3. Data Resmi PU / WRDC (Daerah Irigasi & Balai Wilayah Sungai)
 * 
 * Menghasilkan Jadwal Irigasi Konkret & Panduan Aksi Berbasis Perbandingan.
 */
class IrrigationComparisonService
{
    public function __construct(
        protected SoilDetectionService $soilDetectionService,
        protected WrdcWaterResourceService $wrdcService,
        protected WeatherService $weatherService
    ) {}

    /**
     * Menjalankan analisis komparasi 3 sumber informasi untuk suatu lahan
     *
     * @return array<string, mixed>
     */
    public function compareForFarm(
        Farm $farm,
        ?SoilDetection $soilDetection = null,
        ?float $explicitMoisture = null,
        ?float $explicitSoilTemp = null,
        ?array $overrideOfficialContext = null
    ): array {
        // 1. Tentukan Kelembaban & Suhu Tanah (Sumber 1)
        $moisture = $explicitMoisture;
        $soilTemp = $explicitSoilTemp;

        if ($moisture === null && $soilDetection !== null) {
            $moisture = (float) $soilDetection->moisture_percentage;
            $soilTemp = $soilDetection->soil_temp_celsius ? (float) $soilDetection->soil_temp_celsius : null;
        }

        if ($moisture === null) {
            $latestDetection = SoilDetection::where('farm_id', $farm->id)
                ->latest('tested_at')
                ->first();

            if ($latestDetection) {
                $moisture = (float) $latestDetection->moisture_percentage;
                $soilTemp = $latestDetection->soil_temp_celsius ? (float) $latestDetection->soil_temp_celsius : null;
                $soilDetection = $latestDetection;
            } else {
                $moisture = 50.0;
                $soilTemp = 27.0;
            }
        }

        // 2. Sumber 1: Rekomendasi Sistem (Soil & Weather Rule Engine)
        $systemRec = $this->soilDetectionService->calculateIrrigationSchedule(
            $moisture,
            $soilTemp,
            $farm->id
        );

        // Ambil data cuaca untuk korelasi risiko hujan
        $weatherData = $this->getWeatherSummary($farm);

        // 3. Sumber 2: Jadwal Lapangan / Manual (Raksa Bumi)
        $fieldSchedule = $this->getFieldScheduleData($farm->id);

        // 4. Sumber 3: Konteks Resmi PU / WRDC (Daerah Irigasi & Infrastruktur)
        if ($overrideOfficialContext !== null) {
            $officialContext = $overrideOfficialContext;
        } else {
            $officialContext = $this->wrdcService->getOfficialContextForFarm($farm);
        }

        // Pastikan official context memiliki struktur standar jika tidak tersedia
        if (empty($officialContext) || ($officialContext['is_available'] ?? true) === false) {
            $officialContext = [
                'is_available' => false,
                'is_live_api' => false,
                'provider' => 'Kementerian Pekerjaan Umum (Ditjen Sumber Daya Air / WRDC)',
                'daerah_irigasi' => 'Belum tersedia dari sumber resmi',
                'di_code' => '-',
                'authority' => 'Belum tersedia dari sumber resmi',
                'bbws_bws' => 'Belum tersedia dari sumber resmi',
                'primary_source' => 'Belum tersedia dari sumber resmi',
                'scheme_type' => 'Belum terdefinisi',
                'service_area_ha' => null,
                'water_supply_status' => 'Belum tersedia dari sumber resmi',
                'integration_status' => 'unconnected',
                'notice' => 'Data resmi Daerah Irigasi & SDA belum terhubung untuk wilayah ini.',
            ];
        } else {
            $officialContext['is_available'] = true;
        }

        // 5. Analisis Perbandingan & Sintesis Jadwal Konkret
        $comparison = $this->synthesizeComparison(
            $moisture,
            $systemRec,
            $fieldSchedule,
            $officialContext,
            $weatherData
        );

        // 6. Buat Objek Jadwal Irigasi Final
        $irrigationSchedule = $this->buildConcreteSchedule(
            $moisture,
            $systemRec,
            $fieldSchedule,
            $officialContext,
            $comparison
        );

        return [
            'success' => true,
            'farm' => [
                'id' => $farm->id,
                'name' => $farm->name,
                'irrigation_type' => $farm->irrigation_type,
                'area_ha' => $farm->area_ha,
                'latitude' => $farm->latitude,
                'longitude' => $farm->longitude,
                'regency' => $farm->regency?->name ?? (is_string($farm->regency) ? $farm->regency : null),
                'district' => $farm->district?->name ?? (is_string($farm->district) ? $farm->district : null),
            ],
            'soil_sample' => $soilDetection ? [
                'id' => $soilDetection->id,
                'sample_code' => $soilDetection->sample_code,
                'moisture_percentage' => $moisture,
                'soil_temp_celsius' => $soilTemp,
                'tested_at' => $soilDetection->tested_at?->toIso8601String(),
            ] : [
                'moisture_percentage' => $moisture,
                'soil_temp_celsius' => $soilTemp,
                'source' => 'default_estimate',
            ],
            'weather_summary' => $weatherData,
            'system_recommendation' => $systemRec,
            'field_schedule' => $fieldSchedule,
            'official_context' => $officialContext,
            'irrigation_schedule' => $irrigationSchedule,
            'comparison' => $comparison,
        ];
    }

    /**
     * Mengambil jadwal lapangan terdekat untuk lahan
     *
     * @return array<string, mixed>
     */
    public function getFieldScheduleData(int $farmId): array
    {
        $today = now()->toDateString();

        $activeSchedule = IrrigationSchedule::where('farm_id', $farmId)
            ->where('status', 'scheduled')
            ->whereDate('schedule_date', '>=', $today)
            ->orderBy('schedule_date')
            ->orderBy('start_time')
            ->first();

        $allUpcoming = IrrigationSchedule::where('farm_id', $farmId)
            ->where('status', 'scheduled')
            ->whereDate('schedule_date', '>=', $today)
            ->orderBy('schedule_date')
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        if (! $activeSchedule) {
            return [
                'has_schedule' => false,
                'status' => 'no_schedule',
                'schedule_id' => null,
                'schedule_date' => null,
                'start_time' => null,
                'end_time' => null,
                'time_range' => 'Belum Terjadwal',
                'source' => null,
                'source_label' => 'Belum Ada Jadwal Lapangan',
                'officer_name' => null,
                'irrigation_block' => null,
                'water_source' => null,
                'notes' => null,
                'summary_text' => 'Belum ada jadwal gilir air lapangan yang terdaftar.',
                'upcoming_schedules' => [],
            ];
        }

        $sourceLabel = match ($activeSchedule->source) {
            'raksa_bumi' => 'Petugas Raksa Bumi',
            'officer' => 'Penyuluh Pertanian Lapangan (PPL)',
            'system' => 'Rekomendasi Terjadwal',
            default => 'Input Manual Petani',
        };

        $timeRange = $activeSchedule->start_time
            ? ($activeSchedule->end_time
                ? substr($activeSchedule->start_time, 0, 5) . ' - ' . substr($activeSchedule->end_time, 0, 5) . ' WIB'
                : 'Jam ' . substr($activeSchedule->start_time, 0, 5) . ' WIB')
            : '06:00 - 08:30 WIB (Pagi)';

        return [
            'has_schedule' => true,
            'status' => $activeSchedule->status,
            'schedule_id' => $activeSchedule->id,
            'schedule_date' => $activeSchedule->schedule_date->format('Y-m-d'),
            'schedule_date_formatted' => $activeSchedule->schedule_date->translatedFormat('l, d F Y'),
            'start_time' => $activeSchedule->start_time ? substr($activeSchedule->start_time, 0, 5) : '06:00',
            'end_time' => $activeSchedule->end_time ? substr($activeSchedule->end_time, 0, 5) : '08:30',
            'time_range' => $timeRange,
            'source' => $activeSchedule->source,
            'source_label' => $sourceLabel,
            'officer_name' => $activeSchedule->officer_name ?? ($activeSchedule->source === 'raksa_bumi' ? 'Raksa Bumi Desa' : null),
            'irrigation_block' => $activeSchedule->irrigation_block ?? 'Petak Tersier Utama',
            'water_source' => $activeSchedule->water_source ?? 'Saluran Sekunder/Tersier',
            'notes' => $activeSchedule->notes,
            'summary_text' => "Jadwal {$sourceLabel} pada {$activeSchedule->schedule_date->translatedFormat('d M Y')} ({$timeRange})",
            'upcoming_schedules' => $allUpcoming->map(fn ($s) => [
                'id' => $s->id,
                'date' => $s->schedule_date->format('Y-m-d'),
                'date_formatted' => $s->schedule_date->translatedFormat('d M Y'),
                'time' => $s->start_time ? substr($s->start_time, 0, 5) : '-',
                'source' => $s->source,
                'officer_name' => $s->officer_name,
                'block' => $s->irrigation_block,
            ])->toArray(),
        ];
    }

    /**
     * Membangun blok Jadwal Irigasi konkret (Actionable Schedule)
     *
     * @return array<string, mixed>
     */
    protected function buildConcreteSchedule(
        float $moisture,
        array $systemRec,
        array $fieldSchedule,
        array $officialContext,
        array $comparison
    ): array {
        if ($fieldSchedule['has_schedule']) {
            return [
                'date' => $fieldSchedule['schedule_date'],
                'date_formatted' => $fieldSchedule['schedule_date_formatted'],
                'start_time' => $fieldSchedule['start_time'],
                'end_time' => $fieldSchedule['end_time'],
                'time_range' => $fieldSchedule['time_range'],
                'status' => 'scheduled',
                'status_label' => 'Jadwal Lapangan Terdaftar',
                'is_field_schedule' => true,
                'source' => $fieldSchedule['source'],
                'source_label' => $fieldSchedule['source_label'],
                'officer_name' => $fieldSchedule['officer_name'],
                'irrigation_block' => $fieldSchedule['irrigation_block'],
                'water_source' => $fieldSchedule['water_source'],
                'target_water_depth' => $systemRec['target_water_depth'],
                'water_volume' => $systemRec['water_volume'],
                'headline' => 'Jadwal Irigasi Terdaftar di Lapangan',
                'display_badge' => 'JADWAL TERDAFTAR',
            ];
        }

        // Jika belum ada jadwal lapangan, buat Rekomendasi Slot Waktu Konkret
        $today = now();
        if ($moisture < 45.0) {
            // Butuh air segera: jadwalkan besok pagi (atau hari ini jika pagi)
            $suggestedDate = $today->hour < 8 ? $today : $today->copy()->addDay();
            $statusLabel = 'Rekomendasi Slot (Perlu Penjadwalan)';
        } elseif ($moisture > 75.0) {
            // Terlalu basah: jadwalkan setelah pengeringan 2-3 hari
            $suggestedDate = $today->copy()->addDays(3);
            $statusLabel = 'Rekomendasi Setelah Pengeringan';
        } else {
            // Optimal: jadwalkan pengairan berkala 3 hari ke depan
            $suggestedDate = $today->copy()->addDays(3);
            $statusLabel = 'Rekomendasi Giliran Berkala';
        }

        return [
            'date' => $suggestedDate->format('Y-m-d'),
            'date_formatted' => $suggestedDate->translatedFormat('l, d F Y'),
            'start_time' => '06:00',
            'end_time' => '08:30',
            'time_range' => '06:00 - 08:30 WIB (Pagi)',
            'status' => 'recommended',
            'status_label' => $statusLabel,
            'is_field_schedule' => false,
            'source' => 'system',
            'source_label' => 'Rekomendasi Sistem (AWD/SRI)',
            'officer_name' => 'Belum Diinput (Menunggu Raksa Bumi)',
            'irrigation_block' => 'Petak Tersier Utama',
            'water_source' => $officialContext['primary_source'] !== 'Belum tersedia dari sumber resmi' ? $officialContext['primary_source'] : 'Saluran Irigasi Tersier',
            'target_water_depth' => $systemRec['target_water_depth'],
            'water_volume' => $systemRec['water_volume'],
            'headline' => 'Rekomendasi Jadwal Irigasi Lahan',
            'display_badge' => 'REKOMENDASI JADWAL',
        ];
    }

    /**
     * Sintesis komparasi 3 sumber informasi (Menghasilkan Jadwal Konkret)
     *
     * @return array<string, mixed>
     */
    protected function synthesizeComparison(
        float $moisture,
        array $systemRec,
        array $fieldSchedule,
        array $officialContext,
        array $weatherData
    ): array {
        $systemStatus = $systemRec['status'] ?? 'optimal';
        $hasSchedule = $fieldSchedule['has_schedule'];
        $rainProb = $weatherData['rain_probability'] ?? 0;
        $isHighRainRisk = $rainProb >= 70;
        $hasOfficialData = ($officialContext['is_available'] ?? false) && $officialContext['daerah_irigasi'] !== 'Belum tersedia dari sumber resmi';

        // KASUS 1: Risiko Cuaca Hujan Lebat (Prioritas Keselamatan Lahan)
        if ($isHighRainRisk && $moisture >= 40.0) {
            $scheduleDateText = $hasSchedule ? $fieldSchedule['schedule_date_formatted'] : now()->addDay()->translatedFormat('l, d F Y');

            return [
                'status' => 'weather_warning',
                'status_label' => 'Waspada Hujan Lebat (Disarankan Tunda)',
                'badge_color' => '#d97706',
                'bg_color' => '#fffbeb',
                'border_color' => '#fcd34d',
                'is_aligned' => false,
                'conflict_type' => 'weather_risk',
                'headline' => "Peringatan Cuaca: Disarankan Tunda Pengairan ({$scheduleDateText})",
                'explanation' => "Prakiraan cuaca BMKG menunjukkan potensi hujan {$rainProb}%. Kelembaban tanah saat ini ({$moisture}%) mencukupi. Jadwal lapangan tetap tercatat, namun disarankan menunda pembukaan pintu air untuk mencegah genangan berlebih.",
                'final_recommendation' => 'Tunda pengairan air irigasi tambahan sementara waktu. Buka saluran pembuangan/drainase sawah.',
                'action_items' => [
                    'Tunda pembukaan pintu air irigasi saat terjadi hujan.',
                    'Bersihkan saluran drainase petakan agar air tidak meluap.',
                    'Manfaatkan pasokan air hujan alami untuk efisiensi kuota air.',
                ],
                'source_alignment' => [
                    'system' => 'Sensor mendeteksi kelembaban tanah cukup & risiko hujan',
                    'field' => $hasSchedule ? "Jadwal tercatat: {$fieldSchedule['summary_text']}" : 'Belum ada jadwal manual',
                    'wrdc' => $hasOfficialData ? "Daerah Irigasi: {$officialContext['daerah_irigasi']}" : 'Data resmi DI belum tersedia',
                ],
            ];
        }

        // KASUS 2: Tanah Sangat Basah / Tergenang (>75%)
        if ($moisture >= 75.0) {
            return [
                'status' => 'drainage_needed',
                'status_label' => 'Perlu Pengeringan Berselang (Drainase)',
                'badge_color' => '#0284c7',
                'bg_color' => '#f0f9ff',
                'border_color' => '#bae6fd',
                'is_aligned' => ! $hasSchedule,
                'conflict_type' => $hasSchedule ? 'schedule_override_advised' : 'none',
                'headline' => 'Kondisi Tanah Jenuh Air — Terapkan Pengeringan (AWD)',
                'explanation' => "Kelembaban tanah saat ini sangat tinggi ({$moisture}%). Tanaman padi membutuhkan fase pengeringan berkala selama 2-3 hari untuk merangsang aerasi oksigen dan perakaran dalam.",
                'final_recommendation' => $hasSchedule
                    ? "Jadwal lapangan tersedia pada {$fieldSchedule['schedule_date_formatted']}, namun disarankan mengeringkan petakan terlebih dahulu sebelum memasukkan air baru."
                    : 'Kondisi tanah jenuh. Tidak diperlukan pengairan saat ini, lakukan drainase ringan jika genangan melebihi 5 cm.',
                'action_items' => [
                    'Tutup pintu pemasukan air saluran tersier.',
                    'Buka saluran pembuangan hingga kondisi macak-macak (kelembaban 50-60%).',
                    'Lakukan penyiangan gulma saat kondisi tanah tidak terlalu tergenang.',
                ],
                'source_alignment' => [
                    'system' => 'Rekomendasi drainase & pengeringan berkala',
                    'field' => $hasSchedule ? "Jadwal terdaftar: {$fieldSchedule['summary_text']}" : 'Tidak ada jadwal terdaftar',
                    'wrdc' => $hasOfficialData ? "Sumber air: {$officialContext['primary_source']}" : 'Data resmi belum tersedia',
                ],
            ];
        }

        // KASUS 3: CASE A & B — Tanah Kering / Butuh Air + Jadwal Lapangan Tersedia (ALIGNED)
        if (($systemStatus === 'urgent' || $systemStatus === 'intermittent') && $hasSchedule) {
            $officerInfo = $fieldSchedule['officer_name'] ? " ({$fieldSchedule['officer_name']})" : '';

            return [
                'status' => 'aligned',
                'status_label' => 'Jadwal Lapangan Sesuai Rekomendasi',
                'badge_color' => '#166534',
                'bg_color' => '#f0fdf4',
                'border_color' => '#86efac',
                'is_aligned' => true,
                'conflict_type' => 'none',
                'headline' => "Jadwal Irigasi: {$fieldSchedule['schedule_date_formatted']} ({$fieldSchedule['time_range']})",
                'explanation' => "Kondisi kelembaban tanah ({$moisture}%) memerlukan suplai air, dan jadwal gilir air lapangan telah terdaftar pada {$fieldSchedule['schedule_date_formatted']} ({$fieldSchedule['time_range']}) oleh {$fieldSchedule['source_label']}{$officerInfo}.",
                'final_recommendation' => "Lakukan pengairan sesuai jadwal lapangan pada {$fieldSchedule['schedule_date_formatted']} ({$fieldSchedule['time_range']}) dengan target kedalaman {$systemRec['target_water_depth']}.",
                'action_items' => [
                    "Siapkan pintu petak tersier pada {$fieldSchedule['time_range']}.",
                    "Alirkan air hingga mencapai kedalaman {$systemRec['target_water_depth']}.",
                    "Estimasi volume pasokan: {$systemRec['water_volume']}.",
                ],
                'source_alignment' => [
                    'system' => "Butuh air: {$systemRec['status_label']}",
                    'field' => "Tersedia: {$fieldSchedule['summary_text']}",
                    'wrdc' => $hasOfficialData ? "Daerah Irigasi: {$officialContext['daerah_irigasi']} ({$officialContext['authority']})" : 'Data resmi DI belum tersedia (Jadwal lapangan tetap menjadi acuan)',
                ],
            ];
        }

        // KASUS 4: CASE C & D — Tanah Kering / Butuh Air + Belum Ada Jadwal Lapangan (NEEDS SCHEDULING)
        if (($systemStatus === 'urgent' || $systemStatus === 'intermittent') && ! $hasSchedule) {
            $suggestedDateText = now()->addDay()->translatedFormat('l, d F Y');

            return [
                'status' => 'needs_scheduling',
                'status_label' => 'Perlu Penjadwalan Lapangan',
                'badge_color' => '#b91c1c',
                'bg_color' => '#fef2f2',
                'border_color' => '#fca5a5',
                'is_aligned' => false,
                'conflict_type' => 'missing_field_schedule',
                'headline' => "Rekomendasi Jadwal Irigasi: {$suggestedDateText}, 06:00 – 08:30 WIB",
                'explanation' => "Kelembaban tanah rendah ({$moisture}%), tanaman padi membutuhkan pengairan segera ({$systemRec['target_water_depth']}). Belum ada jadwal gilir air dari Raksa Bumi yang tercatat. Sistem merekomendasikan slot pengairan pada {$suggestedDateText}.",
                'final_recommendation' => "Segera input jadwal pengairan mandiri atau hubungi petugas Raksa Bumi / P3A desa untuk mengonfirmasi pembagian giliran air.",
                'action_items' => [
                    'Klik tombol "Input Jadwal Irigasi" untuk mendaftarkan jadwal ke sistem.',
                    'Koordinasikan kebutuhan air dengan kelompok tani / Raksa Bumi.',
                    "Target kedalaman pengairan: {$systemRec['target_water_depth']} (Volume: {$systemRec['water_volume']}).",
                ],
                'source_alignment' => [
                    'system' => "Kondisi: {$systemRec['status_label']} ({$systemRec['action_recommendation']})",
                    'field' => 'Belum ada jadwal lapangan yang tercatat',
                    'wrdc' => $hasOfficialData ? "Kewenangan: {$officialContext['authority']} — Pasokan {$officialContext['water_supply_status']}" : 'Data resmi belum tersedia',
                ],
            ];
        }

        // KASUS 5: Tanah Masih Optimal/Cukup + Ada Jadwal Lapangan (ADVISORY / NOT URGENT)
        if ($systemStatus === 'optimal' && $hasSchedule) {
            return [
                'status' => 'moisture_sufficient',
                'status_label' => 'Perlu Penyesuaian Debit Air (Advisory)',
                'badge_color' => '#0369a1',
                'bg_color' => '#f0f9ff',
                'border_color' => '#7dd3fc',
                'is_aligned' => false,
                'conflict_type' => 'advisory_light_watering',
                'headline' => "Jadwal Irigasi: {$fieldSchedule['schedule_date_formatted']} (Debit Disesuaikan)",
                'explanation' => "Jadwal gilir air tersedia pada {$fieldSchedule['schedule_date_formatted']}, namun kelembaban tanah saat ini masih dalam batas ideal ({$moisture}%). Jadwal lapangan tetap dipertahankan, disarankan melakukan pengairan tipis (macak-macak 2–3 cm) saja.",
                'final_recommendation' => "Pengairan dapat tetap dilaksanakan sesuai jadwal {$fieldSchedule['source_label']}, namun kurangi debit air agar air tidak terbuang sia-sia.",
                'action_items' => [
                    'Pertahankan jadwal lapangan tanpa membatalkannya.',
                    'Cukup basahi permukaan tanah setinggi 2 - 3 cm (macak-macak).',
                    'Simpan cadangan air di saluran tersier untuk giliran berikutnya.',
                ],
                'source_alignment' => [
                    'system' => "Kelembaban ideal ({$moisture}%) — {$systemRec['status_label']}",
                    'field' => "Jadwal terdaftar: {$fieldSchedule['summary_text']}",
                    'wrdc' => $hasOfficialData ? "DI: {$officialContext['daerah_irigasi']}" : 'Data resmi belum tersedia',
                ],
            ];
        }

        // KASUS 6: Tanah Optimal + Tidak Ada Jadwal (OPTIMAL)
        $nextSuggestedDate = now()->addDays(3)->translatedFormat('l, d F Y');

        return [
            'status' => 'optimal',
            'status_label' => 'Kondisi Air Lahan Optimal',
            'badge_color' => '#166534',
            'bg_color' => '#f0fdf4',
            'border_color' => '#86efac',
            'is_aligned' => true,
            'conflict_type' => 'none',
            'headline' => "Rekomendasi Jadwal Berikutnya: {$nextSuggestedDate}",
            'explanation' => "Kelembaban tanah saat ini ({$moisture}%) berada dalam kondisi ideal untuk pertumbuhan tanaman padi. Belum diperlukan pengairan aktif saat ini.",
            'final_recommendation' => 'Pertahankan pemantauan rutin. Pengairan berikutnya diperkirakan 3-4 hari ke depan.',
            'action_items' => [
                'Lakukan monitoring visual tinggi muka air sawah.',
                'Pertahankan kondisi macak-macak sesuai prinsip SRI / AWD.',
            ],
            'source_alignment' => [
                'system' => "Optimal ({$moisture}%)",
                'field' => 'Tidak memerlukan jadwal mendesak',
                'wrdc' => $hasOfficialData ? "Daerah Irigasi: {$officialContext['daerah_irigasi']}" : 'Data resmi belum tersedia',
            ],
        ];
    }

    /**
     * Mengambil ringkasan cuaca dan probabilitas hujan
     *
     * @return array<string, mixed>
     */
    protected function getWeatherSummary(Farm $farm): array
    {
        $lat = (float) ($farm->latitude ?? -7.25);
        $lon = (float) ($farm->longitude ?? 112.75);

        try {
            $bmkg = $this->weatherService->getBMKGForecast($lat, $lon, 3);
            if (! empty($bmkg['data']['forecasts'][0]['rain_probability_percentage'])) {
                $prob = (int) $bmkg['data']['forecasts'][0]['rain_probability_percentage'];
                $desc = $bmkg['data']['forecasts'][0]['weather'] ?? 'Cerah Berawan';
                return [
                    'source' => 'BMKG Indonesia',
                    'weather' => $desc,
                    'rain_probability' => $prob,
                    'is_rainy' => $prob >= 70,
                ];
            }
        } catch (\Throwable $e) {
            // Fallthrough
        }

        return [
            'source' => 'Prakiraan Agroklimat Lahan',
            'weather' => 'Cerah Berawan',
            'rain_probability' => 20,
            'is_rainy' => false,
        ];
    }
}
