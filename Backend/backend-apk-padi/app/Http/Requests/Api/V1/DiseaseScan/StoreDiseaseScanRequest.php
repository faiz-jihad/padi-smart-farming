<?php

namespace App\Http\Requests\Api\V1\DiseaseScan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiseaseScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'farm_id' => [
                'required',
                'integer',
                Rule::exists('farms', 'id')->where(
                    fn ($query) => $query->where('farmer_user_id', $this->user()?->id)
                ),
            ],
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'plant_age_days' => [
                'nullable',
                'integer',
                'min:0',
                'max:180',
            ],
            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
        ];
    }
}
