<?php

namespace App\Services\Public;

use App\Enums\ProfileWebsiteStatus;
use App\Models\FarmerPublicProfile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PublishFarmerProfileService
{
    public function __construct(
        private readonly SubdomainAvailabilityService $subdomainService,
    ) {}

    /**
     * Publish a farmer's public profile.
     * Validates all requirements before publishing.
     *
     * @throws RuntimeException
     */
    public function publish(FarmerPublicProfile $profile): void
    {
        $this->validateReadyToPublish($profile);

        DB::transaction(function () use ($profile) {
            $profile->update([
                'website_status' => ProfileWebsiteStatus::Published->value,
                'published_at'   => now(),
            ]);
        });
    }

    /**
     * Unpublish (revert to draft).
     */
    public function unpublish(FarmerPublicProfile $profile): void
    {
        $profile->update([
            'website_status' => ProfileWebsiteStatus::Draft->value,
        ]);
    }

    /**
     * Suspend a profile (admin only).
     */
    public function suspend(FarmerPublicProfile $profile): void
    {
        $profile->update([
            'website_status' => ProfileWebsiteStatus::Suspended->value,
        ]);
    }

    /**
     * Restore a suspended profile to draft.
     */
    public function restore(FarmerPublicProfile $profile): void
    {
        $profile->update([
            'website_status' => ProfileWebsiteStatus::Draft->value,
        ]);
    }

    /**
     * @throws RuntimeException if profile is not ready to publish.
     */
    private function validateReadyToPublish(FarmerPublicProfile $profile): void
    {
        if (empty($profile->subdomain)) {
            throw new RuntimeException('Subdomain belum dipilih. Silakan tentukan subdomain terlebih dahulu.');
        }

        if (empty($profile->business_name)) {
            throw new RuntimeException('Nama usaha belum diisi.');
        }

        if (! $profile->profile_template_id) {
            throw new RuntimeException('Template website belum dipilih.');
        }

        if (! $profile->template?->isActive()) {
            throw new RuntimeException('Template yang dipilih saat ini tidak tersedia.');
        }

        if (! $this->subdomainService->isAvailable($profile->subdomain, $profile->id)) {
            throw new RuntimeException('Subdomain sudah tidak tersedia. Silakan pilih subdomain lain.');
        }
    }
}
