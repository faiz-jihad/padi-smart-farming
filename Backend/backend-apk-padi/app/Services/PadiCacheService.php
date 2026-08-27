<?php

namespace App\Services;

use App\Models\CommunityAlert;
use App\Models\CommunityReport;
use App\Models\MarketListing;
use App\Models\PurchaseContract;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class PadiCacheService
{
    const TTL_PRICES = 300;       // 5 minutes
    const TTL_RADAR = 180;        // 3 minutes
    const TTL_LISTINGS = 120;     // 2 minutes
    const TTL_SALES = 300;        // 5 minutes
    const TTL_WEATHER = 900;      // 15 minutes
    const TTL_KB = 86400;         // 24 hours

    /**
     * Get or cache real-time GKP & GKG price statistics.
     */
    public static function getMarketPriceTicker(): array
    {
        return self::remember('padi:market:price_ticker', self::TTL_PRICES, function () {
            $latestGkp = MarketListing::query()
                ->select(['id', 'commodity', 'price_per_unit', 'unit', 'published_at'])
                ->where('commodity', 'like', '%GKP%')
                ->where('status', 'published')
                ->latest('published_at')
                ->first();

            $latestGkg = MarketListing::query()
                ->select(['id', 'commodity', 'price_per_unit', 'unit', 'published_at'])
                ->where('commodity', 'like', '%GKG%')
                ->where('status', 'published')
                ->latest('published_at')
                ->first();

            return [
                'gkp_price' => $latestGkp ? (float) $latestGkp->price_per_unit : 6800.0,
                'gkg_price' => $latestGkg ? (float) $latestGkg->price_per_unit : 7900.0,
                'total_listings' => MarketListing::where('status', 'published')->count(),
                'updated_at' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Invalidate market price & listings cache.
     */
    public static function invalidateMarketCache(): void
    {
        self::forget('padi:market:price_ticker');
        self::forget('padi:market:published_listings');
        self::forget('padi:market:listings_v2');
    }

    /**
     * Invalidate sales reports & contracts cache for relevant parties.
     */
    public static function invalidateContractAndSalesCache(?int $farmerId = null, ?int $partnerId = null): void
    {
        self::invalidateMarketCache();

        if ($farmerId) {
            self::forget("padi:sales:farmer:{$farmerId}:all");
            self::forget("padi:sales:farmer:{$farmerId}:month");
            self::forget("padi:sales:farmer:{$farmerId}:season");
            self::forget("padi:contracts:farmer:{$farmerId}");
        }

        if ($partnerId) {
            self::forget("padi:sales:partner:{$partnerId}:all");
            self::forget("padi:sales:partner:{$partnerId}:month");
            self::forget("padi:sales:partner:{$partnerId}:season");
            self::forget("padi:contracts:partner:{$partnerId}");
        }
    }

    /**
     * Get or cache active community radar alerts.
     */
    public static function getCommunityRadarSummary(): array
    {
        return self::remember('padi:radar:summary', self::TTL_RADAR, function () {
            $activeAlerts = CommunityAlert::where('status', 'active')
                ->latest('published_at')
                ->limit(20)
                ->get();

            $latestReports = CommunityReport::with([
                    'farmer:id,name,phone',
                    'scan:id,disease_name,confidence,crop_stage'
                ])
                ->latest('created_at')
                ->limit(20)
                ->get();

            return [
                'alerts_count' => $activeAlerts->count(),
                'reports_count' => $latestReports->count(),
                'alerts' => $activeAlerts,
                'reports' => $latestReports,
                'cached_at' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Invalidate community radar cache.
     */
    public static function invalidateRadarCache(): void
    {
        self::forget('padi:radar:summary');
    }

    /**
     * Safe remember wrapper with graceful Redis/driver fallback.
     */
    public static function remember(string $key, int $ttlSeconds, Closure $callback)
    {
        try {
            return Cache::remember($key, $ttlSeconds, $callback);
        } catch (Throwable $e) {
            Log::warning("[PadiCache] Cache driver fallback triggered for key: {$key} - {$e->getMessage()}");
            return $callback();
        }
    }

    /**
     * Safe cache forget wrapper.
     */
    public static function forget(string $key): void
    {
        try {
            Cache::forget($key);
        } catch (Throwable $e) {
            Log::warning("[PadiCache] Forget failed for key: {$key}");
        }
    }
}
