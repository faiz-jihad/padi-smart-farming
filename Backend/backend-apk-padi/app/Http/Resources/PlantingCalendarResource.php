<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlantingCalendarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'province_id'      => $this->province_id,
            'regency_id'       => $this->regency_id,
            'district_id'      => $this->district_id,
            'village_id'       => $this->village_id,
            'region_level'     => $this->region_level,
            'season'           => $this->season?->value,
            'season_label'     => $this->season?->label(),
            'season_code'      => $this->season?->indonesian(),
            'year'             => $this->year,
            'planting_start'   => $this->planting_start?->format('Y-m-d'),
            'planting_end'     => $this->planting_end?->format('Y-m-d'),
            'planting_pattern' => $this->planting_pattern,
            'rice_variety'     => $this->rice_variety,
            'recommended_area' => $this->recommended_area ? (float) $this->recommended_area : null,
            'status'           => $this->status?->value,
            'status_label'     => $this->status?->label(),
            'source'           => $this->source,
            'notes'            => $this->notes,
            'province'         => new ProvinceResource($this->whenLoaded('province')),
            'regency'          => new RegencyResource($this->whenLoaded('regency')),
            'district'         => new DistrictResource($this->whenLoaded('district')),
            'village'          => new VillageResource($this->whenLoaded('village')),
        ];
    }
}
