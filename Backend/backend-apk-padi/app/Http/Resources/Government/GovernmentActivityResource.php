<?php

namespace App\Http\Resources\Government;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GovernmentActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $farm = $this->cropSeason?->farm;
        $variety = $this->cropSeason?->variety;
        $date = $this->occurred_at ? Carbon::parse($this->occurred_at) : null;

        $regionParts = array_filter([
            $farm?->village?->name,
            $farm?->district?->name,
            $farm?->regency?->name,
            $farm?->province?->name,
        ]);

        return [
            'id'            => $this->id,
            'activity_type' => ucfirst((string) $this->type),
            'activity_date' => $date ? $date->format('Y-m-d') : null,
            'activity_time' => $date ? $date->format('H:i:s') : null,
            'commodity'     => $variety ? 'Padi (' . $variety->name . ')' : 'Padi Sawah',
            'farm_name'     => $farm?->name ?? 'Lahan Terdaftar',
            'region'        => ! empty($regionParts) ? implode(', ', array_slice($regionParts, 1, 2)) : ($farm?->regency?->name ?? 'Wilayah Terdaftar'),
            'province'      => $farm?->province?->name ?? null,
            'regency'       => $farm?->regency?->name ?? null,
            'status'        => 'completed',
            'notes'         => $this->notes,
        ];
    }
}
