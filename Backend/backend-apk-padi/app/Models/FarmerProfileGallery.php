<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmerProfileGallery extends Model
{
    protected $table = 'farmer_profile_gallery';

    protected $fillable = [
        'farmer_public_profile_id',
        'image_path',
        'caption',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(FarmerPublicProfile::class, 'farmer_public_profile_id');
    }

    public function imageUrl(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
