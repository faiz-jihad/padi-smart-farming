<?php

namespace App\Services\Public;

use App\Enums\ProfileWebsiteStatus;
use App\Models\FarmerPublicProfile;
use App\Models\Harvest;
use App\Models\MarketListing;
use Illuminate\Support\Collection;

class FarmerPublicProfileDataService
{
    /**
     * Build the full public data payload for a published farmer profile.
     * Only includes sections the farmer has opted-in to show.
     *
     * NEVER exposes: NIK, passwords, internal emails, coordinates, API tokens,
     * audit logs, cost structures, or any internal data.
     *
     * @return array<string, mixed>
     */
    public function buildPublicData(FarmerPublicProfile $profile): array
    {
        $farmer = $profile->farmer;
        $sections = $profile->resolvedSectionSettings();

        return [
            'profile'    => $this->buildProfileData($profile),
            'statistics' => $sections['show_productivity'] ? $this->buildStatistics($profile) : null,
            'products'   => $sections['show_products'] ? $this->buildProducts($profile) : collect(),
            'harvests'   => $sections['show_harvests'] ? $this->buildHarvestHistory($profile) : collect(),
            'gallery'    => $sections['show_gallery'] ? $profile->gallery : collect(),
            'location'   => $sections['show_location'] ? $this->buildPublicLocation($profile) : null,
            'contact'    => $sections['show_contact'] ? $this->buildContactData($profile) : null,
            'sections'   => $sections,
        ];
    }

    /**
     * Public-safe profile data — ONLY whitelisted fields.
     *
     * @return array<string, mixed>
     */
    private function buildProfileData(FarmerPublicProfile $profile): array
    {
        return [
            'id'                  => $profile->id,
            'business_name'       => $profile->business_name,
            'headline'            => $profile->headline,
            'description'         => $profile->description,
            'logo_url'            => $profile->logo_path ? asset('storage/' . $profile->logo_path) : null,
            'cover_image_url'     => $profile->cover_image_path ? asset('storage/' . $profile->cover_image_path) : null,
            'instagram_url'       => $profile->instagram_url,
            'facebook_url'        => $profile->facebook_url,
            'website_status'      => $profile->website_status,
            'verification_status' => $profile->verification_status,
            'is_verified'         => $profile->isVerified(),
            'published_at'        => $profile->published_at,
            'public_url'          => $profile->publicUrl(),
            'template_code'       => $profile->template?->code,
        ];
    }

    /**
     * General public location — never expose precise coordinates.
     *
     * @return array<string, string|null>|null
     */
    private function buildPublicLocation(FarmerPublicProfile $profile): ?array
    {
        $address = $profile->public_address;

        // Try to derive from farm region data if address not set
        if (! $address) {
            $farm = $profile->farmer->farms()->with(['regency', 'province'])->first();
            if ($farm) {
                $parts = array_filter([
                    $farm->regency?->name,
                    $farm->province?->name,
                ]);
                $address = implode(', ', $parts) ?: null;
            }
        }

        return [
            'address' => $address,
            // Never expose latitude/longitude here
        ];
    }

    /**
     * Public-safe contact info — only what farmer explicitly opted in.
     *
     * @return array<string, string|null>|null
     */
    private function buildContactData(FarmerPublicProfile $profile): ?array
    {
        return [
            'whatsapp'      => $profile->whatsapp ? $profile->whatsappUrl() : null,
            'public_email'  => $profile->public_email,
            'public_phone'  => $profile->public_phone,
        ];
    }

    /**
     * Aggregate statistics from database — no dummy data in production.
     *
     * @return array<string, mixed>|null
     */
    private function buildStatistics(FarmerPublicProfile $profile): ?array
    {
        $farmer = $profile->farmer;

        $farms = $farmer->farms;
        if ($farms->isEmpty()) {
            return null;
        }

        $totalAreaHa = $farms->sum('area_ha');
        $farmIds = $farms->pluck('id');

        // Total harvest seasons
        $totalSeasons = \App\Models\CropSeason::whereIn('farm_id', $farmIds)->count();

        // Latest harvest productivity (ton/ha)
        $latestHarvest = Harvest::whereHas('cropSeason', fn ($q) => $q->whereIn('farm_id', $farmIds))
            ->with('cropSeason.farm')
            ->latest('harvest_date')
            ->first();

        $latestProductivity = null;
        if ($latestHarvest && $latestHarvest->unit === 'ton') {
            $farmArea = $latestHarvest->cropSeason->farm->area_ha ?? 1;
            $latestProductivity = $farmArea > 0
                ? round($latestHarvest->quantity / $farmArea, 2)
                : null;
        }

        $activeYear = now()->year;

        return [
            'total_area_ha'        => round((float) $totalAreaHa, 2),
            'total_seasons'        => $totalSeasons,
            'latest_productivity'  => $latestProductivity,
            'active_year'          => $activeYear,
        ];
    }

    /**
     * Only published marketplace listings — never draft/rejected.
     */
    private function buildProducts(FarmerPublicProfile $profile): Collection
    {
        return MarketListing::where('farmer_id', $profile->farmer_id)
            ->where('status', 'published')
            ->with('images')
            ->latest('published_at')
            ->limit(12)
            ->get()
            ->map(function (MarketListing $listing) {
                // Explicit whitelist — never serialize full model
                return [
                    'id'             => $listing->id,
                    'commodity'      => $listing->commodity,
                    'quantity'       => $listing->quantity,
                    'unit'           => $listing->unit,
                    'price_per_unit' => $listing->price_per_unit,
                    'description'    => $listing->description,
                    'sales_link'     => $listing->sales_link,
                    'image_url'      => $listing->image_url
                        ?? $listing->images->first()?->image_url,
                    'published_at'   => $listing->published_at,
                ];
            });
    }

    /**
     * Harvest history — no financial/cost details.
     */
    private function buildHarvestHistory(FarmerPublicProfile $profile): Collection
    {
        $farmIds = $profile->farmer->farms->pluck('id');

        return Harvest::whereHas('cropSeason', fn ($q) => $q->whereIn('farm_id', $farmIds))
            ->with(['cropSeason.variety', 'cropSeason.farm'])
            ->orderByDesc('harvest_date')
            ->limit(6)
            ->get()
            ->map(function (Harvest $harvest) {
                // Explicit whitelist — no financial details
                return [
                    'harvest_date'  => $harvest->harvest_date,
                    'quantity'      => $harvest->quantity,
                    'unit'          => $harvest->unit,
                    'quality_grade' => $harvest->quality_grade,
                    'variety_name'  => $harvest->cropSeason->variety?->name,
                    'year'          => \Carbon\Carbon::parse($harvest->harvest_date)->year,
                    'farm_name'     => $harvest->cropSeason->farm?->name,
                    // NEVER expose: moisture_percent, verification_status, cost data
                ];
            });
    }
}
