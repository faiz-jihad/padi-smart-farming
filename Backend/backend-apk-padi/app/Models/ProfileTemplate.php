<?php

namespace App\Models;

use App\Enums\ProfileTemplateStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfileTemplate extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'thumbnail_path',
        'preview_image_path',
        'is_premium',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'is_premium' => 'boolean',
        'status'     => ProfileTemplateStatus::class,
        'sort_order' => 'integer',
    ];

    public function farmerPublicProfiles(): HasMany
    {
        return $this->hasMany(FarmerPublicProfile::class);
    }

    public function isActive(): bool
    {
        return $this->status === ProfileTemplateStatus::Active;
    }

    public function scopeActive($query)
    {
        return $query->where('status', ProfileTemplateStatus::Active->value);
    }
}
