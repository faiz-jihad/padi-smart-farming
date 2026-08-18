<?php

namespace App\Models;

use App\Enums\ProfileVerificationStatus;
use App\Enums\ProfileWebsiteStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FarmerPublicProfile extends Model
{
    /**
     * Default section settings — privacy-safe defaults.
     * Sensitive data is OFF by default.
     */
    public const DEFAULT_SECTION_SETTINGS = [
        'show_products'        => true,   // marketplace listings
        'show_fields'          => false,  // farm coordinates — private
        'show_harvests'        => false,  // harvest history — private
        'show_productivity'    => false,  // ton/ha stats — private
        'show_location'        => true,   // general region name only
        'show_gallery'         => true,
        'show_contact'         => true,
        'show_active_variety'  => false,  // crop variety — private
    ];

    protected $fillable = [
        'farmer_id',
        'profile_template_id',
        'subdomain',
        'business_name',
        'headline',
        'description',
        'logo_path',
        'cover_image_path',
        'whatsapp',
        'public_email',
        'public_phone',
        'public_address',
        'instagram_url',
        'facebook_url',
        'section_settings',
        'website_status',
        'verification_status',
        'published_at',
    ];

    protected $casts = [
        'section_settings'    => 'array',
        'website_status'      => ProfileWebsiteStatus::class,
        'verification_status' => ProfileVerificationStatus::class,
        'published_at'        => 'datetime',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProfileTemplate::class, 'profile_template_id');
    }

    public function gallery(): HasMany
    {
        return $this->hasMany(FarmerProfileGallery::class)
            ->where('status', 'active')
            ->orderBy('sort_order');
    }

    // ─── Section Settings Helpers ───────────────────────────────────────────

    /**
     * Get a specific section visibility setting, falling back to default.
     */
    public function getSectionSetting(string $key): bool
    {
        $settings = $this->section_settings ?? [];
        $defaults = self::DEFAULT_SECTION_SETTINGS;

        return (bool) ($settings[$key] ?? $defaults[$key] ?? false);
    }

    /**
     * Get all section settings merged with defaults.
     *
     * @return array<string, bool>
     */
    public function resolvedSectionSettings(): array
    {
        $settings = $this->section_settings ?? [];

        return array_merge(self::DEFAULT_SECTION_SETTINGS, $settings);
    }

    // ─── Status Helpers ─────────────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->website_status === ProfileWebsiteStatus::Published;
    }

    public function isVerified(): bool
    {
        return $this->verification_status === ProfileVerificationStatus::Verified;
    }

    public function isSuspended(): bool
    {
        return $this->website_status === ProfileWebsiteStatus::Suspended;
    }

    // ─── Public URL ─────────────────────────────────────────────────────────

    /**
     * Return the public URL for this farmer's profile.
     * Returns null if no subdomain is set.
     */
    public function publicUrl(): ?string
    {
        if (! $this->subdomain) {
            return null;
        }

        $scheme = app()->environment('production') ? 'https' : 'http';
        $base = config('domains.base', 'localhost');

        return "{$scheme}://{$this->subdomain}.{$base}";
    }

    /**
     * Normalized WhatsApp link for CTA.
     */
    public function whatsappUrl(): ?string
    {
        if (! $this->whatsapp) {
            return null;
        }

        $number = preg_replace('/[^0-9]/', '', $this->whatsapp);

        return "https://wa.me/{$number}";
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('website_status', ProfileWebsiteStatus::Published->value);
    }

    public function scopeBySubdomain($query, string $subdomain)
    {
        return $query->where('subdomain', $subdomain);
    }
}
