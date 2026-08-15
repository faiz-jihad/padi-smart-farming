<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'farmer_id' => $this->farmer_id,
            'farm_id' => $this->farm_id,
            'crop_season_id' => $this->crop_season_id,
            'harvest_id' => $this->harvest_id,
            'commodity' => $this->commodity,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'price_per_unit' => $this->price_per_unit,
            'description' => $this->description,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'expires_at' => $this->expires_at,
        ];
    }
}