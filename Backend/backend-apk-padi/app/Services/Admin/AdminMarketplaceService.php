<?php

namespace App\Services\Admin;

use App\Models\MarketListing;
use App\Models\MarketOffer;
use Illuminate\Http\Request;

class AdminMarketplaceService
{
    /**
     * @return array<string, mixed>
     */
    public function indexData(): array
    {
        return [
            'title' => 'Marketplace',
            'listings' => MarketListing::query()->with(['farmer', 'farm', 'offers'])->latest('id')->paginate(10),
            'offers' => MarketOffer::query()->with(['partner', 'listing'])->latest('id')->limit(10)->get(),
            'stats' => [
                'listings' => MarketListing::query()->count(),
                'published' => MarketListing::query()->where('status', 'published')->count(),
                'offers' => MarketOffer::query()->count(),
                'pending_offers' => MarketOffer::query()->where('status', 'pending')->count(),
            ],
        ];
    }

    /**
     * @param  array{status: string}  $data
     */
    public function updateListing(
        MarketListing $listing,
        array $data,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): void {
        $oldValues = $listing->only(['status', 'published_at']);

        if ($data['status'] === 'published' && $listing->published_at === null) {
            $data['published_at'] = now();
        }

        $listing->update($data);

        $audit->write('admin_listing_updated', $listing, $oldValues, $listing->only(['status', 'published_at']), $request);
        $notifications->notifyAdmins('Listing marketplace diperbarui', "{$listing->commodity} menjadi {$listing->status}.");
    }

    /**
     * @param  array{status: string}  $data
     */
    public function updateOffer(
        MarketOffer $offer,
        array $data,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): void {
        $oldValues = $offer->only(['status']);
        $offer->update($data);

        $audit->write('admin_offer_updated', $offer, $oldValues, $offer->only(['status']), $request);
        $notifications->notifyAdmins('Penawaran marketplace diperbarui', "Offer #{$offer->id} menjadi {$offer->status}.");
    }
}
