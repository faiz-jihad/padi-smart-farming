<?php

namespace App\Services\Public;

use App\Models\FarmerPublicProfile;
use App\Models\ProfileTemplate;

class FarmerProfileTemplateResolver
{
    /**
     * Whitelist mapping: template code → Blade view path.
     * NEVER resolve arbitrary view paths from user input.
     *
     * @var array<string, string>
     */
    private const ALLOWED_TEMPLATES = [
        'harvest-prestige' => 'public.farmer.templates.harvest-prestige.index',
        'agri-modern'      => 'public.farmer.templates.agri-modern.index',
        'marketplace-pro'  => 'public.farmer.templates.marketplace-pro.index',
    ];

    private const DEFAULT_TEMPLATE = 'harvest-prestige';

    /**
     * Resolve the Blade view path for a given template code.
     * Falls back to the default template if code is not whitelisted.
     */
    public function resolve(?string $templateCode): string
    {
        if ($templateCode !== null && isset(self::ALLOWED_TEMPLATES[$templateCode])) {
            return self::ALLOWED_TEMPLATES[$templateCode];
        }

        return self::ALLOWED_TEMPLATES[self::DEFAULT_TEMPLATE];
    }

    /**
     * Resolve view from a FarmerPublicProfile model.
     */
    public function resolveFromProfile(FarmerPublicProfile $profile): string
    {
        $code = $profile->template?->code;

        return $this->resolve($code);
    }

    /**
     * Check if a template code is allowed.
     */
    public function isAllowed(string $templateCode): bool
    {
        return isset(self::ALLOWED_TEMPLATES[$templateCode]);
    }

    /**
     * Return all allowed template codes.
     *
     * @return list<string>
     */
    public function allowedCodes(): array
    {
        return array_keys(self::ALLOWED_TEMPLATES);
    }
}
