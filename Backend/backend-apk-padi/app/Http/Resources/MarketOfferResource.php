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
        ];
    }
}