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

        return [
            'title' => 'Pertanian',
            'farms' => $farmsQuery->latest('id')->paginate(12),
            'cropSeasons' => CropSeason::query()->with(['farm.farmer', 'variety'])->latest('id')->limit(10)->get(),
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
