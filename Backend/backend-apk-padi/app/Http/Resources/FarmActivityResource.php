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
            'activity_id' => $this->id,
            'crop_season_id' => $this->crop_season_id,
            'type' => $this->type,
            'occurred_at' => $this->occurred_at,
            'notes' => $this->notes,
            'cost' => $this->cost,
            'source' => $this->source ?? 'MANUAL',
            'status' => $this->status ?? 'COMPLETED',
            'sync_status' => $this->sync_status ?? 'pending',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
