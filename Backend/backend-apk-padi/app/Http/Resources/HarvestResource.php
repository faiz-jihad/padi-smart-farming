<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HarvestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'crop_season_id' => $this->crop_season_id,
            'harvest_date' => $this->harvest_date,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'quality_grade' => $this->quality_grade,
            'moisture_percent' => $this->moisture_percent,
            'verification_status' => $this->verification_status,
        ];
    }
}