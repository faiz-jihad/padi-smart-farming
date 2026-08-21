<?php

namespace App\Http\Requests\Api\V1\Harvest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHarvestRequest extends FormRequest
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
            'crop_season_id' => ['sometimes', 'required', 'integer', 'exists:crop_seasons,id'],
            'harvest_date' => ['sometimes', 'required', 'date'],
            'quantity' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'unit' => ['sometimes', 'required', 'string', 'max:20'],
            'quality_grade' => ['nullable', 'string', 'max:50'],
            'moisture_percent' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }
}