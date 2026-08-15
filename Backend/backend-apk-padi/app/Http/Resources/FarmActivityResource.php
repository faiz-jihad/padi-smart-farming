<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'crop_season_id' => $this->crop_season_id,
            'type' => $this->type,
            'occurred_at' => $this->occurred_at,
            'notes' => $this->notes,
            'cost' => $this->cost,
        ];
    }
}