<?php

namespace App\Services\Soil;

use App\Models\Farm;
use App\Models\SoilDetection;
use App\Services\Weather\WeatherService;
use Illuminate\Support\Str;
use App\Models\IrrigationSchedule;

class SoilDetectionService
{
    public function __construct(
        private WeatherService $weatherService
    ) {}

    /**
     * Analyze soil inputs, calculate score, status, and generate agronomic recommendations
     */
    public function analyzeAndCreate(array $data, ?int $userId = null): SoilDetection
    {
        $farm = Farm::findOrFail($data['farm_id']);

        // Fetch live AgroMonitoring soil data if requested or if missing
        if (! isset($data['soil_temp_celsius']) || ! isset($data['moisture_percentage']) || ($data['sync_agromonitoring'] ?? false)) {
            if ($farm->latitude && $farm->longitude) {
                $agroSoil = $this->weatherService->getSoilData($farm->latitude, $farm->longitude);
                if ($agroSoil['success']) {
                    $data['soil_temp_celsius'] = $data['soil_temp_celsius'] ?? $agroSoil['data']['soil_temp_celsius'];
                    if (! isset($data['moisture_percentage'])) {
                        $data['moisture_percentage'] = $agroSoil['data']['moisture_percentage'];
                    }
                }
            }
        }

        $ph = (float) ($data['ph_level'] ?? 6.5);
        $n = (float) ($data['nitrogen_ppm'] ?? 120);
        $p = (float) ($data['phosphorus_ppm'] ?? 25);
        $k = (float) ($data['potassium_ppm'] ?? 150);
        $moisture = (float) ($data['moisture_percentage'] ?? 50);
        $organic = (float) ($data['organic_matter_percentage'] ?? 2.5);

        $evaluation = $this->evaluateSoilHealth($ph, $n, $p, $k, $moisture, $organic);

        $evaluation['irrigation_schedule'] = $this->calculateIrrigationSchedule(
            $moisture,
            $data['soil_temp_celsius'] ?? null,
            $farm->id
        );

        $sampleCode = $data['sample_code'] ?? 'SOIL-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        return SoilDetection::create([
            'farm_id' => $farm->id,
            'sample_code' => $sampleCode,
            'ph_level' => $ph,
            'nitrogen_ppm' => $n,
            'phosphorus_ppm' => $p,
            'potassium_ppm' => $k,
            'moisture_percentage' => $moisture,
            'organic_matter_percentage' => $organic,
            'soil_temp_celsius' => $data['soil_temp_celsius'] ?? null,
            'soil_type' => $data['soil_type'] ?? 'loam',
            'soil_health_score' => $evaluation['score'],
            'soil_status' => $evaluation['status'],
            'recommendations_json' => $evaluation['recommendations'],
            'tested_at' => $data['tested_at'] ?? now(),
            'notes' => $data['notes'] ?? null,
            'created_by' => $userId,
        ]);
    }

    /**
     * Evaluate soil params for rice (padi) cultivation
     */
    public function evaluateSoilHealth(
        float $ph,
        float $n,
        float $p,
        float $k,
        float $moisture,
        float $organic
    ): array {
        $score = 100;
        $recommendations = [];

        // 1. pH Evaluation (Ideal: 5.5 - 7.0 for rice)
        if ($ph < 5.0) {
            $score -= 25;
            $recommendations[] = [
                'category' => 'pH Tanah',
                'level' => 'critical',
                'title' => 'Tanah Sangat Asam (pH ' . $ph . ')',
                'action' => 'Aplikasikan Kapur Pertanian (Dolomit/Kapur Tohor) sebanyak 1.5 - 2 ton/ha 2 minggu sebelum tanam untuk meningkatkan pH ke batas ideal (5.5 - 6.5).',
            ];
        } elseif ($ph < 5.5) {
            $score -= 10;
            $recommendations[] = [
                'category' => 'pH Tanah',
                'level' => 'warning',
                'title' => 'Tanah Agak Asam (pH ' . $ph . ')',
                'action' => 'Tambahkan Kapur Dolomit 500 kg/ha saat pengolahan tanah pertama.',
            ];
        } elseif ($ph > 7.5) {
            $score -= 15;
            $recommendations[] = [
                'category' => 'pH Tanah',
                'level' => 'warning',
                'title' => 'Tanah Basa/Alkali (pH ' . $ph . ')',
                'action' => 'Gunakan pupuk yang bersifat mengasamkan seperti ZA (Amonium Sulfat) dan tingkatkan pemberian bahan organik/belerang.',
            ];
        }

        // 2. Nitrogen (N) Evaluation (Ideal: 100 - 180 ppm)
        if ($n < 80) {
            $score -= 20;
            $recommendations[] = [
                'category' => 'Hara Nitrogen (N)',
                'level' => 'critical',
                'title' => 'Defisiensi Nitrogen Tinggi (' . $n . ' ppm)',
                'action' => 'Berikan pemupukan susulan Urea 150 kg/ha (atau Za 200 kg/ha) terbagi pada fase vegetatif awal (14 HST) dan pembentukan anakan (30 HST).',
            ];
        } elseif ($n < 100) {
            $score -= 10;
            $recommendations[] = [
                'category' => 'Hara Nitrogen (N)',
                'level' => 'warning',
                'title' => 'Nitrogen Sedikit Kurang (' . $n . ' ppm)',
                'action' => 'Tambahkan Urea 75 - 100 kg/ha saat pemupukan susulan I.',
            ];
        } elseif ($n > 220) {
            $score -= 10;
            $recommendations[] = [
                'category' => 'Hara Nitrogen (N)',
                'level' => 'warning',
                'title' => 'Nitrogen Berlebihan (' . $n . ' ppm)',
                'action' => 'Kurangi dosis pupuk N untuk mencegah rebah tanaman padi dan kepekaan terhadap hama wereng/blas.',
            ];
        }

        // 3. Phosphorus (P) Evaluation (Ideal: 15 - 35 ppm)
        if ($p < 12) {
            $score -= 15;
            $recommendations[] = [
                'category' => 'Hara Fosfor (P)',
                'level' => 'warning',
                'title' => 'Defisiensi Fosfor (' . $p . ' ppm)',
                'action' => 'Berikan pupuk SP-36 sebanyak 100 - 125 kg/ha sebagai pupuk dasar saat tanam untuk merangsang pertumbuhan akar padi.',
            ];
        }

        // 4. Potassium (K) Evaluation (Ideal: 120 - 200 ppm)
        if ($k < 100) {
            $score -= 15;
            $recommendations[] = [
                'category' => 'Hara Kalium (K)',
                'level' => 'warning',
                'title' => 'Defisiensi Kalium (' . $k . ' ppm)',
                'action' => 'Berikan pupuk KCl (MOP) 75 - 100 kg/ha menjelang fase primordia/bunting (35-45 HST) untuk pengisian bulir padi yang berbobot.',
            ];
        }

        // 5. Moisture Evaluation (Ideal: 45% - 75% for paddy)
        if ($moisture < 35) {
            $score -= 15;
            $recommendations[] = [
                'category' => 'Kelembaban Tanah',
                'level' => 'warning',
                'title' => 'Kelembaban Rendah (' . $moisture . '%)',
                'action' => 'Lakukan pengairan irigasi berkala (intermittent irrigation) untuk menjaga petakan sawah tetap lembab.',
            ];
        } elseif ($moisture > 90) {
            $score -= 5;
            $recommendations[] = [
                'category' => 'Kelembaban Tanah',
                'level' => 'info',
                'title' => 'Kondisi Tergenang Penuh (' . $moisture . '%)',
                'action' => 'Atur drainase pembuangan air secara berkala agar terjadi sirkulasi oksigen di zona perakaran padi.',
            ];
        }

        // 6. Organic Matter Evaluation (Ideal: >= 2.0%)
        if ($organic < 1.5) {
            $score -= 10;
            $recommendations[] = [
                'category' => 'Bahan Organik',
                'level' => 'warning',
                'title' => 'Kandungan Bahan Organik Rendah (' . $organic . '%)',
                'action' => 'Tambahkan pupuk kandang/kompos 2 - 3 ton/ha atau kembalikan jerami padi sisa panen ke lahan.',
            ];
        }

        $score = max(10, min(100, $score));

        // Determine Status
        if ($score >= 80) {
            $status = 'optimal';
        } elseif ($score >= 65) {
            $status = 'needs_fertilizer';
        } elseif ($score >= 50) {
            $status = 'warning';
        } else {
            $status = 'critical';
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'category' => 'Kondisi Umum',
                'level' => 'optimal',
                'title' => 'Tanah dalam Kondisi Prima',
                'action' => 'Kualitas tanah optimal untuk pertumbuhan tanaman padi. Pertahankan pemupukan berimbang sesuai fase tumbuh.',
            ];
        }

        $irrigationSchedule = $this->calculateIrrigationSchedule($moisture);

        return [
            'score' => $score,
            'status' => $status,
            'recommendations' => $recommendations,
            'irrigation_schedule' => $irrigationSchedule,
        ];
    }

    /**
     * Calculate Indonesian PADI Irrigation Schedule based on soil moisture and soil temp
     *
     * @return array<string, mixed>
     */
    public function calculateIrrigationSchedule(
        float $moisture,
        ?float $soilTemp = null,
        ?int $farmId = null
    ): array {
        $now = now();

        // Cari jadwal irigasi terdekat dari lahan
        $schedule = null;

        if ($farmId) {
            $schedule = IrrigationSchedule::where('farm_id', $farmId)
                ->where('status', 'scheduled')
                ->whereDate('schedule_date', '>=', $now->toDateString())
                ->orderBy('schedule_date')
                ->orderBy('start_time')
                ->first();
        }

        // Kondisi tanah sangat kering
        if ($moisture < 35.0) {
            $status = 'urgent';
            $statusLabel = 'Pengairan Urgen (Sangat Kering)';
            $targetDepthCm = '5 - 7 cm';
            $waterVolumeM3Ha = '60 - 80 m3/ha';

            if ($schedule) {
                $scheduleDate = $schedule->schedule_date->translatedFormat('d F Y');

                $exactDateTime = $scheduleDate .
                    ($schedule->start_time
                        ? ', Jam ' . substr($schedule->start_time, 0, 5)
                        : '');

                $recommendedSlot = $schedule->start_time && $schedule->end_time
                    ? $scheduleDate . ', ' .
                        substr($schedule->start_time, 0, 5) . ' - ' .
                        substr($schedule->end_time, 0, 5) . ' WIB'
                    : $scheduleDate;

                $nextSchedule = $scheduleDate;

                $action = 'Kelembaban tanah sangat rendah. '
                    . 'Lahan membutuhkan air dan terdapat jadwal irigasi pada '
                    . $scheduleDate . '. Prioritaskan pengairan sesuai jadwal yang tersedia.';
            } else {
                $recommendedSlot = 'Menunggu jadwal irigasi';
                $exactDateTime = 'Belum tersedia';
                $nextSchedule = 'Belum ada jadwal irigasi';

                $action = 'Kelembaban tanah sangat rendah dan belum terdapat jadwal irigasi '
                    . 'untuk lahan ini. Periksa ketersediaan sumber air atau tambahkan jadwal irigasi.';
            }
        }

        // Kondisi agak kering
        elseif ($moisture < 45.0) {
            $status = 'intermittent';
            $statusLabel = 'Pengairan Berkala (Agak Kering)';
            $targetDepthCm = '3 - 5 cm';
            $waterVolumeM3Ha = '40 - 50 m3/ha';

            if ($schedule) {
                $scheduleDate = $schedule->schedule_date->translatedFormat('d F Y');

                $recommendedSlot = $scheduleDate;

                if ($schedule->start_time && $schedule->end_time) {
                    $recommendedSlot .= ', ' .
                        substr($schedule->start_time, 0, 5) . ' - ' .
                        substr($schedule->end_time, 0, 5) . ' WIB';
                }

                $exactDateTime = $recommendedSlot;
                $nextSchedule = $scheduleDate;

                $action = 'Kelembaban tanah mulai rendah. '
                    . 'Lakukan pengairan berselang pada jadwal irigasi yang tersedia.';
            } else {
                $recommendedSlot = 'Menunggu jadwal irigasi';
                $exactDateTime = 'Belum tersedia';
                $nextSchedule = 'Belum ada jadwal irigasi';

                $action = 'Kelembaban tanah mulai rendah, tetapi belum terdapat jadwal irigasi. '
                    . 'Pantau kelembaban tanah dan tambahkan jadwal ketika air tersedia.';
            }
        }

        // Kondisi optimal
        elseif ($moisture <= 80.0) {
            $status = 'optimal';
            $statusLabel = 'Kelembaban Lembab Optimal';
            $recommendedSlot = 'Tunda Pengairan (Monitoring Cukup)';
            $exactDateTime = 'Tidak perlu irigasi saat ini';
            $targetDepthCm = '2 - 3 cm';
            $waterVolumeM3Ha = '0 - 20 m3/ha';

            $nextSchedule = $schedule
                ? $schedule->schedule_date->translatedFormat('d F Y')
                : 'Belum ada jadwal';

            $action = 'Kelembaban tanah masih optimal. '
                . 'Tidak perlu melakukan pengairan tambahan saat ini. '
                . 'Pantau kondisi tanah dan gunakan jadwal irigasi berikutnya jika diperlukan.';
        }

        // Tergenang
        else {
            $status = 'drainage';
            $statusLabel = 'Jenuh / Tergenang Penuh';
            $recommendedSlot = 'Pembukaan Saluran Drainase (Segera)';
            $exactDateTime = 'Segera Sekarang (' . $now->translatedFormat('d F Y, H:i') . ' WIB)';
            $targetDepthCm = 'Drainase / Pengeringan Lahan';
            $waterVolumeM3Ha = '0 m3/ha (Keluarkan Air)';
            $nextSchedule = 'Saat Kelembaban Kembali Optimal';

            $action = 'Lahan memiliki kelembaban sangat tinggi. '
                . 'Tunda pengairan dan buka drainase untuk mengurangi genangan.';
        }

        return [
            'status' => $status,
            'status_label' => $statusLabel,
            'moisture_percentage' => $moisture,
            'soil_temp_celsius' => $soilTemp ?? 26.5,
            'exact_date_time' => $exactDateTime,
            'recommended_time_slot' => $recommendedSlot,
            'target_water_depth' => $targetDepthCm,
            'water_volume' => $waterVolumeM3Ha,
            'next_schedule' => $nextSchedule,
            'action_recommendation' => $action,

            // Informasi jadwal aktual
            'has_schedule' => $schedule !== null,
            'schedule_source' => $schedule?->source,
            'schedule_date' => $schedule?->schedule_date?->format('Y-m-d'),
            'schedule_start_time' => $schedule?->start_time,
            'schedule_end_time' => $schedule?->end_time,
        ];
    }
}
