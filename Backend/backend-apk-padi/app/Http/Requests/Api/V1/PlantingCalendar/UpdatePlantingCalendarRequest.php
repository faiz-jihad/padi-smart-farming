<?php

namespace App\Http\Requests\Api\V1\PlantingCalendar;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlantingCalendarRequest extends FormRequest
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
            'province_id'      => ['sometimes', 'nullable', 'integer', 'exists:provinces,id'],
            'regency_id'       => ['sometimes', 'nullable', 'integer', 'exists:regencies,id'],
            'district_id'      => ['sometimes', 'nullable', 'integer', 'exists:districts,id'],
            'village_id'       => ['sometimes', 'nullable', 'integer', 'exists:villages,id'],
            'season'           => ['sometimes', 'required', 'string', 'in:rainy,dry,transition'],
            'year'             => ['sometimes', 'required', 'integer', 'min:2020', 'max:2035'],
            'planting_start'   => ['sometimes', 'required', 'date'],
            'planting_end'     => ['sometimes', 'required', 'date', 'after_or_equal:planting_start'],
            'planting_pattern' => ['sometimes', 'nullable', 'string', 'max:100'],
            'rice_variety'     => ['sometimes', 'nullable', 'string', 'max:100'],
            'recommended_area' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status'           => ['sometimes', 'nullable', 'string', 'in:draft,active,inactive'],
            'source'           => ['sometimes', 'nullable', 'string', 'max:100'],
            'notes'            => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
