<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiseaseScanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'farmer_id' => $this->farmer_id,
            'farm_id' => $this->farm_id,
            'farm_name' => $this->whenLoaded('farm', fn () => $this->farm?->name),
            'image_url' => $this->image_url,
            'quality_status' => $this->quality_status,
            'predicted_class' => $this->predicted_class,
            'confidence' => $this->confidence !== null ? (float) $this->confidence : null,
            'model_version' => $this->model_version,
            'scanned_at' => optional($this->scanned_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'recommendation' => $this->gemini_recommendations ?? (
                $this->relationLoaded('recommendation') && $this->recommendation ? [
                    'analisis' => $this->recommendation->explanation,
                    'langkah_preventif' => $this->recommendation->action,
                    'rekomendasi_obat' => $this->recommendation->safety_note,
                    'source' => $this->recommendation->source,
                ] : null
            ),
        ];
    }
}
