<?php

namespace App\Http\Resources\Government;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GovernmentInsightResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_active_farmers'       => (int) ($this['total_active_farmers'] ?? 0),
            'total_active_farms'         => (int) ($this['total_active_farms'] ?? 0),
            'total_activities'           => (int) ($this['total_activities'] ?? 0),
            'top_activities'             => $this['top_activities'] ?? [],
            'total_disease_detections'   => (int) ($this['total_disease_detections'] ?? 0),
            'top_diseases'               => $this['top_diseases'] ?? [],
            'affected_commodities'       => $this['affected_commodities'] ?? ['Padi Sawah (Oryza sativa)'],
            'productive_rate_pct'        => (float) ($this['productive_rate_pct'] ?? 0),
            'need_attention_rate_pct'    => (float) ($this['need_attention_rate_pct'] ?? 0),
            'insufficient_data_rate_pct' => (float) ($this['insufficient_data_rate_pct'] ?? 0),
        ];
    }
}
