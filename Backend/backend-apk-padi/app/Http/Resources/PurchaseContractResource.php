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
            'quantity' => $this->quantity,
            'agreed_price' => $this->agreed_price,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'contracted_at' => $this->contracted_at,

            'listing' => $this->whenLoaded('listing', function () {
                return [
                    'id' => $this->listing->id,
                    'commodity' => $this->listing->commodity,
                    'unit' => $this->listing->unit,
                ];
            }),

            'farmer' => $this->whenLoaded('farmer', function () {
                return [
                    'id' => $this->farmer->id,
                    'name' => $this->farmer->name,
                    'email' => $this->farmer->email,
                ];
            }),

            'partner' => $this->whenLoaded('partner', function () {
                return [
                    'id' => $this->partner->id,
                    'name' => $this->partner->name,
                    'email' => $this->partner->email,
                ];
            }),
        ];
    }
}
