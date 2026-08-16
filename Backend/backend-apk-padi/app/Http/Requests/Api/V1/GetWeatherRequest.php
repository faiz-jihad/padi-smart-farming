<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class GetWeatherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'farm_id' => 'required|integer|exists:farms,id',
            'units' => 'sometimes|in:metric,imperial',
            'lang' => 'sometimes|string|max:10',
            'force_refresh' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'farm_id.required' => 'Farm ID diperlukan',
            'farm_id.exists' => 'Farm tidak ditemukan',
            'units.in' => 'Satuan harus metric atau imperial',
        ];
    }
}
