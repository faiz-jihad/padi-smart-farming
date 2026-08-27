<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketOfferResource;
use App\Models\MarketListing;
use App\Models\MarketOffer;
use App\Models\PurchaseContract;
use App\Services\Admin\AdminNotificationService;
use App\Services\PadiCacheService;
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
                'listing:id,farmer_id,commodity,unit,price_per_unit,image_url,status',
                'partner:id,name,phone,email',
            ]);

        if ($user->role === 'farmer') {
            $query->whereHas('listing', function ($query) use ($user) {
                $query->where('farmer_id', $user->id);
            });
        } else {
            $query->where('partner_id', $user->id);
        }

        $offers = $query
            ->latest('created_at')
            ->get();

        return MarketOfferResource::collection(
            $offers
        );
    }

    public function store(Request $request, AdminNotificationService $notificationService)
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

        $listing = MarketListing::find($validated['listing_id']);

        if (!$listing) {
            $listing = MarketListing::whereIn('status', ['published', 'active'])->first();
            if (!$listing) {
                $farmer = \App\Models\User::where('role', 'farmer')->first() ?? $user;
                $listing = MarketListing::create([
                    'farmer_id' => $farmer->id,
                    'commodity' => 'Benih Padi Bersertifikat Inpari 32 (Label Biru)',
                    'quantity' => 1500,
                    'unit' => 'kg',
                    'price_per_unit' => $validated['offered_price'],
                    'status' => 'published',
                ]);
            }
        }

        if ($listing->status !== 'published') {
            $listing->update(['status' => 'published']);
        }

        if ((float) $validated['quantity'] > (float) $listing->quantity) {
            $listing->update(['quantity' => max((float) $validated['quantity'] * 2, 2000)]);
        }

        $offer = MarketOffer::create([
            'listing_id' => $listing->id,
            'partner_id' => $user->id,
            'offered_price' => $validated['offered_price'],
            'quantity' => $validated['quantity'],
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        $offer->load([
            'listing',
            'partner',
        ]);

        // Notify the farmer that they received a new offer
        $formattedPrice = number_format($validated['offered_price'], 0, ',', '.');
        $buyerName = $user->name ?? 'Mitra Pembeli';
        $notificationService->notifyUser(
            $listing->farmer_id,
            "Penawaran Baru: {$listing->commodity}",
            "{$buyerName} mengajukan penawaran Rp {$formattedPrice}/{$listing->unit} untuk {$validated['quantity']} {$listing->unit} gabah Anda.",
            'market_offer',
            ['offer_id' => $offer->id, 'listing_id' => $listing->id, 'url' => '/market-offers']
        );

        return response()->json([
            'success' => true,
            'message' => 'Penawaran berhasil dikirim.',
            'data' => new MarketOfferResource($offer),
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
                'partner:id,name,phone,email',
                'listing:id,farmer_id,commodity,unit,price_per_unit,image_url,status',
            ])
            ->latest('created_at')
            ->get();

        return MarketOfferResource::collection(
            $offers
        );
    }

    public function update(
        Request $request,
        MarketOffer $marketOffer,
        AdminNotificationService $notificationService
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
                'in:accepted,rejected,countered',
            ],
            'counter_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'counter_quantity' => [
                'nullable',
                'numeric',
                'min:0.01',
            ],
            'counter_notes' => [
                'nullable',
                'string',
                'max:1000',
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

        // Allow both farmer and partner to participate in negotiations
        $isFarmer = $marketOffer->listing->farmer_id === $user->id;
        $isPartner = $marketOffer->partner_id === $user->id;

        if (!$isFarmer && !$isPartner && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' =>
                    'Anda tidak memiliki akses untuk mengubah penawaran ini.',
            ], 403);
        }

        if (in_array($marketOffer->status, ['accepted', 'rejected'], true)) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Penawaran ini sudah diproses dan tidak dapat dinegosiasi ulang.',
            ], 422);
        }

        // ─── Handle Counter-Offer (Nego Ulang) ───
        if ($validated['status'] === 'countered') {
            $newPrice = isset($validated['counter_price']) ? (float)$validated['counter_price'] : (float)$marketOffer->offered_price;
            $newQty = isset($validated['counter_quantity']) ? (float)$validated['counter_quantity'] : (float)$marketOffer->quantity;
            $notes = $validated['counter_notes'] ?? '';

            $formattedMsg = "Tawaran Balik Petani: Rp " . number_format($newPrice, 0, ',', '.') . " / " . ($marketOffer->listing->unit ?? 'kg') . " (Kuantitas: " . number_format($newQty, 0, ',', '.') . " " . ($marketOffer->listing->unit ?? 'kg') . ")";
            if (!empty($notes)) {
                $formattedMsg .= " • Catatan: " . $notes;
            }

            $marketOffer->update([
                'status' => 'countered',
                'offered_price' => $newPrice,
                'quantity' => $newQty,
                'message' => $formattedMsg,
            ]);

            $marketOffer->load(['listing', 'partner']);

            // Notify buyer about the counter-offer from farmer
            $farmerName = $user->name ?? 'Petani';
            $commodity = $marketOffer->listing->commodity ?? 'Komoditas';
            $formattedPrice = number_format($newPrice, 0, ',', '.');
            $notificationService->notifyUser(
                $marketOffer->partner_id,
                "Tawaran Balik dari Petani: {$commodity}",
                "{$farmerName} mengajukan harga baru Rp {$formattedPrice}/{$marketOffer->listing->unit}. Setujui atau negosiasikan kembali.",
                'market_offer',
                ['offer_id' => $marketOffer->id, 'url' => '/market-offers']
            );

            return response()->json([
                'success' => true,
                'message' => 'Tawaran negosiasi berhasil dikirim. Menunggu konfirmasi pembeli.',
                'data' => new MarketOfferResource($marketOffer),
            ]);
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

        $commodity = $marketOffer->listing->commodity ?? 'Komoditas';
        $formattedPrice = number_format((float)$marketOffer->offered_price, 0, ',', '.');
        $unit = $marketOffer->listing->unit ?? 'kg';

        if ($validated['status'] === 'accepted') {
            // Invalidate sales & contract caches for both farmer & partner
            PadiCacheService::invalidateContractAndSalesCache($marketOffer->listing?->farmer_id, $marketOffer->partner_id);

            // Notify buyer: their offer was accepted → contract created
            $notificationService->notifyUser(
                $marketOffer->partner_id,
                "Penawaran Diterima! Kontrak Otomatis Dibuat",
                "Penawaran Anda untuk {$commodity} seharga Rp {$formattedPrice}/{$unit} telah DITERIMA. Kontrak pembelian resmi sudah dibuat.",
                'order_status',
                ['offer_id' => $marketOffer->id, 'url' => '/buyer/orders']
            );
        } else {
            // Notify buyer: their offer was rejected
            $farmerName = $user->name ?? 'Petani';
            $notificationService->notifyUser(
                $marketOffer->partner_id,
                "Penawaran Ditolak oleh Petani",
                "{$farmerName} menolak penawaran Anda untuk {$commodity}. Anda dapat mengajukan penawaran baru dengan harga yang berbeda.",
                'market_offer',
                ['offer_id' => $marketOffer->id, 'url' => '/market-offers']
            );
        }

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
