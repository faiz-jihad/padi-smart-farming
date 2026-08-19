<?php

namespace App\Http\Requests\Api\V1\FarmActivity;

use Illuminate\Foundation\Http\FormRequest;

class StoreFarmActivityRequest extends FormRequest
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
            'crop_season_id' => ['required', 'integer', 'exists:crop_seasons,id'],
            'type'           => ['required', 'string', 'in:land_preparation,planting,fertilizing,spraying,irrigation,other'],
            'occurred_at'    => ['required', 'date'],
            'notes'          => ['nullable', 'string'],
            'cost'           => ['nullable', 'integer', 'min:0'],
        ];
    }
}
