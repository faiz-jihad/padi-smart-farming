<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PplValidation extends Model
{
    protected $fillable = [
        'scan_id',
        'ppl_id',
        'status',
        'notes',
        'validated_at',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    public function ppl(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ppl_id');
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(DiseaseScan::class, 'scan_id');
    }

    /**
     * Resolve the farmer who submitted this scan.
     */
    public function farmer(): BelongsTo
    {
        // farmer is accessed through the disease_scan -> farmer_id
        return $this->belongsTo(User::class, 'scan_id', 'id')
            ->join('disease_scans', 'disease_scans.id', '=', 'ppl_validations.scan_id')
            ->select('users.*');
    }
}