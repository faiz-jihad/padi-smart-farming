<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'listing_id' => $this->listing_id,
            'farmer_id' => $this->farmer_id,
            'partner_id' => $this->partner_id,
            'offer_id' => $this->offer_id,

            'quantity' => (float) $this->quantity,
            'agreed_price' => (float) $this->agreed_price,
            'total_amount' => (float) $this->total_amount,

            'status' => $this->status,

            'contracted_at' => $this->contracted_at
                ? $this->contracted_at->toIso8601String()
                : null,

            // =====================================================
            // KOMODITAS
            // =====================================================

            'commodity' => $this->listing?->commodity,

            'unit' => $this->listing?->unit ?? 'kg',

            // =====================================================
            // FARMER
            // =====================================================

            'farmer_name' => $this->farmer?->name,

            'farmer_phone' => $this->farmer?->phone,

            // =====================================================
            // PARTNER / PEMBELI
            // =====================================================

            'partner_name' => $this->partner?->name,

            'partner_phone' => $this->partner?->phone,

            // =====================================================
            // LISTING
            // =====================================================

            'listing' => $this->whenLoaded(
                'listing',
                function () {
                    return [
                        'id' => $this->listing->id,

                        'commodity' =>
                            $this->listing->commodity,

                        'unit' =>
                            $this->listing->unit,

                        'image_url' =>
                            $this->listing->image_url,

                        'price_per_unit' =>
                            (float) (
                                $this->listing->price_per_unit ?? 0
                            ),

                        'description' =>
                            $this->listing->description,

                        'status' =>
                            $this->listing->status,
                    ];
                }
            ),

            // =====================================================
            // FARMER
            // =====================================================

            'farmer' => $this->whenLoaded(
                'farmer',
                function () {
                    return [
                        'id' =>
                            $this->farmer->id,

                        'name' =>
                            $this->farmer->name,

                        'email' =>
                            $this->farmer->email,

                        'phone' =>
                            $this->farmer->phone,
                    ];
                }
            ),

            // =====================================================
            // PARTNER
            // =====================================================

            'partner' => $this->whenLoaded(
                'partner',
                function () {
                    return [
                        'id' =>
                            $this->partner->id,

                        'name' =>
                            $this->partner->name,

                        'email' =>
                            $this->partner->email,

                        'phone' =>
                            $this->partner->phone,
                    ];
                }
            ),

            // =====================================================
            // OFFER
            // =====================================================

            'offer' => $this->whenLoaded(
                'offer',
                function () {
                    if (!$this->offer) {
                        return null;
                    }

                    return [
                        'id' =>
                            $this->offer->id,

                        'listing_id' =>
                            $this->offer->listing_id,

                        'partner_id' =>
                            $this->offer->partner_id,

                        'offered_price' =>
                            (float) (
                                $this->offer->offered_price ?? 0
                            ),

                        'quantity' =>
                            (float) (
                                $this->offer->quantity ?? 0
                            ),

                        'status' =>
                            $this->offer->status,

                        'message' =>
                            $this->offer->message,
                    ];
                }
            ),
        ];
    }
}
