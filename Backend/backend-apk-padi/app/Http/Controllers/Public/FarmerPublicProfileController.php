<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\FarmerPublicProfile;
use App\Services\Public\FarmerPublicProfileDataService;
use App\Services\Public\FarmerProfileTemplateResolver;

class FarmerPublicProfileController extends Controller
{
    public function __construct(
        private readonly FarmerPublicProfileDataService $dataService,
        private readonly FarmerProfileTemplateResolver $templateResolver,
    ) {}

    public function show(string $subdomain)
    {
        // Resolve the profile — must be published and exist
        $profile = FarmerPublicProfile::bySubdomain($subdomain)
            ->published()
            ->with(['farmer', 'template', 'gallery'])
            ->first();

        abort_if(! $profile, 404);

        // Build public-safe data payload
        $data = $this->dataService->buildPublicData($profile);

        // Resolve Blade template via whitelist — never from user input
        $view = $this->templateResolver->resolveFromProfile($profile);

        return view($view, array_merge($data, [
            'isPreview' => false,
        ]));
    }
}
