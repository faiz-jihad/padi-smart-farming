<?php

namespace App\Services\Admin;

use App\Models\Farm;
use App\Models\MarketListing;
use App\Models\MarketOffer;
use App\Models\User;
use Illuminate\Http\Request;

class AdminMarketplaceService
{
    /**
     * @return array<string, mixed>
     */
    public function indexData(Request $request): array
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = MarketListing::query()->with(['farmer', 'farm', 'images']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('commodity', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $listings = $query->latest('id')->paginate(10);

        return [
            'title' => 'Marketplace',
            'listings' => $listings,
            'offers' => MarketOffer::query()->with(['partner', 'listing'])->latest('id')->limit(10)->get(),
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'stats' => [
                'listings' => MarketListing::query()->count(),
                'published' => MarketListing::query()->where('status', 'published')->count(),
                'offers' => MarketOffer::query()->count(),
                'pending_offers' => MarketOffer::query()->where('status', 'pending')->count(),
            ],
        ];
    }

    public function createData(): array
    {
        return [
            'farmers' => User::where('role', 'farmer')->orderBy('name')->get(),
            'farms' => Farm::with('farmer')->orderBy('name')->get(),
        ];
    }

    public function storeListing(array $validated): MarketListing
    {
        if (($validated['status'] ?? '') === 'published') {
            $validated['published_at'] = now();
        }

        return MarketListing::create($validated);
    }

    public function updateListing(
        MarketListing $listing,
        array $data,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): void {
        \Illuminate\Support\Facades\DB::transaction(function () use ($listing, $data, $request, $audit, $notifications) {
            $oldValues = $listing->only(['status', 'published_at', 'sales_link', 'image_url']);

            if (isset($data['status']) && $data['status'] === 'published' && $listing->published_at === null) {
                $data['published_at'] = now();
            }

            $listing->update($data);

            $audit->write('admin_listing_updated', $listing, $oldValues, $listing->only(['status', 'published_at']), $request);
            $notifications->notifyAdmins('Listing marketplace diperbarui', "{$listing->commodity} menjadi {$listing->status}.");
        });
    }

    public function deleteListing(MarketListing $listing): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($listing) {
            $listing->images()->delete();
            $listing->delete();
        });
    }

    public function updateOffer(
        MarketOffer $offer,
        array $data,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): void {
        \Illuminate\Support\Facades\DB::transaction(function () use ($offer, $data, $request, $audit, $notifications) {
            $oldValues = $offer->only(['status']);
            $offer->update($data);

            $audit->write('admin_offer_updated', $offer, $oldValues, $offer->only(['status']), $request);
            $notifications->notifyAdmins('Penawaran marketplace diperbarui', "Offer #{$offer->id} menjadi {$offer->status}.");
        });
    }
}

