<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imageUrl = null;

        if ($this->image_url) {
            $imageUrl = url(
                'storage/' . ltrim($this->image_url, '/')
            );
        }

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
            'sales_link' => $this->sales_link,
            'image_url' => $imageUrl,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'expires_at' => $this->expires_at,

            'is_owner' => $request->user()
                ? (int) $this->farmer_id === (int) $request->user()->id
                : false,
        ];
    }
}
