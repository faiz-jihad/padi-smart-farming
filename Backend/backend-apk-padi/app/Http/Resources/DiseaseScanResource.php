<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiseaseScanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $metadata = is_array($this->detection_metadata ?? null) ? $this->detection_metadata : [];

        return [
            'id' => $this->id,
            'farmer_id' => $this->farmer_id,
            'farm_id' => $this->farm_id,
            'farm_name' => $this->whenLoaded('farm', fn () => $this->farm?->name),
            'image_url' => $this->image_url,
            'quality_status' => $this->quality_status,
            'predicted_class' => $this->predicted_class,
            'confidence' => $this->confidence !== null ? (float) $this->confidence : null,
            'confidence_level' => $metadata['confidence_level'] ?? null,
            'needs_expert_review' => (bool) ($metadata['needs_expert_review'] ?? false),
            'image_quality' => $metadata['image_quality'] ?? null,
            'top_predictions' => $metadata['top_predictions'] ?? [],
            'prediction_margin' => isset($metadata['prediction_margin']) ? (float) $metadata['prediction_margin'] : null,
            'model_accuracy' => isset($metadata['model_accuracy']) ? (float) $metadata['model_accuracy'] : null,
            'detection_status' => $metadata['detection_status'] ?? 'DETECTED',
            'status_message' => $metadata['status_message'] ?? null,
            'model_version' => $this->model_version,
            'user_feedback' => $this->user_feedback,
            'verified_class' => $this->verified_class,
            'is_learned' => (bool) $this->is_learned,
            'feedback_notes' => $this->feedback_notes,
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
