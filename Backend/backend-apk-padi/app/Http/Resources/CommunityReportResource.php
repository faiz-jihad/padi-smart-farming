<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunityReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scan_id' => $this->scan_id,
            'farmer_id' => $this->farmer_id,
            'farmer_name' => $this->farmer?->name ?? 'Petani Hamparan',
            'disease_name' => $this->scan?->predicted_class ?? 'Penyakit Padi',
            'image_url' => $this->scan?->image_url,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'radius_km' => $this->radius_km !== null ? (float) $this->radius_km : null,
            'consent_given' => (bool) $this->consent_given,
            'status' => $this->status ?? 'verified',
            'reported_at' => $this->reported_at ? (is_string($this->reported_at) ? $this->reported_at : $this->reported_at->toIso8601String()) : optional($this->created_at)->toIso8601String(),
        ];
    }
}