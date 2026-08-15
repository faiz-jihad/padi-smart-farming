<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateMarketListingRequest;
use App\Http\Requests\Admin\UpdateMarketOfferRequest;
use App\Models\MarketListing;
use App\Models\MarketOffer;
use App\Services\Admin\AdminAuditLogger;
use App\Services\Admin\AdminMarketplaceService;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(AdminMarketplaceService $marketplace): View
    {
        return view('admin.marketplace.index', $marketplace->indexData());
    }

    public function updateListing(
        UpdateMarketListingRequest $request,
        MarketListing $listing,
        AdminMarketplaceService $marketplace,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $marketplace->updateListing($listing, $request->validated(), $request, $audit, $notifications);

        return back()->with('status', 'Status listing berhasil diperbarui.');
    }

    public function updateOffer(
        UpdateMarketOfferRequest $request,
        MarketOffer $offer,
        AdminMarketplaceService $marketplace,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $marketplace->updateOffer($offer, $request->validated(), $request, $audit, $notifications);

        return back()->with('status', 'Status penawaran berhasil diperbarui.');
    }
}
