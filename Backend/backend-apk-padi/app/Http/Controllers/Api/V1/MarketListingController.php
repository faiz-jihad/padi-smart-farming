<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MarketListing\StoreMarketListingRequest;
use App\Http\Requests\Api\V1\MarketListing\UpdateMarketListingRequest;
use App\Http\Resources\MarketListingResource;
use App\Models\MarketListing;
use App\Services\MarketListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketListingController extends Controller
{
    public function index(
        Request $request,
        MarketListingService $listings
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => 'Daftar listing berhasil diambil',
            'data' => MarketListingResource::collection(
                $listings->getListings($request->user())
            ),
        ]);
    }

    public function store(
        StoreMarketListingRequest $request,
        MarketListingService $listings
    ): JsonResponse {
        $listing = $listings->createListing(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Listing hasil panen berhasil dibuat',
            'data' => new MarketListingResource($listing),
        ], 201);
    }

    public function show(
        Request $request,
        MarketListing $marketListing,
        MarketListingService $listings
    ): JsonResponse {
        $listing = $listings->getListing(
            $request->user(),
            $marketListing
        );

        return response()->json([
            'success' => true,
            'message' => 'Detail listing berhasil diambil',
            'data' => new MarketListingResource($listing),
        ]);
    }

    public function update(
        UpdateMarketListingRequest $request,
        MarketListing $marketListing,
        MarketListingService $listings
    ): JsonResponse {
        $listing = $listings->updateListing(
            $request->user(),
            $marketListing,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Listing berhasil diperbarui',
            'data' => new MarketListingResource($listing),
        ]);
    }

    public function destroy(
        Request $request,
        MarketListing $marketListing,
        MarketListingService $listings
    ): JsonResponse {
        $listings->deleteListing(
            $request->user(),
            $marketListing
        );

        return response()->json([
            'success' => true,
            'message' => 'Listing berhasil dihapus',
            'data' => null,
        ]);
    }
}
