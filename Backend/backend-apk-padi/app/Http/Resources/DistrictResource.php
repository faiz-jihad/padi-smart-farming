<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistrictResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'regency_id'  => $this->regency_id,
            'code'        => $this->code,
            'name'        => $this->name,
            'latitude'    => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude'   => $this->longitude !== null ? (float) $this->longitude : null,
            'has_boundary' => $this->whenLoaded('boundary', fn () => $this->boundary !== null, false),
        ];
    }
}
