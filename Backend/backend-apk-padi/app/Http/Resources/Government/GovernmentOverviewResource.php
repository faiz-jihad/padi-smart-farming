<?php

namespace App\Http\Resources\Government;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GovernmentOverviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_farmers'             => (int) ($this['total_farmers'] ?? 0),
            'total_farms'               => (int) ($this['total_farms'] ?? 0),
            'total_activities'          => (int) ($this['total_activities'] ?? 0),
            'total_disease_detections'  => (int) ($this['total_disease_detections'] ?? 0),
            'productive_farms'          => (int) ($this['productive_farms'] ?? 0),
            'need_attention_farms'      => (int) ($this['need_attention_farms'] ?? 0),
            'insufficient_data_farms'   => (int) ($this['insufficient_data_farms'] ?? 0),
        ];
    }
}
