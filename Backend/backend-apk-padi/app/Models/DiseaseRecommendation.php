<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiseaseRecommendation extends Model
{
    protected $fillable = [
        'scan_id',
        'source',
        'llm_model',
        'explanation',
        'action',
        'safety_note',
    ];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(DiseaseScan::class, 'scan_id');
    }

    /**
     * Backward-compatibility accessor for treatment_title.
     */
    public function getTreatmentTitleAttribute(): ?string
    {
        return $this->action;
    }

    /**
     * Backward-compatibility accessor for action_summary.
     */
    public function getActionSummaryAttribute(): ?string
    {
        return $this->action;
    }
}
