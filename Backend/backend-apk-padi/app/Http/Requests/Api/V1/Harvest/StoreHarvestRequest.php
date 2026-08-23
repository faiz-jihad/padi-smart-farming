<?php

namespace App\Http\Requests\Api\V1\Harvest;

use Illuminate\Foundation\Http\FormRequest;

class StoreHarvestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'crop_season_id' => ['required', 'integer', 'exists:crop_seasons,id'],
            'harvest_date' => ['required', 'date'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string'],
            'quality_grade' => ['nullable', 'string'],
            'moisture_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
