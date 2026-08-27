<?php

namespace App\Services\Api;

use App\Models\AdminBroadcast;
use App\Models\AlertSubscription;
use App\Models\AuditLog;
use App\Models\CommunityReport;
use App\Models\ContractPayment;
use App\Models\DeviceToken;
use App\Models\FarmActivity;
use App\Models\FarmerProfile;
use App\Models\FertilizerRule;
use App\Models\Harvest;
use App\Models\ListingImage;
use App\Models\MarketListing;
use App\Models\MarketOffer;
use App\Models\Notification;
use App\Models\PartnerFavorite;
use App\Models\PplValidation;
use App\Models\PurchaseContract;
use App\Models\RiceVariety;
use App\Models\User;
use App\Models\WeatherSnapshot;

use Illuminate\Database\Eloquent\Collection;

class ApiResourceIndexService
{
    public function alertSubscriptions(): Collection
    {
        return AlertSubscription::query()->latest('id')->get();
    }

    public function adminBroadcasts(): Collection
{
    return AdminBroadcast::query()
        ->with('admin')
        ->where('status', 'published')
        ->where(function ($query): void {
            $query
                ->whereNull('published_at')
                ->orWhere('published_at', '<=', now());
        })
        ->where(function ($query): void {
            $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now());
        })
        ->where(function ($query): void {
            $query
                ->whereNull('target_role')
                ->orWhere('target_role', 'farmer');
        })
        ->latest('published_at')
        ->get();
}

    public function auditLogs(): Collection
    {
        return AuditLog::query()->with('user')->latest()->get();
    }

    public function communityReports(): Collection
    {
        return CommunityReport::query()
            ->with(['farmer:id,name,phone', 'scan:id,predicted_class,confidence,image_url'])
            ->latest('reported_at')
            ->limit(50)
            ->get();
    }

    public function contractPayments(?User $user = null): Collection
    {
        $query = ContractPayment::query()->with('contract');

        if (! $user) {
            return $query->get();
        }

        $isAdmin = ($user->role === 'admin' || (method_exists($user, 'hasRole') && $user->hasRole('admin')));

        if ($isAdmin) {
            return $query->get();
        }

        return $query->whereHas('contract', function ($q) use ($user): void {
            $q->where('farmer_id', $user->id)
                ->orWhere('partner_id', $user->id);
        })->get();
    }

    public function deviceTokens(): Collection
    {
        return DeviceToken::query()->with('user')->get();
    }

    public function farmerProfiles(): Collection
    {
        return FarmerProfile::query()->with('user')->latest('id')->get();
    }

    public function farmActivities(): Collection
    {
        return FarmActivity::query()->latest('occurred_at')->get();
    }

    public function fertilizerRules(): Collection
    {
        return FertilizerRule::query()->with('variety')->orderBy('phase')->get();
    }

    public function harvests(): Collection
    {
        return Harvest::query()->with('cropSeason')->get();
    }

    public function listingImages(): Collection
    {
        return ListingImage::query()->with('listing')->get();
    }

    public function marketListings(): Collection
    {
        return MarketListing::query()
            ->with(['farmer', 'farm', 'cropSeason', 'harvest', 'images', 'offers'])
            ->get();
    }

    public function marketOffers(): Collection
    {
        return MarketOffer::query()->with(['listing', 'partner'])->get();
    }

    public function notifications(): Collection
    {
        return Notification::query()->with('user')->get();
    }

    public function partnerFavorites(): Collection
    {
        return PartnerFavorite::query()->with(['partner', 'listing'])->get();
    }

    public function pplValidations(): Collection
    {
        return PplValidation::query()->with('ppl')->get();
    }

    public function purchaseContracts(): Collection
    {
        return PurchaseContract::query()->with(['listing', 'farmer', 'partner', 'offer'])->get();
    }

    public function riceVarieties(): Collection
    {
        return RiceVariety::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function weatherSnapshots(): Collection
    {
        return WeatherSnapshot::query()->with('farm')->latest('observed_at')->get();
    }
}
