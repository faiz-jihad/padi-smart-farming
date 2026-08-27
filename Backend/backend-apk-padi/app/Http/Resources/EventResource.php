<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'event_date' => $this->event_date?->format('Y-m-d'),
            'event_time' => $this->event_time,
            'location_name' => $this->location_name,
            'location_address' => $this->location_address,
            'is_online' => (bool) $this->is_online,
            'organizer' => $this->organizer,
            'speaker' => $this->speaker,
            'quota' => (int) $this->quota,
            'registered_count' => (int) $this->registered_count,
            'price_type' => $this->price_type,
            'asset_image' => $this->asset_image ?? 'assets/images/onboarding_1.jpeg',
            'contact_person' => $this->contact_person,
            'status' => $this->status,
            'is_registered' => $user ? (isset($this->is_user_registered) ? (bool) $this->is_user_registered : $this->isRegisteredBy($user)) : false,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
