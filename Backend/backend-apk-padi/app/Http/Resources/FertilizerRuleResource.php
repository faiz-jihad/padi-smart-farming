<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FertilizerRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'variety_id' => $this->variety_id,
            'phase' => $this->phase,
            'nutrient' => $this->nutrient,
            'kg_per_ha' => $this->kg_per_ha,
            'source' => $this->source,
            'version' => $this->version,
        ];
    }
}