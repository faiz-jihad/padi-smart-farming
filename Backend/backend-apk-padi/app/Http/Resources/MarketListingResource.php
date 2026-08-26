<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imageUrl = $this->image_url;

        if (
            $imageUrl
            && ! preg_match('/^https?:\/\//i', $imageUrl)
        ) {
            $imageUrl = url(
                'storage/'.ltrim($imageUrl, '/')
            );
        }

        return [
            'id' => $this->id,
            'farmer_id' => $this->farmer_id,
            'farm_id' => $this->farm_id,
            'crop_season_id' => $this->crop_season_id,
            'harvest_id' => $this->harvest_id,
            'commodity' => $this->commodity ?? 'Gabah Kering Panen (GKP)',
            'quantity' => (float) ($this->quantity ?? 0),
            'unit' => $this->unit ?? 'kg',
            'price_per_unit' => (float) ($this->price_per_unit ?? 0),
            'description' => $this->description,
            'sales_link' => $this->sales_link,
            'image_url' => $imageUrl,
            'status' => $this->status ?? 'published',
            'published_at' => $this->published_at ? (is_string($this->published_at) ? $this->published_at : $this->published_at->toIso8601String()) : null,
            'expires_at' => $this->expires_at ? (is_string($this->expires_at) ? $this->expires_at : $this->expires_at->toIso8601String()) : null,
            'created_at' => $this->created_at ? (is_string($this->created_at) ? $this->created_at : $this->created_at->toIso8601String()) : null,

            'farmer_name' => $this->farmer?->name ?? 'Petani Hamparan',
            'farmer_phone' => $this->farmer?->phone,
            'farm_name' => $this->farm?->name ?? 'Lahan Padi',
            'farm_area_ha' => $this->farm?->area_ha,
            'variety_name' => $this->cropSeason?->variety?->name,
            'planting_date' => $this->cropSeason?->planting_date,
            'moisture_percent' => $this->harvest?->moisture_percent,
            'quality_grade' => $this->harvest?->quality_grade,

            'farmer' => $this->farmer ? [
                'id' => $this->farmer->id,
                'name' => $this->farmer->name,
                'phone' => $this->farmer->phone,
                'email' => $this->farmer->email,
            ] : null,

            'farm' => $this->farm ? [
                'id' => $this->farm->id,
                'name' => $this->farm->name,
                'area_ha' => $this->farm->area_ha,
                'latitude' => $this->farm->latitude,
                'longitude' => $this->farm->longitude,
            ] : null,

            'is_owner' => $request->user()
                ? (int) $this->farmer_id === (int) $request->user()->id
                : false,
        ];
    }
}
