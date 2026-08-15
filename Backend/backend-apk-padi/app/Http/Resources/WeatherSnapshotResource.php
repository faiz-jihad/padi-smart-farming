<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WeatherSnapshotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'farm_id' => $this->farm_id,
            'provider' => $this->provider,
            'observed_at' => $this->observed_at,
            'payload_json' => $this->payload_json,
            'expires_at' => $this->expires_at,
        ];
    }
}