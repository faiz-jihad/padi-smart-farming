<?php

namespace App\Http\Requests\Farmer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileSectionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('farmer')->check();
    }

    public function rules(): array
    {
        $keys = array_keys(\App\Models\FarmerPublicProfile::DEFAULT_SECTION_SETTINGS);

        $rules = [];
        foreach ($keys as $key) {
            $rules["section_settings.{$key}"] = ['boolean'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        // Checkboxes that are unchecked won't be sent — default to false
        $keys = array_keys(\App\Models\FarmerPublicProfile::DEFAULT_SECTION_SETTINGS);
        $settings = [];

        foreach ($keys as $key) {
            $settings[$key] = $this->boolean("section_settings.{$key}", false);
        }

        $this->merge(['section_settings' => $settings]);
    }
}
