<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSoilRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $isAdmin = ($user->role === 'admin' || (method_exists($user, 'hasRole') && $user->hasRole('admin')));
        $isOfficer = ($user->role === 'extension_officer' || (method_exists($user, 'hasRole') && $user->hasRole('extension_officer')));

        if ($isAdmin || $isOfficer) {
            return true;
        }

        $isFarmer = ($user->role === 'farmer' || (method_exists($user, 'hasRole') && $user->hasRole('farmer')));
        if ($isFarmer) {
            $farmId = $this->input('farm_id');
            if (! $farmId) {
                return true;
            }

            $farm = \App\Models\Farm::find($farmId);

            return $farm && $farm->farmer_user_id === $user->id;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'farm_id' => 'required|integer|exists:farms,id',
            'sample_code' => 'nullable|string|max:50|unique:soil_detections,sample_code',
            'ph_level' => 'required|numeric|min:3|max:11',
            'nitrogen_ppm' => 'required|numeric|min:0|max:1000',
            'phosphorus_ppm' => 'required|numeric|min:0|max:500',
            'potassium_ppm' => 'required|numeric|min:0|max:1000',
            'moisture_percentage' => 'required|numeric|min:0|max:100',
            'organic_matter_percentage' => 'required|numeric|min:0|max:30',
            'soil_temp_celsius' => 'nullable|numeric|min:-10|max:60',
            'soil_type' => 'required|string|in:alluvial,clay,loam,sandy_loam,peat,latosol',
            'tested_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'sync_agromonitoring' => 'nullable|boolean',
        ];
    }
}
