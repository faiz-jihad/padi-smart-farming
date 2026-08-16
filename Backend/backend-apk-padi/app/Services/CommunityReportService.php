<?php

namespace App\Services;

use App\Models\CommunityReport;
use Illuminate\Database\Eloquent\Collection;

class CommunityReportService
{
    public function getReports(): Collection
    {
        return CommunityReport::with('farmer')->get();
    }
}