<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketOfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'listing_id' => $this->listing_id,
            'partner_id' => $this->partner_id,
            'offered_price' => $this->offered_price,
            'quantity' => $this->quantity,
            'message' => $this->message,
            'status' => $this->status,

            'partner' => $this->whenLoaded('partner', function () {
                return [
                    'id' => $this->partner->id,
                    'name' => $this->partner->name,
                    'email' => $this->partner->email,
                    'phone' => $this->partner->phone,
                ];
            }),

            'listing' => $this->whenLoaded('listing', function () {
                return [
                    'id' => $this->listing->id,
                    'commodity' => $this->listing->commodity,
                    'quantity' => $this->listing->quantity,
                    'unit' => $this->listing->unit,
                    'price_per_unit' => $this->listing->price_per_unit,
                    'status' => $this->listing->status,
                ];
            }),
        ];
    }
}
