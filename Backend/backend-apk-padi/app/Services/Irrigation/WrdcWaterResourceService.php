<?php

namespace App\Services\Irrigation;

use App\Models\Farm;
use Illuminate\Support\Facades\Cache;

/**
 * Service untuk menyusun Data Resmi Daerah Irigasi & Infrastruktur SDA
 * berdasarkan data spasial GEOAPI PUSDATIN Kementerian Pekerjaan Umum.
 */
class WrdcWaterResourceService
{
    public function __construct(
        protected PuGeoApiService $puGeoApiService
    ) {}

    /**
     * Mengambil data konteks resmi Daerah Irigasi & Infrastruktur SDA untuk lahan
     *
     * @return array<string, mixed>
     */
    public function getOfficialContextForFarm(Farm $farm): array
    {
        $cacheKey = "wrdc.context.farm_{$farm->id}";

        return Cache::remember($cacheKey, 86400, function () use ($farm) {
            $lat = (float) ($farm->latitude ?? 0);
            $lon = (float) ($farm->longitude ?? 0);

            // Jika koordinat tidak valid (0, 0), langsung gunakan fallback.
            if ($lat === 0.0 && $lon === 0.0) {
                return $this->resolveFallbackContext($farm);
            }

            // Ambil seluruh layer spasial resmi dari GEOAPI PU.
            $di = $this->puGeoApiService->getIrrigationAreasByPoint($lat, $lon);

            $waterAvailability = $this->puGeoApiService
                ->getWaterAvailabilityByPoint($lat, $lon);

            $waterDemand = $this->puGeoApiService
                ->getWaterDemandByPoint($lat, $lon);

            $waterBalance = $this->puGeoApiService
                ->getWaterBalanceByPoint($lat, $lon);

            $nearestDam = $this->puGeoApiService
                ->getNearestDam($lat, $lon);

            /*
             * API dianggap berhasil memberikan konteks resmi apabila
             * minimal satu layer resmi PU berhasil ditemukan.
             *
             * DI tidak ditemukan TIDAK berarti API gagal.
             */
            $hasAnyPuData =
                ($di !== null) ||
                ($waterAvailability !== null) ||
                ($waterDemand !== null) ||
                ($waterBalance !== null) ||
                ($nearestDam !== null);

            /*
             * Tidak ada satu pun data resmi PU.
             * Ini baru dianggap sebagai fallback.
             */
            if (! $hasAnyPuData) {
                return $this->resolveFallbackContext($farm);
            }

            $irrType = strtolower(
                (string) ($farm->irrigation_type ?? 'technical')
            );

            /*
             * ==========================================================
             * KONDISI 1: FARM BERADA DI DALAM POLYGON DI RESMI PU
             * ==========================================================
             */
            if ($di !== null) {
                $diName = $di['nm_inf']
                    ?? 'Daerah Irigasi Terdaftar PU';

                $diCode = $di['kd_inf'] ?? null;

                $authority = $di['kewenangan']
                    ?? 'Kementerian Pekerjaan Umum';

                $balai = $di['nm_balai'] ?? null;

                $primarySource = ! empty($di['smbr_air'])
                    ? $di['smbr_air']
                    : (
                        $nearestDam
                            ? "Bendung {$nearestDam['nama_infrastruktur']}"
                            : 'Jaringan Irigasi Resmi PU'
                    );

                $schemeType = ! empty($di['jenis_di'])
                    ? $di['jenis_di']
                    : $this->formatSchemeType($irrType);

                $serviceArea = $di['luas_ha']
                    ?? ($di['luas_fung'] ?? null);

                $supplyStatus = ! empty($di['kondisi'])
                    ? "Kondisi Jaringan: {$di['kondisi']}"
                    : 'Tersedia Normal (Jaringan Irigasi Resmi PU)';

                $diNotice = 'Lokasi farm tercakup dalam polygon Daerah Irigasi resmi PU.';
            }

            /*
             * ==========================================================
             * KONDISI 2: DI TIDAK DITEMUKAN, TAPI LAYER PU LAIN ADA
             * ==========================================================
             *
             * Ini adalah kondisi farm saat ini:
             *
             * DI                 -> null
             * Ketersediaan Air   -> ada
             * Kebutuhan Air      -> ada
             * Neraca Air         -> ada
             * Bendung            -> ada
             *
             * Artinya API hidup dan memberikan data resmi,
             * tetapi koordinat farm tidak berada di polygon DI.
             */
            else {
                $diName = 'Tidak tercakup polygon Daerah Irigasi PU';

                $diCode = null;

                $authority = $nearestDam['kewenangan']
                    ?? 'Kementerian Pekerjaan Umum';

                $balai = $nearestDam['nm_balai'] ?? null;

                $primarySource = $nearestDam
                    ? "Bendung {$nearestDam['nama_infrastruktur']}"
                    : 'Sumber Air Permukaan Terdekat';

                $schemeType = $this->formatSchemeType($irrType);

                $serviceArea = null;

                $supplyStatus = $nearestDam
                    ? 'Tersedia dari Bendung Terdekat'
                    : 'Belum tercakup polygon Daerah Irigasi resmi PU.';

                $diNotice =
                    'GEOAPI PUSDATIN berhasil diakses, tetapi koordinat farm '
                    . 'tidak berada di dalam polygon Daerah Irigasi Permukaan '
                    . 'resmi PU.';
            }

            return [
                'is_available' => true,

                // True karena minimal satu layer resmi PU berhasil.
                'is_live_api' => true,

                'provider' =>
                    'Kementerian Pekerjaan Umum / PUSDATIN GEOAPI',

                'daerah_irigasi' => $diName,

                'di_code' => $diCode,

                'authority' => $authority,

                'bbws_bws' => $balai,

                'primary_source' => $primarySource,

                'scheme_type' => $schemeType,

                'service_area_ha' =>
                    $serviceArea !== null
                        ? (float) $serviceArea
                        : null,

                'water_supply_status' => $supplyStatus,

                'water_availability' => $waterAvailability,

                'water_demand' => $waterDemand,

                'water_balance' => $waterBalance,

                'nearest_dam' => $nearestDam,

                'distance_to_dam_km' =>
                    $nearestDam['distance_km'] ?? null,

                'integration_status' => 'pu_geoapi',

                'notice' => $diNotice,
            ];
        });
    }

    /**
     * Fallback context yang aman jika GEOAPI tidak dapat diakses
     * atau tidak mengembalikan data sama sekali.
     *
     * @return array<string, mixed>
     */
    public function resolveFallbackContext(Farm $farm): array
    {
        $irrType = strtolower(
            (string) ($farm->irrigation_type ?? 'technical')
        );

        return [
            'is_available' => true,

            'is_live_api' => false,

            'provider' =>
                'Kementerian Pekerjaan Umum / PUSDATIN GEOAPI',

            'daerah_irigasi' =>
                'Data resmi DI belum tersedia',

            'di_code' => null,

            'authority' =>
                'Kementerian Pekerjaan Umum / Dinas PUPR Daerah',

            'bbws_bws' => null,

            'primary_source' =>
                'Jaringan Irigasi / Saluran Air Pertanian',

            'scheme_type' =>
                $this->formatSchemeType($irrType),

            'service_area_ha' => null,

            'water_supply_status' =>
                'Data resmi belum disinkronkan',

            'water_availability' => null,

            'water_demand' => null,

            'water_balance' => null,

            'nearest_dam' => null,

            'distance_to_dam_km' => null,

            'integration_status' =>
                'authoritative_wrdc_fallback',

            'notice' =>
                'Koneksi ke GEOAPI PUSDATIN PU belum tersedia '
                . 'atau tidak ada data resmi yang dapat ditemukan.',
        ];
    }

    private function formatSchemeType(string $irrType): string
    {
        return match ($irrType) {
            'technical',
            'teknis',
            'irrigated' =>
                'Irigasi Teknis Gravitasi',

            'semi_technical',
            'semi_teknis' =>
                'Irigasi Setengah Teknis',

            'rainfed',
            'tadah_hujan',
            'hujan' =>
                'Lahan Tadah Hujan',

            'swamp',
            'rawa' =>
                'Irigasi Rawa Pasang Surut',

            default =>
                'Irigasi Permukaan Terkontrol',
        };
    }
}