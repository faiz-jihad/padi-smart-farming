<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketListingResource;
use App\Models\CropSeason;
use App\Models\MarketListing;
use App\Services\Admin\AdminNotificationService;
use App\Services\PadiCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarketListingController extends Controller
{
    public function index()
    {
        $listings = PadiCacheService::remember('padi:market:published_listings', 120, function () {
            return MarketListing::query()
                ->with([
                    'farmer:id,name,phone,email',
                    'farm:id,name,area_ha,latitude,longitude',
                    'cropSeason.variety:id,name',
                    'harvest:id,moisture_percent,quality_grade',
                ])
                ->where('status', 'published')
                ->latest('published_at')
                ->get();
        });

        return MarketListingResource::collection($listings);
    }

    public function show(MarketListing $marketListing)
    {
        return new MarketListingResource($marketListing);
    }

    public function store(Request $request, AdminNotificationService $notificationService)
    {
        $validated = $request->validate([
            'farm_id' => ['required', 'integer', 'exists:farms,id'],
            'commodity' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', 'string', 'max:20'],
            'price_per_unit' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string'],
            'sales_link' => ['nullable', 'string', 'max:500'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user = $request->user();
        $farm = \App\Models\Farm::findOrFail($validated['farm_id']);

        if ($farm->farmer_user_id !== $user->id && ! $user->hasRole('admin')) {
            abort(403, 'Anda tidak memiliki akses ke data lahan ini.');
        }

        $cropSeason = CropSeason::where('farm_id', $validated['farm_id'])
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if (!$cropSeason) {
            return response()->json([
                'success' => false,
                'message' => 'Lahan belum memiliki musim tanam aktif.',
            ], 422);
        }

        $imagePath = $request->file('image')->store(
            'marketplace',
            'public'
        );

        $listing = MarketListing::create([
            'farmer_id' => $user->hasRole('admin') ? ($farm->farmer_user_id ?? $user->id) : $user->id,
            'farm_id' => $validated['farm_id'],
            'crop_season_id' => $cropSeason->id,
            'harvest_id' => null,
            'commodity' => $validated['commodity'],
            'quantity' => $validated['quantity'],
            'unit' => $validated['unit'],
            'price_per_unit' => $validated['price_per_unit'],
            'description' => $validated['description'] ?? null,
            'sales_link' => $validated['sales_link'] ?? null,
            'image_url' => $imagePath,
            'status' => 'published',
            'published_at' => now(),
        ]);

        // 1. Invalidate Redis & Marketplace Cache
        PadiCacheService::invalidateMarketCache();

        // 2. Real-time Notification: Notify buyers about new grain harvest listing
        $farmerName = $request->user()->name ?? 'Petani Hamparan';
        $formattedPrice = number_format($listing->price_per_unit, 0, ',', '.');

        $notificationService->notifyBuyers(
            "🌾 Gabah Siap Tebas: {$listing->commodity}",
            "{$farmerName} menerbitkan penawaran {$listing->quantity} {$listing->unit} seharga Rp {$formattedPrice}/{$listing->unit}.",
            'marketplace_deal',
            ['listing_id' => $listing->id, 'price' => $listing->price_per_unit]
        );

        return response()->json([
            'success' => true,
            'message' => 'Hasil panen berhasil dipublikasikan secara real-time.',
            'data' => new MarketListingResource($listing),
        ], 201);
    }
}
