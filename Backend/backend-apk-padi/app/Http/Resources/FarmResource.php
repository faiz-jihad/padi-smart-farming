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
            'id'               => $this->id,
            'farmer_user_id'   => $this->farmer_user_id,
            'name'             => $this->name,
            'area_ha'          => (float) $this->area_ha,
            'latitude'         => (float) $this->latitude,
            'longitude'        => (float) $this->longitude,
            'boundary_coordinates' => $this->boundary_coordinates,
            'irrigation_type'  => $this->irrigation_type,
            'irrigation_notes' => $this->irrigation_notes,
            'soil_type'        => $this->soil_type,
            'status'           => $this->status ?? 'active',
            'province_id'      => $this->province_id,
            'regency_id'       => $this->regency_id,
            'district_id'      => $this->district_id,
            'village_id'       => $this->village_id,
            'province'         => new ProvinceResource($this->whenLoaded('province')),
            'regency'          => new RegencyResource($this->whenLoaded('regency')),
            'district'         => new DistrictResource($this->whenLoaded('district')),
            'village'          => new VillageResource($this->whenLoaded('village')),
        ];
    }
}
