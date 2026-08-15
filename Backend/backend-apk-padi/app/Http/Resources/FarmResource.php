<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'area_ha' => $this->area_ha,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'irrigation_type' => $this->irrigation_type,
            'irrigation_notes' => $this->irrigation_notes,
        ];
    }
}