<?php

namespace App\Http\Requests\Api\V1\MarketListing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMarketListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('farmer') ?? false;
    }

    public function rules(): array
    {
        return [
            'commodity' => [
                'sometimes',
                'string',
                'max:100',
            ],
            'quantity' => [
                'sometimes',
                'numeric',
                'gt:0',
            ],
            'unit' => [
                'sometimes',
                'string',
                'max:20',
            ],
            'price_per_unit' => [
                'sometimes',
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
        ];
    }
}