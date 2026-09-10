<?php

namespace App\Http\Resources\Government;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GovernmentDiseaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $farm = $this->farm;
        $date = $this->scanned_at ? Carbon::parse($this->scanned_at) : ($this->created_at ? Carbon::parse($this->created_at) : null);
        $metadata = is_array($this->detection_metadata ?? null) ? $this->detection_metadata : [];

        $regionParts = array_filter([
            $farm?->village?->name,
            $farm?->district?->name,
            $farm?->regency?->name,
            $farm?->province?->name,
        ]);

        return [
            'id'               => $this->id,
            'disease'          => $this->predicted_class ?? 'Penyakit Tidak Teridentifikasi',
            'quality_status'   => $this->quality_status,
            'confidence'       => $this->confidence !== null ? round((float) $this->confidence, 4) : null,
            'confidence_level' => $metadata['confidence_level'] ?? ($this->confidence >= 0.8 ? 'Tinggi' : ($this->confidence >= 0.5 ? 'Sedang' : 'Rendah')),
            'commodity'        => 'Padi Sawah (Oryza sativa)',
            'farm_name'        => $farm?->name ?? 'Lahan Terdaftar',
            'region'           => ! empty($regionParts) ? implode(', ', array_slice($regionParts, 1, 2)) : ($farm?->regency?->name ?? 'Wilayah Terdaftar'),
            'province'         => $farm?->province?->name ?? null,
            'regency'          => $farm?->regency?->name ?? null,
            'detected_at'      => $date ? $date->toIso8601String() : null,
            'recommendation'   => $this->recommendation?->action ?? 'Lakukan inspeksi visual dan isolasi tanaman terinfeksi.',
        ];
    }
}
