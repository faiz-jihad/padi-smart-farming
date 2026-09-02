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
                'listing:id,farmer_id,commodity,quantity,unit,price_per_unit,image_url,status',
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

        return MarketOfferResource::collection($offers);
    }

    public function store(
        Request $request,
        AdminNotificationService $notificationService
    ) {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user->role !== 'partner') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya mitra pembeli yang dapat mengajukan penawaran.',
            ], 403);
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
                'min:0.01',
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

        $listing = MarketListing::query()
            ->where('id', $validated['listing_id'])
            ->whereIn('status', ['published', 'active'])
            ->first();

        if (!$listing) {
            return response()->json([
                'success' => false,
                'message' => 'Hasil panen tidak tersedia atau sudah tidak dapat ditawar.',
            ], 422);
        }

        if ((float) $validated['quantity'] > (float) $listing->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah penawaran melebihi stok hasil panen yang tersedia.',
            ], 422);
        }

        $existingOffer = MarketOffer::query()
            ->where('listing_id', $listing->id)
            ->where('partner_id', $user->id)
            ->whereIn('status', ['pending', 'countered'])
            ->exists();

        if ($existingOffer) {
            return response()->json([
                'success' => false,
                'message' => 'Anda masih memiliki penawaran aktif pada hasil panen ini.',
            ], 422);
        }

        $offer = MarketOffer::create([
            'listing_id' => $listing->id,
            'partner_id' => $user->id,
            'offered_price' => $validated['offered_price'],
            'quantity' => $validated['quantity'],
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
            'last_offer_by' => 'buyer',
        ]);

        $offer->load([
            'listing',
            'partner',
        ]);

        $formattedPrice = number_format(
            (float) $validated['offered_price'],
            0,
            ',',
            '.'
        );

        $buyerName = $user->name ?? 'Mitra Pembeli';
        $unit = $listing->unit ?? 'kg';

        $notificationService->notifyUser(
            $listing->farmer_id,
            "Penawaran Baru: {$listing->commodity}",
            "{$buyerName} mengajukan penawaran Rp {$formattedPrice}/{$unit} untuk {$validated['quantity']} {$unit} hasil panen Anda.",
            'market_offer',
            [
                'offer_id' => $offer->id,
                'listing_id' => $listing->id,
                'url' => '/market-offers',
            ]
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
            $marketListing->farmer_id !== $user->id &&
            $user->role !== 'admin'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke penawaran listing ini.',
            ], 403);
        }

        $offers = $marketListing
            ->offers()
            ->with([
                'partner:id,name,phone,email',
                'listing:id,farmer_id,commodity,quantity,unit,price_per_unit,image_url,status',
            ])
            ->latest('created_at')
            ->get();

        return MarketOfferResource::collection($offers);
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
                'min:0.01',
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

        $marketOffer->load([
            'listing',
            'partner',
        ]);

        if (!$marketOffer->listing) {
            return response()->json([
                'success' => false,
                'message' => 'Listing penawaran tidak ditemukan.',
            ], 404);
        }

        $listing = $marketOffer->listing;

        $isFarmer = $listing->farmer_id === $user->id;
        $isPartner = $marketOffer->partner_id === $user->id;
        $isAdmin = $user->role === 'admin';

        if (!$isFarmer && !$isPartner && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengubah penawaran ini.',
            ], 403);
        }

        if (in_array($marketOffer->status, ['accepted', 'rejected'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Penawaran ini sudah diproses dan tidak dapat diubah kembali.',
            ], 422);
        }

        $lastOfferBy = strtolower((string) $marketOffer->last_offer_by);
        if (!$isAdmin) {
    if ($isFarmer && $lastOfferBy !== 'buyer') {
        return response()->json([
            'success' => false,
            'message' => 'Belum giliran petani untuk memproses penawaran ini.',
        ], 422);
    }

    if ($isPartner && $lastOfferBy !== 'farmer') {
        return response()->json([
            'success' => false,
            'message' => 'Belum giliran pembeli untuk memproses penawaran ini.',
        ], 422);
    }
}
        if ($validated['status'] === 'countered') {
            if ($isFarmer && $lastOfferBy !== 'buyer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum giliran petani untuk memberikan tawaran balik.',
                ], 422);
            }

            if ($isPartner && $lastOfferBy !== 'farmer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum giliran pembeli untuk memberikan tawaran balik.',
                ], 422);
            }

            if ($isAdmin) {
                $counterBy = $lastOfferBy === 'buyer'
                    ? 'farmer'
                    : 'buyer';
            } elseif ($isFarmer) {
                $counterBy = 'farmer';
            } else {
                $counterBy = 'buyer';
            }

            $newPrice = isset($validated['counter_price'])
                ? (float) $validated['counter_price']
                : (float) $marketOffer->offered_price;

            $newQuantity = isset($validated['counter_quantity'])
                ? (float) $validated['counter_quantity']
                : (float) $marketOffer->quantity;

            if ($newQuantity > (float) $listing->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah tawaran balik melebihi stok hasil panen yang tersedia.',
                ], 422);
            }

            $notes = trim($validated['counter_notes'] ?? '');
            $unit = $listing->unit ?? 'kg';

            $actorLabel = $counterBy === 'farmer'
                ? 'Petani'
                : 'Pembeli';

            $message = "Tawaran Balik {$actorLabel}: Rp " .
                number_format($newPrice, 0, ',', '.') .
                " / {$unit} (Kuantitas: " .
                number_format($newQuantity, 2, ',', '.') .
                " {$unit})";

            if ($notes !== '') {
                $message .= ' • Catatan: ' . $notes;
            }

            $marketOffer->update([
                'status' => 'countered',
                'offered_price' => $newPrice,
                'quantity' => $newQuantity,
                'message' => $message,
                'last_offer_by' => $counterBy,
            ]);

            $marketOffer->load([
                'listing',
                'partner',
            ]);

            $commodity = $listing->commodity ?? 'Komoditas';

            if ($counterBy === 'farmer') {
                $farmerName = $user->name ?? 'Petani';

                $notificationService->notifyUser(
                    $marketOffer->partner_id,
                    "Tawaran Balik dari Petani: {$commodity}",
                    "{$farmerName} mengajukan harga baru Rp " .
                    number_format($newPrice, 0, ',', '.') .
                    "/{$unit}. Silakan terima, tolak, atau ajukan penawaran kembali.",
                    'market_offer',
                    [
                        'offer_id' => $marketOffer->id,
                        'listing_id' => $listing->id,
                        'url' => '/market-offers',
                    ]
                );

                $responseMessage =
                    'Tawaran balik petani berhasil dikirim. Sekarang giliran pembeli.';
            } else {
                $buyerName = $user->name ?? 'Mitra Pembeli';

                $notificationService->notifyUser(
                    $listing->farmer_id,
                    "Tawaran Balik dari Pembeli: {$commodity}",
                    "{$buyerName} mengajukan harga baru Rp " .
                    number_format($newPrice, 0, ',', '.') .
                    "/{$unit}. Silakan terima, tolak, atau ajukan penawaran kembali.",
                    'market_offer',
                    [
                        'offer_id' => $marketOffer->id,
                        'listing_id' => $listing->id,
                        'url' => '/market-offers',
                    ]
                );

                $responseMessage =
                    'Tawaran balik pembeli berhasil dikirim. Sekarang giliran petani.';
            }

            return response()->json([
                'success' => true,
                'message' => $responseMessage,
                'data' => new MarketOfferResource($marketOffer),
            ]);
        }

        if ($validated['status'] === 'accepted') {
            if ($isFarmer && $lastOfferBy !== 'buyer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum giliran petani untuk menerima penawaran.',
                ], 422);
            }

            if ($isPartner && $lastOfferBy !== 'farmer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum giliran pembeli untuk menerima penawaran.',
                ], 422);
            }

            if (!$isFarmer && !$isPartner && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menerima penawaran.',
                ], 403);
            }
        }

        if ($validated['status'] === 'rejected') {
            if ($isFarmer && $lastOfferBy !== 'buyer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum giliran petani untuk menolak penawaran.',
                ], 422);
            }

            if ($isPartner && $lastOfferBy !== 'farmer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum giliran pembeli untuk menolak penawaran.',
                ], 422);
            }

            if (!$isFarmer && !$isPartner && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menolak penawaran.',
                ], 403);
            }
        }

        try {
            DB::transaction(function () use (
                $marketOffer,
                $validated
            ) {
                $listing = MarketListing::query()
                    ->where('id', $marketOffer->listing_id)
                    ->lockForUpdate()
                    ->first();

                if (!$listing) {
                    throw new \RuntimeException(
                        'Hasil panen tidak ditemukan.'
                    );
                }

                if ($validated['status'] === 'rejected') {
                    $marketOffer->update([
                        'status' => 'rejected',
                    ]);

                    return;
                }

                if ($listing->status !== 'published') {
                    throw new \RuntimeException(
                        'Hasil panen ini sudah terjual atau tidak tersedia.'
                    );
                }

                if ((float) $marketOffer->quantity > (float) $listing->quantity) {
                    throw new \RuntimeException(
                        'Jumlah penawaran melebihi stok hasil panen yang tersedia.'
                    );
                }

                $alreadyAccepted = MarketOffer::query()
                    ->where('listing_id', $listing->id)
                    ->where('status', 'accepted')
                    ->where('id', '!=', $marketOffer->id)
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
                    ->where('listing_id', $listing->id)
                    ->where('id', '!=', $marketOffer->id)
                    ->whereIn('status', ['pending', 'countered'])
                    ->update([
                        'status' => 'rejected',
                    ]);

                $listing->update([
                    'status' => 'sold',
                ]);

                PurchaseContract::create([
                    'listing_id' => $listing->id,
                    'farmer_id' => $listing->farmer_id,
                    'partner_id' => $marketOffer->partner_id,
                    'offer_id' => $marketOffer->id,
                    'quantity' => $marketOffer->quantity,
                    'agreed_price' => $marketOffer->offered_price,
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
        $unit = $marketOffer->listing->unit ?? 'kg';

        $formattedPrice = number_format(
            (float) $marketOffer->offered_price,
            0,
            ',',
            '.'
        );

        PadiCacheService::invalidateContractAndSalesCache(
            $marketOffer->listing->farmer_id,
            $marketOffer->partner_id
        );

        if ($validated['status'] === 'accepted') {
            $acceptedBy = $isFarmer
                ? 'Petani'
                : ($isPartner ? 'Pembeli' : 'Admin');

            $notificationService->notifyUser(
                $marketOffer->partner_id,
                'Penawaran Diterima! Kontrak Otomatis Dibuat',
                "Penawaran {$commodity} sebesar Rp {$formattedPrice}/{$unit} telah diterima oleh {$acceptedBy}. Kontrak pembelian resmi sudah dibuat.",
                'order_status',
                [
                    'offer_id' => $marketOffer->id,
                    'listing_id' => $marketOffer->listing_id,
                    'url' => '/buyer/orders',
                ]
            );

            $message =
                'Penawaran berhasil diterima. Purchase Contract otomatis dibuat dan penawaran lainnya pada hasil panen ini ditolak.';
        } else {
            if ($isFarmer) {
                $farmerName = $user->name ?? 'Petani';

                $notificationService->notifyUser(
                    $marketOffer->partner_id,
                    'Penawaran Ditolak oleh Petani',
                    "{$farmerName} menolak penawaran Anda untuk {$commodity}. Anda dapat mengajukan penawaran baru dengan harga yang berbeda.",
                    'market_offer',
                    [
                        'offer_id' => $marketOffer->id,
                        'listing_id' => $marketOffer->listing_id,
                        'url' => '/marketplace',
                    ]
                );
            } elseif ($isPartner) {
                $buyerName = $user->name ?? 'Mitra Pembeli';

                $notificationService->notifyUser(
                    $marketOffer->listing->farmer_id,
                    'Penawaran Ditolak oleh Pembeli',
                    "{$buyerName} menolak penawaran untuk {$commodity}.",
                    'market_offer',
                    [
                        'offer_id' => $marketOffer->id,
                        'listing_id' => $marketOffer->listing_id,
                        'url' => '/market-offers',
                    ]
                );
            }

            $message = 'Penawaran berhasil ditolak.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => new MarketOfferResource($marketOffer),
        ]);
    }
}
