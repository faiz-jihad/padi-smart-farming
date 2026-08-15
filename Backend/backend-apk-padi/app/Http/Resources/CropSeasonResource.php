<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CropSeasonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'farm_id' => $this->farm_id,
            'variety_id' => $this->variety_id,
            'planned_planting_date' => $this->planned_planting_date,
            'planting_date' => $this->planting_date,
            'estimated_harvest_date' => $this->estimated_harvest_date,
            'status' => $this->status,
        ];
    }
}