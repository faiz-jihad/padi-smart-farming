<?php

namespace App\Services;

use App\Models\CropSeason;
use App\Models\Farm;
use App\Models\Harvest;
use App\Models\MarketListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class MarketListingService
{
    public function getListings(User $user): Collection
    {
        return PadiCacheService::remember('padi:market:listings_v2', PadiCacheService::TTL_LISTINGS, function () {
            return MarketListing::query()
                ->where('status', 'published')
                ->with([
                    'farmer:id,name,phone,email',
                    'farm:id,name,area_ha,latitude,longitude',
                    'cropSeason:id,variety_id,status',
                    'harvest:id,moisture_percent,quality_grade,quantity',
                    'images:id,market_listing_id,image_url,is_primary',
                    'offers:id,listing_id,partner_id,offered_price,quantity,status',
                ])
                ->latest('published_at')
                ->get();
        });
    }

    public function createListing(
        User $user,
        array $data
    ): MarketListing {
        $farm = Farm::query()
            ->findOrFail($data['farm_id']);

        $this->authorizeFarm($user, $farm);

        $cropSeason = CropSeason::query()
            ->where('id', $data['crop_season_id'])
            ->where('farm_id', $farm->id)
            ->firstOrFail();

        if (! empty($data['harvest_id'])) {
            $harvest = Harvest::query()
                ->where('id', $data['harvest_id'])
                ->where('crop_season_id', $cropSeason->id)
                ->firstOrFail();

            if ((float) $data['quantity'] > (float) $harvest->quantity) {
                abort(
                    422,
                    'Jumlah yang dijual tidak boleh melebihi hasil panen'
                );
            }
        }

        $listingData = $this->prepareListingData($data);

        $listing = MarketListing::query()->create([
            ...$listingData,
            'farmer_id' => $user->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        PadiCacheService::invalidateMarketCache();

        return $listing->load([
            'farmer:id,name,phone,email',
            'farm:id,name,area_ha,latitude,longitude',
            'cropSeason:id,variety_id,status',
            'harvest:id,moisture_percent,quality_grade,quantity',
            'images:id,market_listing_id,image_url,is_primary',
            'offers:id,listing_id,partner_id,offered_price,quantity,status',
        ]);
    }

    public function getListing(
        User $user,
        MarketListing $listing
    ): MarketListing {
        $this->authorizeListing($user, $listing, allowPublicListing: true);

        return $listing->load([
            'farmer:id,name,phone,email',
            'farm:id,name,area_ha,latitude,longitude',
            'cropSeason:id,variety_id,status',
            'harvest:id,moisture_percent,quality_grade,quantity',
            'images:id,market_listing_id,image_url,is_primary',
            'offers:id,listing_id,partner_id,offered_price,quantity,status',
        ]);
    }

    public function updateListing(
        User $user,
        MarketListing $listing,
        array $data
    ): MarketListing {
        $this->authorizeListing($user, $listing);

        if ($listing->status !== 'draft') {
            abort(
                422,
                'Listing yang sudah dipublikasikan tidak dapat diedit'
            );
        }

        if (
            isset($data['quantity'])
            && $listing->harvest_id
        ) {
            $harvest = $listing->harvest;

            if ((float) $data['quantity'] > (float) $harvest->quantity) {
                abort(
                    422,
                    'Jumlah yang dijual tidak boleh melebihi hasil panen'
                );
            }
        }

        $listing->update($this->prepareListingData($data));

        PadiCacheService::invalidateMarketCache();

        return $listing->load([
            'farmer:id,name,phone,email',
            'farm:id,name,area_ha,latitude,longitude',
            'cropSeason:id,variety_id,status',
            'harvest:id,moisture_percent,quality_grade,quantity',
            'images:id,market_listing_id,image_url,is_primary',
            'offers:id,listing_id,partner_id,offered_price,quantity,status',
        ]);
    }

    public function publishListing(
        User $user,
        MarketListing $listing
    ): MarketListing {
        $this->authorizeListing($user, $listing);

        if ($listing->status !== 'draft') {
            abort(422, 'Hanya listing draft yang dapat dipublikasikan');
        }

        $listing->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        PadiCacheService::invalidateMarketCache();

        return $listing->load([
            'farmer:id,name,phone,email',
            'farm:id,name,area_ha,latitude,longitude',
            'cropSeason:id,variety_id,status',
            'harvest:id,moisture_percent,quality_grade,quantity',
        ]);
    }

    public function deleteListing(
        User $user,
        MarketListing $listing
    ): void {
        $this->authorizeListing($user, $listing);

        if ($listing->status !== 'draft') {
            abort(
                422,
                'Hanya listing draft yang dapat dihapus'
            );
        }

        $listing->delete();
        PadiCacheService::invalidateMarketCache();
    }

    private function authorizeFarm(
        User $user,
        Farm $farm
    ): void {
        if (
            ! $user->hasRole('admin')
            && $farm->farmer_user_id !== $user->id
        ) {
            abort(403, 'Anda tidak memiliki akses ke lahan ini');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepareListingData(array $data): array
    {
        if (($data['image'] ?? null) instanceof UploadedFile) {
            $data['image_url'] = $data['image']->store(
                'marketplace',
                'public'
            );
        }

        unset($data['image']);

        return $data;
    }

    private function authorizeListing(
        User $user,
        MarketListing $listing,
        bool $allowPublicListing = false
    ): void {
        if (
            $allowPublicListing
            && in_array($listing->status, ['published', 'sold'], true)
        ) {
            return;
        }

        if (
            ! $user->hasRole('admin')
            && $listing->farmer_id !== $user->id
        ) {
            abort(403, 'Anda tidak memiliki akses ke listing ini');
        }
    }
}
