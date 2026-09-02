<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PplValidationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $scan = $this->whenLoaded('scan');

        return [
            'id'           => $this->id,
            'scan_id'      => $this->scan_id,
            'ppl_id'       => $this->ppl_id,
            'status'       => $this->status,
            'notes'        => $this->notes,
            'validated_at' => $this->validated_at?->toIso8601String(),
            'created_at'   => $this->created_at?->toIso8601String(),

            // Scan details (when loaded)
            'scan' => $scan ? [
                'id'              => $scan->id,
                'predicted_class' => $scan->predicted_class,
                'quality_status'  => $scan->quality_status,
                'confidence'      => $scan->confidence,
                'image_url'       => $scan->image_url,
                'scanned_at'      => $scan->scanned_at?->toIso8601String(),
                'farm'            => $this->whenLoaded('scan', fn () => $scan->relationLoaded('farm') ? [
                    'id'   => $scan->farm->id,
                    'name' => $scan->farm->name,
                ] : null),
                'farmer'          => $this->whenLoaded('scan', fn () => $scan->relationLoaded('farmer') ? [
                    'id'   => $scan->farmer->id,
                    'name' => $scan->farmer->name,
                ] : null),
                'recommendation'  => $this->whenLoaded('scan', fn () => $scan->relationLoaded('recommendation') && $scan->recommendation ? [
                    'explanation'    => $scan->recommendation->explanation,
                    'action'         => $scan->recommendation->action,
                    'safety_note'    => $scan->recommendation->safety_note,
                ] : null),
            ] : null,

            // PPL details (when loaded)
            'ppl' => $this->whenLoaded('ppl', fn () => [
                'id'   => $this->ppl->id,
                'name' => $this->ppl->name,
            ]),
        ];
    }
}