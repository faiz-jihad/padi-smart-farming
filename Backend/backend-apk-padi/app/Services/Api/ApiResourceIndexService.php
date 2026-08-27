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
            ->with('admin:id,name,email')
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
        return AuditLog::query()->with('user:id,name,email,role')->latest()->limit(100)->get();
    }

    public function communityReports(): Collection
    {
        return CommunityReport::query()
            ->with(['farmer:id,name,phone', 'scan:id,predicted_class,confidence,image_url'])
            ->latest('reported_at')
            ->limit(50)
            ->get();
    }

    public function contractPayments(): Collection
    {
        return ContractPayment::query()->with('contract:id,quantity,agreed_price,total_amount,status')->latest('id')->get();
    }

    public function deviceTokens(): Collection
    {
        return DeviceToken::query()->with('user:id,name,role')->get();
    }

    public function farmerProfiles(): Collection
    {
        return FarmerProfile::query()->with('user:id,name,phone,email')->latest('id')->get();
    }

    public function farmActivities(): Collection
    {
        return FarmActivity::query()->latest('occurred_at')->limit(50)->get();
    }

    public function fertilizerRules(): Collection
    {
        return FertilizerRule::query()->with('variety:id,name,category')->orderBy('phase')->get();
    }

    public function harvests(): Collection
    {
        return Harvest::query()->with('cropSeason:id,variety_id,farm_id,status')->latest('harvest_date')->get();
    }

    public function listingImages(): Collection
    {
        return ListingImage::query()->with('listing:id,commodity,price_per_unit')->get();
    }

    public function marketListings(): Collection
    {
        return MarketListing::query()
            ->where('status', 'published')
            ->with([
                'farmer:id,name,phone,email',
                'farm:id,name,area_ha,latitude,longitude',
                'cropSeason:id,variety_id,status',
                'harvest:id,moisture_percent,quality_grade,quantity',
                'images:id,market_listing_id,image_url,is_primary',
                'offers:id,listing_id,partner_id,offered_price,quantity,status',
            ])
            ->latest('published_at')
            ->get();
    }

    public function marketOffers(): Collection
    {
        return MarketOffer::query()
            ->with([
                'listing:id,farmer_id,commodity,unit,price_per_unit,image_url,status',
                'partner:id,name,phone,email',
            ])
            ->latest('created_at')
            ->get();
    }

    public function notifications(): Collection
    {
        return Notification::query()->with('user:id,name,role')->latest('created_at')->limit(50)->get();
    }

    public function partnerFavorites(): Collection
    {
        return PartnerFavorite::query()
            ->with([
                'partner:id,name,email',
                'listing:id,commodity,price_per_unit,unit,image_url,status',
            ])
            ->get();
    }

    public function pplValidations(): Collection
    {
        return PplValidation::query()->with('ppl:id,name,phone,email')->latest('id')->get();
    }

    public function purchaseContracts(): Collection
    {
        return PurchaseContract::query()
            ->with([
                'listing:id,commodity,unit,price_per_unit,image_url,status',
                'farmer:id,name,phone,email',
                'partner:id,name,phone,email',
                'offer:id,listing_id,partner_id,offered_price,quantity,status',
            ])
            ->latest('contracted_at')
            ->get();
    }

    public function riceVarieties(): Collection
    {
        return RiceVariety::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function weatherSnapshots(): Collection
    {
        return WeatherSnapshot::query()->with('farm:id,name,latitude,longitude')->latest('observed_at')->limit(50)->get();
    }
}
