<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\V1\MarketListing\StoreMarketListingRequest;
use App\Http\Resources\MarketListingResource;
use App\Models\MarketListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketListingController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketListing::query()
            ->with([
                'farmer',
                'farm',
                'cropSeason',
                'harvest',
                'images',
            ])
            ->where('status', 'published')
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($query) use ($search): void {
                $query
                    ->where('commodity', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return MarketListingResource::collection(
            $query->get()
        );
    }

    public function show(MarketListing $marketListing)
    {
        $marketListing->load([
            'farmer',
            'farm',
            'cropSeason',
            'harvest',
            'images',
            'offers',
        ]);

        return new MarketListingResource(
            $marketListing
        );
    }

    public function store(
        StoreMarketListingRequest $request
    ): JsonResponse {
        $listing = MarketListing::create([
            'farmer_id' => $request->user()->id,
            'farm_id' => $request->validated('farm_id'),
            'crop_season_id' => $request->validated('crop_season_id'),
            'harvest_id' => $request->validated('harvest_id'),
            'commodity' => $request->validated('commodity'),
            'quantity' => $request->validated('quantity'),
            'unit' => $request->validated('unit'),
            'price_per_unit' => $request->validated('price_per_unit'),
            'description' => $request->validated('description'),
            'sales_link' => $request->validated('sales_link'),
            'image_url' => $request->validated('image_url'),
            'status' => 'published',
            'published_at' => now(),
            'expires_at' => $request->validated('expires_at'),
        ]);

        $listing->load([
            'farmer',
            'farm',
            'cropSeason',
            'harvest',
            'images',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hasil panen berhasil dipublikasikan.',
            'data' => new MarketListingResource($listing),
        ], 201);
    }
}
