<?php

namespace App\Services;

use App\Models\AlertSubscription;
use App\Models\CropSeason;
use App\Models\DiseaseScan;
use App\Models\Farm;
use App\Models\MarketListing;
use App\Models\User;
use App\Services\Geography\LocationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FarmService
{
    public function __construct(
        private LocationService $locationService
    ) {}

    /**
     * Get farms for user (or all if admin).
     */
    public function getFarms(User $user): Collection
    {
        return Farm::query()
            ->when(! $user->hasRole('admin'), function ($query) use ($user): void {
                $query->where('farmer_user_id', $user->id);
            })
            ->with(['province', 'regency', 'district', 'village'])
            ->latest('id')
            ->get();
    }

    /**
     * Create new farm and resolve location if needed.
     *
     * @param array<string, mixed> $data
     */
    public function createFarm(User $user, array $data): Farm
    {
        $data['farmer_user_id'] = $user->id;

        // Auto-resolve region if not explicitly provided
        if (empty($data['district_id']) && empty($data['village_id']) && isset($data['latitude'], $data['longitude'])) {
            $resolved = $this->locationService->resolveCoordinates(
                (float) $data['latitude'],
                (float) $data['longitude']
            );

            if ($resolved) {
                $data['province_id'] = $resolved['province']['id'] ?? $data['province_id'] ?? null;
                $data['regency_id']  = $resolved['regency']['id'] ?? $data['regency_id'] ?? null;
                $data['district_id'] = $resolved['district']['id'] ?? null;
                $data['village_id']  = $resolved['village']['id'] ?? null;
            }
        }

        $farm = Farm::create($data);
        $farm->load(['province', 'regency', 'district', 'village']);

        return $farm;
    }

    /**
     * Update farm and re-resolve location if coordinates changed.
     *
     * @param array<string, mixed> $data
     */
    public function updateFarm(Farm $farm, array $data): Farm
    {
        // If coordinates changed and region not explicitly set, auto-resolve again
        if ((isset($data['latitude']) || isset($data['longitude'])) && empty($data['district_id'])) {
            $lat = $data['latitude'] ?? $farm->latitude;
            $lng = $data['longitude'] ?? $farm->longitude;

            $resolved = $this->locationService->resolveCoordinates((float) $lat, (float) $lng);
            if ($resolved) {
                $data['province_id'] = $resolved['province']['id'] ?? $farm->province_id;
                $data['regency_id']  = $resolved['regency']['id'] ?? $farm->regency_id;
                $data['district_id'] = $resolved['district']['id'] ?? null;
                $data['village_id']  = $resolved['village']['id'] ?? null;
            }
        }

        $farm->update($data);
        $farm->load(['province', 'regency', 'district', 'village']);

        return $farm;
    }

    /**
     * Delete farm and clean up all related entities in transaction.
     */
    public function deleteFarm(Farm $farm): void
    {
        DB::transaction(function () use ($farm) {
            // Bersihkan atau lepaskan relasi terkait agar tidak melanggar foreign key constraint
            $farm->irrigationSchedules()->delete();
            $farm->soilDetections()->delete();
            $farm->weatherSnapshots()->delete();
            AlertSubscription::where('farm_id', $farm->id)->delete();
            $farm->cropSeasons()->delete();
            DiseaseScan::where('farm_id', $farm->id)->update(['farm_id' => null]);
            MarketListing::where('farm_id', $farm->id)->update(['farm_id' => null]);

            $farm->delete();
        });
    }
}