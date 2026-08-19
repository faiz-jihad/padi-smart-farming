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
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius_km' => $this->radius_km,
            'consent_given' => $this->consent_given,
            'status' => $this->status,
            'reported_at' => $this->reported_at,
        ];
    }
}