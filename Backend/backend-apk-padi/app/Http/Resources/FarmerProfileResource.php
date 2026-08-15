<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmerProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'village' => $this->village,
            'district' => $this->district,
            'regency' => $this->regency,
            'province' => $this->province,
            'experience_years' => $this->experience_years,

            'user' => $this->whenLoaded('user', function (): array {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'phone' => $this->user->phone,
                    'role' => $this->user->role,
                    'status' => $this->user->status,
                    'verification_status' => $this->user->verification_status,
                ];
            }),
        ];
    }
}