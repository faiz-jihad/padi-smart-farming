<?php

namespace App\Http\Requests\Api\V1\CommunityReport;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'scan_id' => [
                'required',
                'integer',
                'exists:disease_scans,id',
            ],
            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
            'radius_km' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
            'consent_given' => [
                'required',
                'boolean',
                'accepted',
            ],
        ];
    }
}
