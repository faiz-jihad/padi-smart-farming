<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PplValidationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scan_id' => $this->scan_id,
            'ppl_id' => $this->ppl_id,
            'status' => $this->status,
            'notes' => $this->notes,
            'validated_at' => $this->validated_at,
        ];
    }
}