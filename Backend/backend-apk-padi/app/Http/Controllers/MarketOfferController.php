<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketOfferResource;
use App\Models\MarketListing;
use App\Models\MarketOffer;
use App\Models\PurchaseContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketOfferController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $query = MarketOffer::query()
            ->with([
                'listing',
                'partner',
            ]);

        if ($user->role === 'farmer') {
            $query->whereHas('listing', function ($query) use ($user) {
                $query->where('farmer_id', $user->id);
            });
        } else {
            $query->where('partner_id', $user->id);
        }

        $offers = $query
            ->latest()
            ->get();

        return MarketOfferResource::collection(
            $offers
        );
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validated = $request->validate([
            'listing_id' => [
                'required',
                'integer',
                'exists:market_listings,id',
            ],
            'offered_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'quantity' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'message' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $listing = MarketListing::findOrFail(
            $validated['listing_id']
        );

        if ($listing->farmer_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Petani tidak dapat memberikan penawaran pada hasil panennya sendiri.',
            ], 422);
        }

        if ($listing->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' =>
                    'Hasil panen ini sudah tidak tersedia.',
            ], 422);
        }

        if (
            (float) $validated['quantity'] >
            (float) $listing->quantity
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Jumlah penawaran melebihi jumlah hasil panen yang tersedia.',
            ], 422);
        }

        $offer = MarketOffer::create([
            'listing_id' => $listing->id,
            'partner_id' => $user->id,
            'offered_price' =>
                $validated['offered_price'],
            'quantity' =>
                $validated['quantity'],
            'message' =>
                $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        $offer->load([
            'listing',
            'partner',
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Penawaran berhasil dikirim.',
            'data' =>
                new MarketOfferResource($offer),
        ], 201);
    }

    public function listingOffers(
        Request $request,
        MarketListing $marketListing
    ) {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (
            $marketListing->farmer_id !==
            $user->id
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Anda tidak memiliki akses ke penawaran listing ini.',
            ], 403);
        }

        $offers = $marketListing
            ->offers()
            ->with([
                'partner',
                'listing',
            ])
            ->latest()
            ->get();

        return MarketOfferResource::collection(
            $offers
        );
    }

    public function update(
        Request $request,
        MarketOffer $marketOffer
    ) {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validated = $request->validate([
            'status' => [
                'required',
                'in:accepted,rejected',
            ],
        ]);

        $marketOffer->load('listing');

        if (!$marketOffer->listing) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Listing penawaran tidak ditemukan.',
            ], 404);
        }

        if (
            $marketOffer->listing->farmer_id !==
            $user->id
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Anda tidak memiliki akses untuk mengubah penawaran ini.',
            ], 403);
        }

        if ($marketOffer->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' =>
                    'Penawaran ini sudah diproses.',
            ], 422);
        }

        try {
            DB::transaction(function () use (
                $marketOffer,
                $validated
            ) {
                $listing = MarketListing::query()
                    ->where(
                        'id',
                        $marketOffer->listing_id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $validated['status'] ===
                    'rejected'
                ) {
                    $marketOffer->update([
                        'status' => 'rejected',
                    ]);

                    return;
                }

                if (
                    $listing->status !==
                    'published'
                ) {
                    throw new \RuntimeException(
                        'Hasil panen ini sudah terjual atau tidak tersedia.'
                    );
                }

                $alreadyAccepted =
                    MarketOffer::query()
                        ->where(
                            'listing_id',
                            $listing->id
                        )
                        ->where(
                            'status',
                            'accepted'
                        )
                        ->exists();

                if ($alreadyAccepted) {
                    throw new \RuntimeException(
                        'Hasil panen ini sudah memiliki penawaran yang diterima.'
                    );
                }

                $marketOffer->update([
                    'status' => 'accepted',
                ]);

                MarketOffer::query()
                    ->where(
                        'listing_id',
                        $listing->id
                    )
                    ->where(
                        'id',
                        '!=',
                        $marketOffer->id
                    )
                    ->where(
                        'status',
                        'pending'
                    )
                    ->update([
                        'status' => 'rejected',
                    ]);

                $listing->update([
                    'status' => 'sold',
                ]);

                PurchaseContract::create([
                    'listing_id' =>
                        $listing->id,
                    'farmer_id' =>
                        $listing->farmer_id,
                    'partner_id' =>
                        $marketOffer->partner_id,
                    'offer_id' =>
                        $marketOffer->id,
                    'quantity' =>
                        $marketOffer->quantity,
                    'agreed_price' =>
                        $marketOffer->offered_price,
                    'total_amount' =>
                        (float) $marketOffer->quantity *
                        (float) $marketOffer->offered_price,
                    'status' => 'active',
                    'contracted_at' => now(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $marketOffer->load([
            'listing',
            'partner',
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                $validated['status'] ===
                'accepted'
                    ? 'Penawaran berhasil diterima. Penawaran lainnya pada hasil panen ini ditolak dan hasil panen terjual.'
                    : 'Penawaran berhasil ditolak.',
            'data' =>
                new MarketOfferResource(
                    $marketOffer
                ),
        ]);
    }
}
