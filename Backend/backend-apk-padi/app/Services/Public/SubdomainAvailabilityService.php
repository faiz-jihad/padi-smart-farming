<?php

namespace App\Services\Public;

use App\Models\FarmerPublicProfile;
use Illuminate\Support\Str;

class SubdomainAvailabilityService
{
    /**
     * Check if a subdomain is available to be claimed.
     */
    public function isAvailable(string $subdomain, ?int $excludeProfileId = null): bool
    {
        if (! $this->isValidFormat($subdomain)) {
            return false;
        }

        if ($this->isReserved($subdomain)) {
            return false;
        }

        $query = FarmerPublicProfile::where('subdomain', $subdomain);

        if ($excludeProfileId !== null) {
            $query->where('id', '!=', $excludeProfileId);
        }

        return ! $query->exists();
    }

    /**
     * Validate subdomain format rules.
     */
    public function isValidFormat(string $subdomain): bool
    {
        // lowercase letters, numbers, hyphens only
        // no leading/trailing hyphens
        // 3–40 chars
        return (bool) preg_match('/^[a-z0-9][a-z0-9\-]{1,38}[a-z0-9]$/', $subdomain)
            || (bool) preg_match('/^[a-z0-9]{3,40}$/', $subdomain);
    }

    /**
     * Check if the subdomain is in the reserved list.
     */
    public function isReserved(string $subdomain): bool
    {
        $reserved = config('reserved-subdomains.reserved', []);

        return in_array(strtolower($subdomain), $reserved, true);
    }

    /**
     * Normalize a business name into a suggested subdomain slug.
     *
     * "Pak Joko Farm" → "pak-joko-farm"
     */
    public function suggestFromBusinessName(string $businessName): string
    {
        $slug = Str::slug($businessName, '-');

        // Truncate to 40 chars
        $slug = Str::limit($slug, 40, '');

        // Remove trailing hyphens
        $slug = rtrim($slug, '-');

        return $slug;
    }

    /**
     * Return a reason why a subdomain is not available.
     */
    public function unavailableReason(string $subdomain): string
    {
        if (! $this->isValidFormat($subdomain)) {
            return 'Subdomain hanya boleh mengandung huruf kecil, angka, dan tanda hubung (-), minimal 3 karakter.';
        }

        if ($this->isReserved($subdomain)) {
            return 'Subdomain tersebut tidak dapat digunakan.';
        }

        return 'Subdomain sudah digunakan oleh petani lain.';
    }
}
