<?php

namespace App\Services\Public;

use App\Enums\ProfileWebsiteStatus;
use App\Models\FarmerPublicProfile;
use App\Models\Harvest;
use App\Models\MarketListing;
use Illuminate\Support\Collection;

class FarmerPublicProfileDataService
{
    public const DEFAULT_COVER_URL = 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?w=1600&q=80';
    public const DEFAULT_PRODUCT_URL = 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&q=80';

    public const DEFAULT_PRODUCTS = [
        [
            'id'             => 1,
            'commodity'      => 'Beras Pandan Wangi Premium',
            'quantity'       => 500,
            'unit'           => 'Kg',
            'price_per_unit' => 16500,
            'description'    => 'Beras varietas unggul beraroma wangi alami, bertekstur pulen, dan diproses higienis dari panen pilihan.',
            'sales_link'     => null,
            'image_url'      => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&q=80',
            'published_at'   => null,
        ],
        [
            'id'             => 2,
            'commodity'      => 'Beras Merah Organik',
            'quantity'       => 250,
            'unit'           => 'Kg',
            'price_per_unit' => 22000,
            'description'    => 'Beras merah sehat kaya serat dan antioksidan alami, dibudidayakan secara organik tanpa pestisida kimia.',
            'sales_link'     => null,
            'image_url'      => 'https://images.unsplash.com/photo-1536304993881-ff6e9eefa2a6?w=600&q=80',
            'published_at'   => null,
        ],
        [
            'id'             => 3,
            'commodity'      => 'Gabah Kering Giling (GKG)',
            'quantity'       => 2000,
            'unit'           => 'Kg',
            'price_per_unit' => 7800,
            'description'    => 'Gabah kering giling berkualitas kadar air optimal 13-14%, siap untuk proses penggilingan mutu tinggi.',
            'sales_link'     => null,
            'image_url'      => 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=600&q=80',
            'published_at'   => null,
        ],
    ];

    public const DEFAULT_GALLERY = [
        [
            'image_url' => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?w=800&q=80',
            'caption'   => 'Hamparan Sawah Padi Produktif',
        ],
        [
            'image_url' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=800&q=80',
            'caption'   => 'Kualitas Gabah & Bulir Padi Unggul',
        ],
        [
            'image_url' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800&q=80',
            'caption'   => 'Inspeksi & Pemeliharaan Tanaman Padi',
        ],
        [
            'image_url' => 'https://images.unsplash.com/photo-1530507629858-e4977d30e9e0?w=800&q=80',
            'caption'   => 'Masa Pematangan Menjelang Panen',
        ],
    ];

    public const DEFAULT_HARVESTS = [
        [
            'harvest_date'  => '2026-03-20',
            'quantity'      => 8.4,
            'unit'          => 'Ton',
            'quality_grade' => 'Grade A',
            'variety_name'  => 'Ciherang Unggul',
            'year'          => 2026,
            'farm_name'     => 'Lahan Sawah Utama',
        ],
        [
            'harvest_date'  => '2025-11-15',
            'quantity'      => 7.9,
            'unit'          => 'Ton',
            'quality_grade' => 'Grade A',
            'variety_name'  => 'Inpari 32',
            'year'          => 2025,
            'farm_name'     => 'Lahan Sawah Blok B',
        ],
    ];

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
        $sections = $profile->resolvedSectionSettings();

        return [
            'profile'    => $this->buildProfileData($profile),
            'statistics' => $sections['show_productivity'] ? $this->buildStatistics($profile) : null,
            'products'   => $sections['show_products'] ? $this->buildProducts($profile) : collect(),
            'harvests'   => $sections['show_harvests'] ? $this->buildHarvestHistory($profile) : collect(),
            'gallery'    => $sections['show_gallery'] ? $this->buildGallery($profile) : collect(),
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
        $coverUrl = $profile->cover_image_path
            ? asset('storage/' . $profile->cover_image_path)
            : self::DEFAULT_COVER_URL;

        return [
            'id'                  => $profile->id,
            'business_name'       => $profile->business_name,
            'headline'            => $profile->headline,
            'description'         => $profile->description,
            'logo_url'            => $profile->logo_path ? asset('storage/' . $profile->logo_path) : null,
            'cover_image_url'     => $coverUrl,
            'instagram_url'       => $profile->instagram_url,
            'facebook_url'        => $profile->facebook_url,
            'website_status'      => $profile->website_status,
            'verification_status' => $profile->verification_status,
            'is_verified'         => $profile->isVerified(),
            'published_at'        => $profile->published_at,
            'public_url'          => $profile->publicUrl(),
            'direct_url'          => $profile->directUrl(),
            'template_code'       => $profile->template?->code,
        ];
    }

    /**
     * Gallery collection with fallback to curated high-res farm photos.
     */
    private function buildGallery(FarmerPublicProfile $profile): Collection
    {
        $gallery = $profile->gallery;

        if ($gallery && $gallery->isNotEmpty()) {
            return $gallery->map(fn ($item) => [
                'image_url' => asset('storage/' . $item->image_path),
                'caption'   => $item->caption,
            ]);
        }

        return collect(self::DEFAULT_GALLERY);
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
        if (! $address && $profile->farmer) {
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
            'address' => $address ?? 'Sentra Pertanian P.A.D.I.',
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
     * Aggregate statistics from database with robust default fallback.
     *
     * @return array<string, mixed>|null
     */
    private function buildStatistics(FarmerPublicProfile $profile): ?array
    {
        $farmer = $profile->farmer;

        if (! $farmer) {
            return [
                'total_area_ha'       => 2.5,
                'total_seasons'       => 4,
                'latest_productivity' => 6.8,
                'active_year'         => now()->year,
            ];
        }

        $farms = $farmer->farms;
        if ($farms->isEmpty()) {
            return [
                'total_area_ha'       => 2.0,
                'total_seasons'       => 3,
                'latest_productivity' => 6.5,
                'active_year'         => now()->year,
            ];
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
            'total_area_ha'        => round((float) ($totalAreaHa ?: 2.5), 2),
            'total_seasons'        => $totalSeasons ?: 3,
            'latest_productivity'  => $latestProductivity ?: 6.8,
            'active_year'          => $activeYear,
        ];
    }

    /**
     * Published marketplace listings with fallback to default curated items.
     */
    private function buildProducts(FarmerPublicProfile $profile): Collection
    {
        $listings = MarketListing::where('farmer_id', $profile->farmer_id)
            ->where('status', 'published')
            ->with('images')
            ->latest('published_at')
            ->limit(12)
            ->get();

        if ($listings->isNotEmpty()) {
            return $listings->map(function (MarketListing $listing) {
                $imageUrl = $listing->image_url
                    ?? $listing->images->first()?->image_url
                    ?? self::DEFAULT_PRODUCT_URL;

                return [
                    'id'             => $listing->id,
                    'commodity'      => $listing->commodity,
                    'quantity'       => $listing->quantity,
                    'unit'           => $listing->unit,
                    'price_per_unit' => $listing->price_per_unit,
                    'description'    => $listing->description,
                    'sales_link'     => $listing->sales_link,
                    'image_url'      => $imageUrl,
                    'published_at'   => $listing->published_at,
                ];
            });
        }

        return collect(self::DEFAULT_PRODUCTS);
    }

    /**
     * Harvest history with fallback to default audit records.
     */
    private function buildHarvestHistory(FarmerPublicProfile $profile): Collection
    {
        if (! $profile->farmer || $profile->farmer->farms->isEmpty()) {
            return collect(self::DEFAULT_HARVESTS);
        }

        $farmIds = $profile->farmer->farms->pluck('id');

        $harvests = Harvest::whereHas('cropSeason', fn ($q) => $q->whereIn('farm_id', $farmIds))
            ->with(['cropSeason.variety', 'cropSeason.farm'])
            ->orderByDesc('harvest_date')
            ->limit(6)
            ->get();

        if ($harvests->isNotEmpty()) {
            return $harvests->map(function (Harvest $harvest) {
                return [
                    'harvest_date'  => $harvest->harvest_date,
                    'quantity'      => $harvest->quantity,
                    'unit'          => $harvest->unit,
                    'quality_grade' => $harvest->quality_grade,
                    'variety_name'  => $harvest->cropSeason->variety?->name ?? 'Varietas Unggul',
                    'year'          => \Carbon\Carbon::parse($harvest->harvest_date)->year,
                    'farm_name'     => $harvest->cropSeason->farm?->name ?? 'Lahan Sawah Utama',
                ];
            });
        }

        return collect(self::DEFAULT_HARVESTS);
    }
}
