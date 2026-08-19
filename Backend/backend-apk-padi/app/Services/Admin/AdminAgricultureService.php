<?php

namespace App\Services\Admin;

use App\Models\CropSeason;
use App\Models\Farm;
use App\Models\Harvest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminAgricultureService
{
    /**
     * @return array<string, mixed>
     */
    public function indexData(Request $request): array
    {
        $search = trim((string) $request->query('search', ''));
        $irrigation = trim((string) $request->query('irrigation', ''));

        $farmsQuery = Farm::query()
            ->with(['farmer', 'cropSeasons.variety'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $sub) use ($search): void {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('irrigation_notes', 'like', "%{$search}%")
                        ->orWhereHas('farmer', function (Builder $fq) use ($search): void {
                            $fq->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($irrigation !== '', function (Builder $query) use ($irrigation): void {
                $query->where('irrigation_type', $irrigation);
            });

        $cropSeasonService = app(\App\Services\Agriculture\CropSeasonService::class);
        if (CropSeason::query()->count() === 0) {
            $cropSeasonService->autoGenerateAllFarmsCropSeasons();
        }

        $allCropSeasons = CropSeason::query()
            ->with(['farm.farmer', 'variety'])
            ->latest('id')
            ->get();

        $concreteSchedules = $this->buildConcretePlantingSchedules($allCropSeasons);
        $irrigationAlerts = $this->buildIrrigationAlerts(Farm::with('farmer', 'cropSeasons.variety')->get());

        return [
            'title' => 'Pertanian',
            'farms' => $farmsQuery->latest('id')->paginate(12),
            'cropSeasons' => $allCropSeasons->take(10),
            'concreteSchedules' => $concreteSchedules,
            'irrigationAlerts' => $irrigationAlerts,
            'farmers' => User::query()->orderBy('name')->get(['id', 'name', 'email', 'role']),
            'filters' => [
                'search' => $search,
                'irrigation' => $irrigation,
            ],
            'stats' => [
                'farms' => Farm::query()->count(),
                'area' => (float) Farm::query()->sum('area_ha'),
                'active_seasons' => CropSeason::query()->where('status', 'active')->count(),
                'harvests' => Harvest::query()->count(),
            ],
        ];
    }

    /**
     * Build concrete planting timeline & progress for active crop seasons
     */
    private function buildConcretePlantingSchedules($cropSeasons): array
    {
        $schedules = [];
        $now = now();

        foreach ($cropSeasons as $season) {
            $plantingDateStr = $season->planting_date ?? $season->planned_planting_date;
            if (!$plantingDateStr) {
                continue;
            }

            $plantingDate = \Carbon\Carbon::parse($plantingDateStr);
            $maturityDays = (int) ($season->variety?->maturity_days ?? 115);
            $harvestDate = $plantingDate->copy()->addDays($maturityDays);

            $hst = (int) $plantingDate->diffInDays($now, false);
            if ($hst < 0) {
                // Pra-tanam
                $progressPct = 5;
                $phase = 'Pra-Tanam & Pengolahan Tanah';
                $badgeClass = 'irrigation-lainnya';
                $action = 'Lakukan pembajakan tanah sedalam 20 cm dan persemaian benih (rendam benih 24 jam).';
                $statusLabel = 'Masa Semai (H' . $hst . ')';
            } elseif ($hst <= 30) {
                $progressPct = max(10, min(35, round(($hst / $maturityDays) * 100)));
                $phase = 'Vegetatif Awal (Tanam Pindah)';
                $badgeClass = 'irrigation-teknis';
                $action = 'Jaga ketinggian air 2-3 cm. Berikan pemupukan dasar Urea 50 kg + NPK 100 kg/ha pada 7-10 HST.';
                $statusLabel = "{$hst} HST (Vegetatif)";
            } elseif ($hst <= 55) {
                $progressPct = max(35, min(60, round(($hst / $maturityDays) * 100)));
                $phase = 'Vegetatif Aktif (Anakan Maksimum)';
                $badgeClass = 'irrigation-teknis';
                $action = 'Terapkan sistem pengairan berselang (AWD / macak-macak). Pemupukan susulan kedua Urea 50 kg/ha.';
                $statusLabel = "{$hst} HST (Anakan)";
            } elseif ($hst <= 85) {
                $progressPct = max(60, min(85, round(($hst / $maturityDays) * 100)));
                $phase = 'Generatif / Bunting (Primordia & Heading)';
                $badgeClass = 'irrigation-hujan';
                $action = 'Fase KRITIS air: Genangi air 5 cm. Waspadai serangan penggerek batang dan penyakit blas daun.';
                $statusLabel = "{$hst} HST (Bunting)";
            } elseif ($hst <= $maturityDays) {
                $progressPct = max(85, min(98, round(($hst / $maturityDays) * 100)));
                $phase = 'Pematangan Bulir (Ripening)';
                $badgeClass = 'irrigation-hujan';
                $action = 'Keringkan lahan secara bertahap 10-14 hari sebelum panen untuk mematangkan gabah secara serentak.';
                $statusLabel = "{$hst} HST (Pengisian Bulir)";
            } else {
                $progressPct = 100;
                $phase = 'Siap Panen (Matang Fisiologis)';
                $badgeClass = 'irrigation-rawa';
                $action = 'Panen saat 90-95% bulir menguning. Segera lakukan perontokan dan pengeringan gabah (kadar air target 14%).';
                $statusLabel = 'Siap Panen';
            }

            $daysRemaining = max(0, (int) $now->diffInDays($harvestDate, false));

            $schedules[] = [
                'id' => $season->id,
                'farm_name' => $season->farm?->name ?? 'Lahan',
                'farmer_name' => $season->farm?->farmer?->name ?? 'Petani',
                'variety_name' => $season->variety?->name ?? 'Inpari 32',
                'maturity_days' => $maturityDays,
                'planting_date' => $plantingDate->isoFormat('D MMMM Y'),
                'planting_date_raw' => $plantingDate->format('Y-m-d'),
                'harvest_date' => $harvestDate->isoFormat('D MMMM Y'),
                'harvest_date_raw' => $harvestDate->format('Y-m-d'),
                'hst' => $hst,
                'progress_pct' => $progressPct,
                'phase' => $phase,
                'badge_class' => $badgeClass,
                'status_label' => $statusLabel,
                'days_remaining' => $daysRemaining,
                'action' => $action,
                'season_name' => $season->season_name ?? 'Musim Tanam 2026',
            ];
        }

        return $schedules;
    }

    /**
     * Build smart irrigation notifications and alerts per farm
     */
    private function buildIrrigationAlerts($farms): array
    {
        $alerts = [];

        foreach ($farms as $farm) {
            $type = strtolower($farm->irrigation_type ?? 'lainnya');
            $activeSeason = $farm->cropSeasons->where('status', 'active')->first();
            $variety = $activeSeason?->variety?->name ?? 'Padi';

            if ($type === 'hujan' || $type === 'tadah_hujan') {
                $alerts[] = [
                    'severity' => 'warning',
                    'level_label' => 'Waspada Air',
                    'farm_id' => $farm->id,
                    'farm_name' => $farm->name,
                    'farmer_name' => $farm->farmer?->name ?? 'Petani',
                    'area_ha' => $farm->area_ha,
                    'irrigation_type' => 'Tadah Hujan',
                    'title' => "Peringatan Fluktuasi Air — {$farm->name}",
                    'message' => "Lahan mengandalkan curah hujan. Rekomendasi: monitor kelembaban tanah dan persiapkan pompa air cadangan untuk menjaga kondisi tanah tetap lembab/macak-macak.",
                    'action_label' => 'Jadwalkan Pompa',
                    'status_color' => '#d97706',
                    'bg_color' => '#fef3c7',
                ];
            } elseif ($type === 'swamp' || $type === 'rawa') {
                $alerts[] = [
                    'severity' => 'info',
                    'level_label' => 'Manajemen Drainase',
                    'farm_id' => $farm->id,
                    'farm_name' => $farm->name,
                    'farmer_name' => $farm->farmer?->name ?? 'Petani',
                    'area_ha' => $farm->area_ha,
                    'irrigation_type' => 'Rawa Pasang Surut',
                    'title' => "Kontrol Saluran Pintu Air — {$farm->name}",
                    'message' => "Buka pintu tabat saat surut untuk membuang air asam, dan tutup saat pasang untuk menahan suplai air tawar.",
                    'action_label' => 'Cek Pintu Air',
                    'status_color' => '#2563eb',
                    'bg_color' => '#eff6ff',
                ];
            } else {
                // Irigasi Teknis / Setengah Teknis
                $alerts[] = [
                    'severity' => 'success',
                    'level_label' => 'Irigasi Optimal',
                    'farm_id' => $farm->id,
                    'farm_name' => $farm->name,
                    'farmer_name' => $farm->farmer?->name ?? 'Petani',
                    'area_ha' => $farm->area_ha,
                    'irrigation_type' => 'Irigasi Teknis',
                    'title' => "Suplai Air Terkontrol (AWD) — {$farm->name}",
                    'message' => "Saluran irigasi primer/sekunder aktif. Terapkan rotasi penggenangan 3 cm dan pengeringan 2 hari untuk efisiensi air 25%.",
                    'action_label' => 'Atur Debit',
                    'status_color' => '#166534',
                    'bg_color' => '#f0fdf4',
                ];
            }
        }

        return $alerts;
    }

    /**
     * @param  array{farmer_user_id: int, name: string, area_ha: float, latitude?: float|null, longitude?: float|null, boundary_coordinates?: array|string|null, irrigation_type: string, irrigation_notes?: string|null}  $data
     */
    public function store(
        array $data,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): Farm {
        if (isset($data['boundary_coordinates']) && is_string($data['boundary_coordinates'])) {
            $data['boundary_coordinates'] = json_decode($data['boundary_coordinates'], true);
        }

        $farm = Farm::query()->create($data);
        app(\App\Services\Agriculture\CropSeasonService::class)->autoGenerateCropSeasonsForFarm($farm);

        $audit->write('admin_farm_created', $farm, null, $farm->toArray(), $request);
        $notifications->notifyAdmins('Lahan dibuat', "Lahan {$farm->name} ditambahkan ke sistem.");

        return $farm;
    }

    /**
     * @param  array{farmer_user_id: int, name: string, area_ha: float, latitude?: float|null, longitude?: float|null, boundary_coordinates?: array|string|null, irrigation_type: string, irrigation_notes?: string|null}  $data
     */
    public function update(
        Farm $farm,
        array $data,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): bool {
        if (isset($data['boundary_coordinates']) && is_string($data['boundary_coordinates'])) {
            $data['boundary_coordinates'] = json_decode($data['boundary_coordinates'], true);
        }

        $oldValues = $farm->toArray();
        $farm->update($data);

        $audit->write('admin_farm_updated', $farm, $oldValues, $farm->toArray(), $request);
        $notifications->notifyAdmins('Lahan diperbarui', "Data lahan {$farm->name} telah diperbarui.");

        return true;
    }

    public function destroy(
        Farm $farm,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): bool {
        $oldValues = $farm->toArray();
        $farm->delete();

        $audit->write('admin_farm_deleted', Farm::class, $oldValues, null, $request, $farm->id);
        $notifications->notifyAdmins('Lahan dihapus', "Lahan {$oldValues['name']} telah dihapus dari sistem.");

        return true;
    }
}
