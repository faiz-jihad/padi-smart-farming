<?php

namespace App\Http\Requests\Api\V1\FarmActivity;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFarmActivityRequest extends FormRequest
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
            'type'           => ['sometimes', 'required', 'string', 'in:land_preparation,planting,fertilizing,spraying,irrigation,other'],
            'occurred_at'    => ['sometimes', 'required', 'date'],
            'notes'          => ['nullable', 'string'],
            'cost'           => ['nullable', 'integer', 'min:0'],
            'source'         => ['sometimes', 'nullable', 'string', 'in:VOICE,MANUAL,AI_RECOMMENDATION'],
            'status'         => ['sometimes', 'nullable', 'string', 'in:COMPLETED,PENDING,CANCELLED'],
            'sync_status'    => ['sometimes', 'nullable', 'string', 'in:pending,synced,failed'],
        ];
    }
}
