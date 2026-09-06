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
        $canViewRejection = $user && (
            $user->id === $this->created_by ||
            $user->role === 'admin' ||
            (method_exists($user, 'hasRole') && $user->hasRole('admin'))
        );

        $isRegistered = $user ? (isset($this->is_user_registered) ? (bool) $this->is_user_registered : $this->isRegisteredBy($user)) : false;
        $isCreator = $user ? ((int) $user->id === (int) $this->created_by) : false;
        $isApproved = ($this->approval_status ?? 'approved') === 'approved';
        $isFull = $this->quota > 0 && $this->registered_count >= $this->quota;
        $canRegister = $user && $isApproved && !$isCreator && !$isRegistered && !$isFull;

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
            'source' => $this->source ?? 'official',
            'approval_status' => $this->approval_status ?? 'approved',
            'rejection_reason' => $canViewRejection ? $this->rejection_reason : null,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'created_by' => $this->created_by,
            'is_event_creator' => $isCreator,
            'can_register' => $canRegister,
            'is_registered' => $isRegistered,
            'ticket_code' => $user ? $this->getRegistrationForUser($user)?->ticket_code : null,
            'ticket_status' => $user ? $this->getRegistrationForUser($user)?->ticket_status : null,
            'registered_at' => $user ? $this->getRegistrationForUser($user)?->registered_at?->toIso8601String() : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}


