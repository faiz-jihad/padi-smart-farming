<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CropSeason;
use App\Rules\FarmBelongsToFarmer;
use App\Models\MarketListing;
use App\Models\MarketOffer;
use App\Services\Admin\AdminAuditLogger;
use App\Services\Admin\AdminMarketplaceService;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request, AdminMarketplaceService $marketplace): View
    {
        return view('admin.marketplace.index', $marketplace->indexData($request));
    }

    public function create(AdminMarketplaceService $marketplace): View
    {
        return view('admin.marketplace.create', $marketplace->createData());
    }

    public function store(Request $request, AdminMarketplaceService $marketplace): RedirectResponse
    {
        $validated = $request->validate([
            'farmer_id' => 'required|integer|exists:users,id',
            'farm_id' => [
                'required',
                'integer',
                'exists:farms,id',
                new FarmBelongsToFarmer((int) $request->farmer_id),
            ],
            'commodity' => 'required|string|max:100',
            'quantity' => 'required|numeric|min:0.1',
            'unit' => 'required|string|max:20',
            'price_per_unit' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'sales_link' => 'nullable|url|max:1000',
            'image_url' => 'nullable|url|max:1000',
            'status' => 'required|string|in:draft,published,closed,rejected,expired',
        ]);

        // Auto assign crop season
        $cropSeason = CropSeason::where('farm_id', $validated['farm_id'])
            ->latest('id')
            ->first();

        $validated['crop_season_id'] = $cropSeason?->id ?? CropSeason::firstOrCreate([
            'farm_id' => $validated['farm_id'],
            'season_name' => 'MT2 2026',
        ], [
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->addMonths(4),
            'status' => 'active',
        ])->id;

        $listing = $marketplace->storeListing($validated);

        return redirect()->route('admin.marketplace.index')
            ->with('status', "Listing {$listing->commodity} berhasil dibuat.");
    }

    public function edit(MarketListing $listing, AdminMarketplaceService $marketplace): View
    {
        $data = $marketplace->createData();
        $data['listing'] = $listing;

        return view('admin.marketplace.edit', $data);
    }

    public function updateListing(
        Request $request,
        MarketListing $listing,
        AdminMarketplaceService $marketplace,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $validated = $request->validate([
            'commodity' => 'sometimes|string|max:100',
            'quantity' => 'sometimes|numeric|min:0.1',
            'unit' => 'sometimes|string|max:20',
            'price_per_unit' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'sales_link' => 'nullable|url|max:1000',
            'image_url' => 'nullable|url|max:1000',
            'status' => 'required|string|in:draft,published,closed,rejected,expired',
        ]);

        $marketplace->updateListing($listing, $validated, $request, $audit, $notifications);

        return redirect()->route('admin.marketplace.index')
            ->with('status', "Listing #{$listing->id} ({$listing->commodity}) berhasil diperbarui.");
    }

    public function destroy(MarketListing $listing, AdminMarketplaceService $marketplace): RedirectResponse
    {
        $commodity = $listing->commodity;
        $marketplace->deleteListing($listing);

        return redirect()->route('admin.marketplace.index')
            ->with('status', "Listing {$commodity} telah dihapus.");
    }

    public function updateOffer(
        Request $request,
        MarketOffer $offer,
        AdminMarketplaceService $marketplace,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,accepted,rejected,cancelled',
        ]);

        $marketplace->updateOffer($offer, $validated, $request, $audit, $notifications);

        return back()->with('status', 'Status penawaran berhasil diperbarui.');
    }
}
