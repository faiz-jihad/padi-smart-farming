<?php

namespace App\Http\Requests\Api\V1\PlantingCalendar;

use Illuminate\Foundation\Http\FormRequest;

class StorePlantingCalendarRequest extends FormRequest
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
            'province_id'      => ['nullable', 'integer', 'exists:provinces,id'],
            'regency_id'       => ['nullable', 'integer', 'exists:regencies,id'],
            'district_id'      => ['nullable', 'integer', 'exists:districts,id'],
            'village_id'       => ['nullable', 'integer', 'exists:villages,id'],
            'season'           => ['required', 'string', 'in:rainy,dry,transition'],
            'year'             => ['required', 'integer', 'min:2020', 'max:2035'],
            'planting_start'   => ['required', 'date'],
            'planting_end'     => ['required', 'date', 'after_or_equal:planting_start'],
            'planting_pattern' => ['nullable', 'string', 'max:100'],
            'rice_variety'     => ['nullable', 'string', 'max:100'],
            'recommended_area' => ['nullable', 'numeric', 'min:0'],
            'status'           => ['nullable', 'string', 'in:draft,active,inactive'],
            'source'           => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }
}
