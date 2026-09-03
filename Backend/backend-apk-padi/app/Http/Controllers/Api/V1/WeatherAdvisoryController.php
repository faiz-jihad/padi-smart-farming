<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CropSeason;
use App\Models\Farm;
use App\Models\WeatherSnapshot;
use App\Services\Weather\WeatherService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeatherAdvisoryController extends Controller
{
    public function __construct(
        private WeatherService $weather
    ) {}

    /**
     * Get agronomic weather advisory tailored to farm stage (HST).
     */
    public function index(Request $request, Farm $farm): JsonResponse
    {
        $user = $request->user();

        // 1. Coordinates
        $lat = (float) ($farm->latitude ?? -6.3264); // Default Indramayu
        $lon = (float) ($farm->longitude ?? 108.3200);

        // 2. Fetch or fallback weather
        $currentWeather = $this->weather->getCurrentWeather($lat, $lon);
        $wData = $currentWeather['data'] ?? [];
        $main = $wData['main'] ?? [];
        $temp = (float) ($main['temp'] ?? 29.0);
        $humidity = (int) ($main['humidity'] ?? 78);
        $windSpeed = (float) ($wData['wind']['speed'] ?? 3.5);
        $weatherDesc = $wData['weather'][0]['description'] ?? 'Cerah Berawan';

        // 3. Active Season & HST
        $activeSeason = CropSeason::where('farm_id', $farm->id)
            ->where('status', 'active')
            ->with('variety')
            ->latest('id')
            ->first();

        $hst = 0;
        $phaseName = 'Pra-Tanam';
        if ($activeSeason) {
            $pDate = $activeSeason->planting_date ? Carbon::parse($activeSeason->planting_date) : null;
            if ($pDate) {
                $hst = max(0, (int) $pDate->diffInDays(now(), false));
            }
        }

        // 4. Agronomy Rule Engine
        $advisories = [];
        $voiceText = '';

        if ($hst >= 0 && $hst <= 14) {
            $phaseName = 'Vegetatif Awal (Pemulihan)';
            if ($humidity > 85) {
                $advisories[] = [
                    'severity'     => 'warning',
                    'title'        => 'Risiko Busuk Batang Awal',
                    'action'       => 'Kurangi genangan air menjadi macak-macak (1 cm). Jangan pupuk saat hujan lebat.',
                    'recommended'  => 'Periksa pangkal bibit 2 hari sekali',
                ];
                $voiceText = "Fase vegetatif awal hari ke $hst. Kelembaban tinggi $humidity persen. Jaga air sawah macak-macak satu sentimeter dan tunda pemupukan jika hujan lebat.";
            } else {
                $advisories[] = [
                    'severity'     => 'good',
                    'title'        => 'Kondisi Optimal Pertumbuhan Akar',
                    'action'       => 'Jaga ketinggian air 2-3 cm untuk membantu anakan baru berakar kuat.',
                    'recommended'  => 'Siapkan pupuk Urea dan NPK untuk HST ke-7',
                ];
                $voiceText = "Cuaca bersahabat pada fase vegetatif hari ke $hst. Pertahankan air dua sentimeter untuk perakaran optimal.";
            }
        } elseif ($hst >= 15 && $hst <= 35) {
            $phaseName = 'Anakan Aktif (Vegetatif)';
            if ($temp >= 33) {
                $advisories[] = [
                    'severity'     => 'warning',
                    'title'        => 'Evapotranspirasi Tinggi',
                    'action'       => 'Alirkan air pagi hari sebelum pukul 08.00 untuk menjaga kelembaban perakaran.',
                    'recommended'  => 'Hindari aplikasi herbisida pada siang terik',
                ];
                $voiceText = "Suhu panas $temp derajat celcius pada fase anakan aktif. Alirkan air sebelum jam delapan pagi agar anakan padi tidak layu.";
            } else {
                $advisories[] = [
                    'severity'     => 'good',
                    'title'        => 'Waktu Tepat Pemupukan Susulan',
                    'action'       => 'Aplikasi NPK dan Urea susulan ke-2 sangat optimal dalam 3 hari ke depan.',
                    'recommended'  => 'Gunakan dosis berimbang sesuai rekomendasi kalkulator',
                ];
                $voiceText = "Kondisi cuaca sangat baik untuk pemupukan susulan fase anakan aktif hari ke $hst.";
            }
        } elseif ($hst >= 36 && $hst <= 65) {
            $phaseName = 'Bunting / Primordia (Generatif)';
            if ($humidity >= 80) {
                $advisories[] = [
                    'severity'     => 'urgent',
                    'title'        => 'Waspada Penyakit Blas & Hawar Daun',
                    'action'       => 'Amati daun bendera dan pelepah. Semprot fungisida pencegah bila ada bercak cokelat.',
                    'recommended'  => 'Pantau petak sawah setiap pagi',
                ];
                $voiceText = "Peringatan fase bunting hari ke $hst. Kelembaban tinggi rawan jamur blas. Segera periksa daun bendera tanaman padi Anda.";
            } else {
                $advisories[] = [
                    'severity'     => 'good',
                    'title'        => 'Pembentukan Malai Serempak',
                    'action'       => 'Pastikan air tidak pernah kering (3-5 cm) untuk mendukung pengisian bunga padi.',
                    'recommended'  => 'Cegah serangan penggerek batang',
                ];
                $voiceText = "Fase bunting hari ke $hst membutuhkan pasokan air konsisten tiga sentimeter untuk malai yang seragam.";
            }
        } elseif ($hst >= 66 && $hst <= 90) {
            $phaseName = 'Pengisian Bulir (Pematangan)';
            if ($windSpeed > 15) {
                $advisories[] = [
                    'severity'     => 'warning',
                    'title'        => 'Angin Kencang Berpotensi Rebah',
                    'action'       => 'Terapkan pengairan berselang (intermittent) agar perakaran mencengkeram tanah lebih kuat.',
                    'recommended'  => 'Waspadai walang sangit saat senja',
                ];
                $voiceText = "Kecepatan angin mencapai $windSpeed kilometer per jam pada fase pengisian bulir. Lakukan pengairan berselang agar batang tetap kokoh.";
            } else {
                $advisories[] = [
                    'severity'     => 'good',
                    'title'        => 'Pengisian Bulir Maksimal',
                    'action'       => 'Pertahankan tanah tetap basah tanpa genangan tinggi.',
                    'recommended'  => 'Pasang penghalau hama burung di sekitar petak',
                ];
                $voiceText = "Bulir padi sedang mengisi secara optimal. Jaga tanah tetap lembab dan pasang pelindung dari hama burung.";
            }
        } else {
            $phaseName = 'Pra-Panen / Pematangan Penuh';
            $advisories[] = [
                'severity'     => 'good',
                'title'        => 'Persiapan Panen Raya',
                'action'       => 'Keringkan sawah 7-10 hari sebelum hari panen untuk memadatkan tanah dan mematangkan gabah merata.',
                'recommended'  => 'Pantau harga gabah terkini di Bursa Panen',
            ];
            $voiceText = "Sawah siap panen. Keringkan petak sawah seminggu sebelum panen dan pasarkan gabah Anda di bursa.";
        }

        return ApiResponse::success('Saran cuaca & agroklimat berhasil dihitung.', [
            'farm_id'       => $farm->id,
            'farm_name'     => $farm->name,
            'hst'           => $hst,
            'phase_name'    => $phaseName,
            'weather'       => [
                'temp'        => $temp,
                'humidity'    => $humidity,
                'wind_speed'  => $windSpeed,
                'description' => $weatherDesc,
            ],
            'voice_text'    => $voiceText,
            'advisories'    => $advisories,
        ]);
    }
}
