<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\CropSeason;
use App\Models\DiseaseScan;
use App\Models\Farm;
use App\Models\FarmActivity;
use App\Models\Harvest;
use App\Services\Government\GovernmentDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgricultureOverviewController extends Controller
{
    public function __construct(
        protected GovernmentDataService $dataService
    ) {}

    public function index(Request $request): View
    {
        $farmer = auth('farmer')->user() ?? auth()->user();
        
        $farms = Farm::where('farmer_user_id', $farmer->id)
            ->with(['cropSeasons.variety', 'cropSeasons.harvests', 'province', 'regency', 'district', 'village'])
            ->get();

        $farmIds = $farms->pluck('id')->toArray();

        $recentActivities = FarmActivity::whereHas('cropSeason', fn ($q) => $q->whereIn('farm_id', $farmIds))
            ->with(['cropSeason.variety', 'cropSeason.farm'])
            ->latest('occurred_at')
            ->limit(10)
            ->get();

        $recentScans = DiseaseScan::whereIn('farm_id', $farmIds)
            ->with(['farm', 'recommendation', 'pplValidation'])
            ->latest('scanned_at')
            ->limit(10)
            ->get();

        $farmsProductivity = $farms->map(function (Farm $farm) {
            return $this->dataService->formatFarmProductivity($farm);
        });

        return view('farmer.overview.index', [
            'title'             => 'Ringkasan Pertanian & Produktivitas',
            'farmer'            => $farmer,
            'farms'             => $farms,
            'recentActivities'  => $recentActivities,
            'recentScans'       => $recentScans,
            'farmsProductivity' => $farmsProductivity,
        ]);
    }
}
