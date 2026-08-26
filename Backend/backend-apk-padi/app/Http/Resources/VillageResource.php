<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VillageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'district_id' => $this->district_id,
            'code'        => $this->code,
            'name'        => $this->name,
            'type'        => $this->type?->value,
            'type_label'  => $this->type?->label(),
            'latitude'    => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude'   => $this->longitude !== null ? (float) $this->longitude : null,
            'has_boundary' => $this->whenLoaded('boundary', fn () => $this->boundary !== null, false),
        ];
    }
}
