<?php

namespace App\Http\Requests\Api\V1\Farm;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFarmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'             => ['sometimes', 'required', 'string', 'max:100'],
            'area_ha'          => ['sometimes', 'required', 'numeric', 'gt:0'],
            'latitude'         => ['sometimes', 'required', 'numeric', 'between:-90,90'],
            'longitude'        => ['sometimes', 'required', 'numeric', 'between:-180,180'],
            'irrigation_type'  => ['sometimes', 'required', 'string', 'max:50'],
            'irrigation_notes' => ['nullable', 'string', 'max:500'],
            'province_id'      => ['nullable', 'integer', 'exists:provinces,id'],
            'regency_id'       => ['nullable', 'integer', 'exists:regencies,id'],
            'district_id'      => ['nullable', 'integer', 'exists:districts,id'],
            'village_id'       => ['nullable', 'integer', 'exists:villages,id'],
            'soil_type'        => ['nullable', 'string', 'max:50'],
            'status'           => ['nullable', 'in:active,inactive,fallow'],
        ];
    }
}
