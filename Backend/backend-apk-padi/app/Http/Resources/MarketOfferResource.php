<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketOfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        $isFarmer = $user
            && $this->listing
            && $this->listing->farmer_id === $user->id;

        $isPartner = $user
            && $this->partner_id === $user->id;

        $isAdmin = $user
            && $user->role === 'admin';

        $canAct = false;

        if ($isAdmin) {
            $canAct = true;
        } elseif ($isFarmer && $this->last_offer_by === 'buyer') {
            $canAct = true;
        } elseif ($isPartner && $this->last_offer_by === 'farmer') {
            $canAct = true;
        }

        if (in_array($this->status, ['accepted', 'rejected'], true)) {
            $canAct = false;
        }

        return [
            'id' => $this->id,
            'listing_id' => $this->listing_id,
            'partner_id' => $this->partner_id,
            'offered_price' => $this->offered_price,
            'quantity' => $this->quantity,
            'message' => $this->message,
            'status' => $this->status,
            'last_offer_by' => $this->last_offer_by,
            'can_act' => $canAct,

            'partner' => $this->whenLoaded('partner', function () {
                return [
                    'id' => $this->partner->id,
                    'name' => $this->partner->name,
                    'email' => $this->partner->email,
                    'phone' => $this->partner->phone,
                ];
            }),

            'listing' => $this->whenLoaded('listing', function () {
                return [
                    'id' => $this->listing->id,
                    'commodity' => $this->listing->commodity,
                    'quantity' => $this->listing->quantity,
                    'unit' => $this->listing->unit,
                    'price_per_unit' => $this->listing->price_per_unit,
                    'status' => $this->listing->status,
                ];
            }),
        ];
    }
}
