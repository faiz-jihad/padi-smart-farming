<?php

namespace App\Http\Requests\Api\V1\CropSeason;

use Illuminate\Foundation\Http\FormRequest;

class StoreCropSeasonRequest extends FormRequest
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
            'farm_id' => [
                'required',
                'integer',
                'exists:farms,id',
            ],

            'variety_id' => [
                'nullable',
                'integer',
                'exists:rice_varieties,id',
            ],

            'planned_planting_date' => [
                'nullable',
                'date',
            ],

            'planting_date' => [
                'nullable',
                'date',
                'after_or_equal:planned_planting_date',
            ],
            
            'estimated_harvest_date' => [
                'nullable',
                'date',
                'after_or_equal:planting_date',
            ],

            'status' => [
                'nullable',
                'string',
                'in:planned,active,completed,cancelled',
            ],
        ];
    }
}