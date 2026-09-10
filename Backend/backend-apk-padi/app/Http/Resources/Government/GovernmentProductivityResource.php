<?php

namespace App\Http\Resources\Government;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GovernmentProductivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'farm_id'                 => $this['farm_id'],
            'farm_name'               => $this['farm_name'],
            'area_ha'                 => (float) $this['area_ha'],
            'commodity'               => $this['commodity'],
            'region'                  => $this['region'],
            'total_harvest_ton'       => (float) $this['total_harvest_ton'],
            'productivity_ton_per_ha' => (float) $this['productivity_ton_per_ha'],
            'status'                  => $this['status'],
            'status_label'            => $this['status_label'],
            'harvest_records'         => (int) $this['harvest_records'],
        ];
    }
}
