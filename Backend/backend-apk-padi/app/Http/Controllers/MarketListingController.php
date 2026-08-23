<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketListingResource;
use App\Models\CropSeason;
use App\Models\MarketListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarketListingController extends Controller
{
    public function index()
    {
        $listings = MarketListing::query()
            ->where('status', 'published')
            ->latest('published_at')
            ->get();

        return MarketListingResource::collection($listings);
    }

    public function show(MarketListing $marketListing)
    {
        return new MarketListingResource($marketListing);
    }

    public function store(Request $request)
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
            'farmer_id' => $request->user()->id,
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

        return response()->json([
            'success' => true,
            'message' => 'Hasil panen berhasil dipublikasikan.',
            'data' => new MarketListingResource($listing),
        ], 201);
    }
}
