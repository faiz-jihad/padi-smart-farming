<?php

namespace App\Http\Requests\Api\V1\MarketListing;

use Illuminate\Foundation\Http\FormRequest;

class StoreMarketListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('farmer') || $this->user()?->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'farm_id' => [
                'required',
                'integer',
                'exists:farms,id',
            ],
            'crop_season_id' => [
                'required',
                'integer',
                'exists:crop_seasons,id',
            ],
            'harvest_id' => [
                'nullable',
                'integer',
                'exists:harvests,id',
            ],
            'commodity' => [
                'required',
                'string',
                'max:100',
            ],
            'quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'unit' => [
                'required',
                'string',
                'max:30',
            ],
            'price_per_unit' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'sales_link' => [
                'nullable',
                'string',
                'max:2048',
            ],
            'image_url' => [
                'nullable',
                'string',
                'max:2048',
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:today',
            ],
        ];
    }
}
