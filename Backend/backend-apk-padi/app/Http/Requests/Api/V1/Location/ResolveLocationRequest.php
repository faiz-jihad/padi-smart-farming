<?php

namespace App\Http\Requests\Api\V1\Location;

use Illuminate\Foundation\Http\FormRequest;

class ResolveLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        return [
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
