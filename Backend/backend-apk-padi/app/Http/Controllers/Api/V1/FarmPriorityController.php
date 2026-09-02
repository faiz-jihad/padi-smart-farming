<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CropSeason;
use App\Models\DiseaseScan;
use App\Models\Farm;
use App\Models\FarmActivity;
use App\Models\IrrigationSchedule;
use App\Models\PplValidation;
use App\Models\WeatherSnapshot;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmPriorityController extends Controller
{
    /**
     * Get dynamic daily priorities for a specific farm based on:
     * - Active crop season / HST stage
     * - Disease scans & pest risks (< 7 days)
     * - PPL validation status
     * - Irrigation schedules for today
     * - Real-time weather warnings
     */
    public function index(Request $request, Farm $farm): JsonResponse
    {
        $user = $request->user();

        // Access check
        if ($farm->farmer_user_id !== $user->id && ! $user->hasRole('admin') && ! $user->hasRole('extension_officer')) {
            return ApiResponse::error('Akses ditolak untuk lahan ini.', 403);
        }

        $priorities = [];
        $today = Carbon::today();

        // 1. Active Crop Season & HST Context
        $activeSeason = CropSeason::where('farm_id', $farm->id)
            ->where('status', 'active')
            ->with('variety')
            ->latest('id')
            ->first();

        $hst = null;
        if ($activeSeason) {
            $plantDate = $activeSeason->planting_date ? Carbon::parse($activeSeason->planting_date) : null;
            if ($plantDate) {
                $hst = max(0, (int) $plantDate->diffInDays($today, false));
            }
        }

        // 2. Recent Disease Scans & Pest Risks (last 7 days)
        $recentDiseasedScan = DiseaseScan::where('farm_id', $farm->id)
            ->where('scanned_at', '>=', now()->subDays(7))
            ->where('quality_status', '!=', 'healthy')
            ->latest('scanned_at')
            ->first();

        if ($recentDiseasedScan) {
            $diseaseName = $recentDiseasedScan->predicted_class ?? 'Penyakit Tanaman';
            
            // Check if submitted to PPL
            $pplValidation = PplValidation::where('scan_id', $recentDiseasedScan->id)->first();

            if ($pplValidation && $pplValidation->status === 'validated') {
                $priorities[] = [
                    'id'           => 'ppl_validated_' . $pplValidation->id,
                    'type'         => 'ppl_verified',
                    'urgency'      => 'urgent',
                    'title'        => 'Rekomendasi Petugas PPL Lapangan',
                    'subtitle'     => "Penyuluh telah memvalidasi temuan {$diseaseName}. Tindak lanjuti catatan: " . ($pplValidation->notes ?: 'Terapkan pengendalian sesuai panduan.'),
                    'action_label' => 'Lihat Detail Panduan',
                    'route'        => '/plant-check',
                    'icon'         => 'verified_user',
                ];
            } elseif ($pplValidation && $pplValidation->status === 'pending') {
                $priorities[] = [
                    'id'           => 'ppl_pending_' . $pplValidation->id,
                    'type'         => 'ppl_pending',
                    'urgency'      => 'warning',
                    'title'        => 'Menunggu Tinjauan Penyuluh Lapangan',
                    'subtitle'     => "Laporan gejala {$diseaseName} sedang dalam antrean verifikasi petugas PPL wilayah Anda.",
                    'action_label' => 'Pantau Kasus',
                    'route'        => '/plant-check',
                    'icon'         => 'schedule',
                ];
            } else {
                $priorities[] = [
                    'id'           => 'disease_action_' . $recentDiseasedScan->id,
                    'type'         => 'disease_control',
                    'urgency'      => 'urgent',
                    'title'        => "Pengendalian {$diseaseName}",
                    'subtitle'     => "Terdeteksi 7 hari terakhir pada {$farm->name}. Segera isolasi petak dan konsultasikan ke PPL.",
                    'action_label' => 'Konsultasikan ke PPL',
                    'route'        => '/plant-check',
                    'icon'         => 'coronavirus',
                ];
            }
        }

        // 3. Irrigation Schedule for Today
        $todayIrrigation = IrrigationSchedule::where('farm_id', $farm->id)
            ->whereDate('schedule_date', $today)
            ->first();

        if ($todayIrrigation) {
            $timeSlot = "{$todayIrrigation->start_time} - {$todayIrrigation->end_time}";
            $priorities[] = [
                'id'           => 'irrigation_' . $todayIrrigation->id,
                'type'         => 'irrigation',
                'urgency'      => 'urgent',
                'title'        => 'Giliran Air Irigasi Hari Ini',
                'subtitle'     => "Jadwal buka pintu air pukul {$timeSlot} (Blok: {$todayIrrigation->irrigation_block}).",
                'action_label' => 'Atur Pintu Air',
                'route'        => '/land/timeline',
                'icon'         => 'water_drop',
            ];
        }

        // 4. Weather Advisory Warning
        $latestWeather = WeatherSnapshot::where('farm_id', $farm->id)
            ->latest('observed_at')
            ->first();

        if ($latestWeather && is_array($latestWeather->payload_json)) {
            $payload = $latestWeather->payload_json;
            $rainMm = $payload['rain']['1h'] ?? $payload['rain']['3h'] ?? 0;
            $humidity = $payload['main']['humidity'] ?? 0;
            $temp = $payload['main']['temp'] ?? 28;

            if ($rainMm > 15 || $humidity > 90) {
                $priorities[] = [
                    'id'           => 'weather_heavy_rain',
                    'type'         => 'weather_alert',
                    'urgency'      => 'warning',
                    'title'        => 'Waspada Curah Hujan & Kelembaban Tinggi',
                    'subtitle'     => 'Kelembaban tinggi memicu perkembangan jamur dan bakteri blas. Periksa parit pembuangan.',
                    'action_label' => 'Cek Agroklimat',
                    'route'        => '/planting-calendar',
                    'icon'         => 'thunderstorm',
                ];
            } elseif ($temp >= 34) {
                $priorities[] = [
                    'id'           => 'weather_hot_temp',
                    'type'         => 'weather_alert',
                    'urgency'      => 'warning',
                    'title'        => 'Suhu Panas Ekstrem Terdeteksi',
                    'subtitle'     => "Suhu mencapai {$temp}°C. Jaga ketinggian genangan air 3-5 cm untuk mencegah pengeringan rumpun.",
                    'action_label' => 'Cek Saran Pemupukan',
                    'route'        => '/fertilizer',
                    'icon'         => 'wb_sunny',
                ];
            }
        }

        // 5. Agronomy Advisory based on HST (if not diseased)
        if ($hst !== null && empty($recentDiseasedScan)) {
            if ($hst >= 1 && $hst <= 14) {
                $priorities[] = [
                    'id'           => 'hst_vegetative_early',
                    'type'         => 'cultivation_step',
                    'urgency'      => 'info',
                    'title'        => "Fase Pemulihan & Anakan Awal (HST {$hst})",
                    'subtitle'     => 'Jaga kedalaman air macak-macak (1-2 cm). Persiapkan pemupukan susulan pertama.',
                    'action_label' => 'Hitung Dosis Pupuk',
                    'route'        => '/fertilizer',
                    'icon'         => 'spa',
                ];
            } elseif ($hst >= 15 && $hst <= 35) {
                $priorities[] = [
                    'id'           => 'hst_vegetative_active',
                    'type'         => 'cultivation_step',
                    'urgency'      => 'info',
                    'title'        => "Fase Pembentukan Anakan Maksimal (HST {$hst})",
                    'subtitle'     => 'Waktu optimal aplikasi Urea dan NPK susulan ke-2. Lakukan penyiangan gulma.',
                    'action_label' => 'Kalkulator Pupuk',
                    'route'        => '/fertilizer',
                    'icon'         => 'grass',
                ];
            } elseif ($hst >= 36 && $hst <= 65) {
                $priorities[] = [
                    'id'           => 'hst_generative_booting',
                    'type'         => 'cultivation_step',
                    'urgency'      => 'info',
                    'title'        => "Fase Bunting / Primordia Malai (HST {$hst})",
                    'subtitle'     => 'Hindari kekeringan lahan. Amati serangan hama wereng dan penggerek batang di pangkal rumpun.',
                    'action_label' => 'Periksa Daun & Batang',
                    'route'        => '/plant-check',
                    'icon'         => 'energy_savings_leaf',
                ];
            } elseif ($hst >= 66 && $hst <= 90) {
                $priorities[] = [
                    'id'           => 'hst_grain_filling',
                    'type'         => 'cultivation_step',
                    'urgency'      => 'info',
                    'title'        => "Fase Pengisian Butir Gabah (HST {$hst})",
                    'subtitle'     => 'Jaga air berselang (intermittent). Antisipasi serangan burung dan walang sangit.',
                    'action_label' => 'Cek Radar Komunitas',
                    'route'        => '/community-alert',
                    'icon'         => 'grain',
                ];
            } elseif ($hst > 90) {
                $priorities[] = [
                    'id'           => 'hst_near_harvest',
                    'type'         => 'harvest_ready',
                    'urgency'      => 'success',
                    'title'        => "Persiapan Panen Raya (HST {$hst})",
                    'subtitle'     => 'Keringkan sawah 7-10 hari sebelum panen. Pasarkan gabah Anda di Bursa Panen untuk harga optimal.',
                    'action_label' => 'Buat Listing Penjualan',
                    'route'        => '/marketplace/create',
                    'icon'         => 'storefront',
                ];
            }
        }

        // 6. Routine Leaf Inspection fallback (if priorities is empty)
        if (empty($priorities)) {
            $priorities[] = [
                'id'           => 'routine_inspection',
                'type'         => 'routine',
                'urgency'      => 'success',
                'title'        => 'Kondisi Lahan Terkendali & Optimal',
                'subtitle'     => 'Tidak ada ancaman penyakit aktif. Lakukan pemeriksaan visual rutin setiap pagi atau sore.',
                'action_label' => 'Ambil Foto Daun',
                'route'        => '/plant-check',
                'icon'         => 'check_circle',
            ];
        }

        return ApiResponse::success('Daftar prioritas tindakan harian berhasil diambil.', [
            'farm_id'    => $farm->id,
            'farm_name'  => $farm->name,
            'hst'        => $hst,
            'priorities' => $priorities,
        ]);
    }
}
