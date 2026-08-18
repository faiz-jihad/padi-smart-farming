<?php

namespace Tests\Feature;

use App\Enums\ProfileVerificationStatus;
use App\Enums\ProfileWebsiteStatus;
use App\Models\FarmerPublicProfile;
use App\Models\MarketListing;
use App\Models\ProfileTemplate;
use App\Models\User;
use App\Services\Public\SubdomainAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FarmerPublicProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\ProfileTemplateSeeder::class);
    }

    public function test_farmer_can_login_to_farmer_web_panel(): void
    {
        $farmer = User::factory()->create([
            'role'     => 'farmer',
            'status'   => 'active',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('farmer.login.submit'), [
            'email'    => $farmer->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('farmer.website.index'));
        $this->assertAuthenticatedAs($farmer, 'farmer');
    }

    public function test_non_farmer_cannot_login_to_farmer_panel(): void
    {
        $nonFarmer = User::factory()->create([
            'role'     => 'admin',
            'status'   => 'active',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('farmer.login.submit'), [
            'email'    => $nonFarmer->email,
            'password' => 'password123',
        ]);


        $response->assertSessionHasErrors('email');
        $this->assertGuest('farmer');
    }

    public function test_farmer_can_update_profile_info_and_upload_media(): void
    {
        Storage::fake('public');
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);

        $response = $this->actingAs($farmer, 'farmer')
            ->post(route('farmer.website.update'), [
                'business_name'  => 'Berkah Tani Organik',
                'headline'       => 'Padi Unggul Indramayu',
                'description'    => 'Petani padi berpengalaman lebih dari 10 tahun.',
                'whatsapp'       => '081234567890',
                'public_email'   => 'berkah@tani.com',
                'public_address' => 'Kec. Kandanghaur, Kab. Indramayu',
                'logo'           => UploadedFile::fake()->image('logo.jpg', 200, 200),
            ]);

        $response->assertRedirect(route('farmer.website.index'));

        $this->assertDatabaseHas('farmer_public_profiles', [
            'farmer_id'     => $farmer->id,
            'business_name' => 'Berkah Tani Organik',
            'whatsapp'      => '6281234567890', // Normalized
        ]);

        $profile = FarmerPublicProfile::where('farmer_id', $farmer->id)->first();
        $this->assertNotNull($profile->logo_path);
        Storage::disk('public')->assertExists($profile->logo_path);
    }

    public function test_subdomain_availability_checks_format_and_reserved_keywords(): void
    {
        $service = app(SubdomainAvailabilityService::class);

        // Valid
        $this->assertTrue($service->isAvailable('tani-makmur'));
        $this->assertTrue($service->isAvailable('pakjoko99'));

        // Invalid format
        $this->assertFalse($service->isAvailable('ab')); // < 3 chars
        $this->assertFalse($service->isAvailable('-badstart'));
        $this->assertFalse($service->isAvailable('badend-'));
        $this->assertFalse($service->isAvailable('with_underscore'));

        // Reserved subdomains
        $this->assertFalse($service->isAvailable('admin'));
        $this->assertFalse($service->isAvailable('api'));
        $this->assertFalse($service->isAvailable('www'));
        $this->assertFalse($service->isAvailable('marketplace'));
    }

    public function test_farmer_can_check_and_update_subdomain(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);

        // Check via AJAX
        $response = $this->actingAs($farmer, 'farmer')
            ->getJson(route('farmer.website.subdomain.check', ['subdomain' => 'petanimaju']));

        $response->assertOk()
            ->assertJsonPath('available', true)
            ->assertJsonPath('subdomain', 'petanimaju');

        // Save subdomain
        $postResponse = $this->actingAs($farmer, 'farmer')
            ->post(route('farmer.website.subdomain.update'), [
                'subdomain' => 'petanimaju',
            ]);

        $postResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('farmer_public_profiles', [
            'farmer_id' => $farmer->id,
            'subdomain' => 'petanimaju',
        ]);
    }

    public function test_farmer_can_select_template(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        $template = ProfileTemplate::where('code', 'harvest-prestige')->first();

        $response = $this->actingAs($farmer, 'farmer')
            ->post(route('farmer.website.template.select'), [
                'template_id' => $template->id,
            ]);

        $response->assertRedirect(route('farmer.website.index'));
        $this->assertDatabaseHas('farmer_public_profiles', [
            'farmer_id'           => $farmer->id,
            'profile_template_id' => $template->id,
        ]);
    }

    public function test_farmer_can_update_privacy_section_settings(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);

        $response = $this->actingAs($farmer, 'farmer')
            ->post(route('farmer.website.sections.update'), [
                'section_settings' => [
                    'show_products'     => '1',
                    'show_location'     => '1',
                    'show_harvests'     => '1',
                    'show_productivity' => '0',
                    'show_fields'       => '0',
                ],
            ]);

        $response->assertSessionHasNoErrors();
        $profile = FarmerPublicProfile::where('farmer_id', $farmer->id)->first();

        $this->assertTrue($profile->getSectionSetting('show_products'));
        $this->assertTrue($profile->getSectionSetting('show_harvests'));
        $this->assertFalse($profile->getSectionSetting('show_productivity'));
        $this->assertFalse($profile->getSectionSetting('show_fields'));
    }

    public function test_farmer_cannot_publish_without_required_fields(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        FarmerPublicProfile::create([
            'farmer_id'     => $farmer->id,
            'business_name' => 'Belum Lengkap',
            // Missing template and subdomain
        ]);

        $response = $this->actingAs($farmer, 'farmer')
            ->post(route('farmer.website.publish'));

        $response->assertSessionHasErrors('publish');
        $this->assertDatabaseHas('farmer_public_profiles', [
            'farmer_id'      => $farmer->id,
            'website_status' => ProfileWebsiteStatus::Draft->value,
        ]);
    }

    public function test_farmer_can_publish_and_unpublish_website(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        $template = ProfileTemplate::where('code', 'harvest-prestige')->first();

        $profile = FarmerPublicProfile::create([
            'farmer_id'           => $farmer->id,
            'profile_template_id' => $template->id,
            'business_name'       => 'Tani Makmur Jaya',
            'subdomain'           => 'tanimakmurjaya',
            'website_status'      => ProfileWebsiteStatus::Draft,
        ]);

        // Publish
        $response = $this->actingAs($farmer, 'farmer')
            ->post(route('farmer.website.publish'));

        $response->assertRedirect(route('farmer.website.index'));
        $this->assertDatabaseHas('farmer_public_profiles', [
            'id'             => $profile->id,
            'website_status' => ProfileWebsiteStatus::Published->value,
        ]);

        // Unpublish
        $unpublishResponse = $this->actingAs($farmer, 'farmer')
            ->post(route('farmer.website.unpublish'));

        $unpublishResponse->assertRedirect(route('farmer.website.index'));
        $this->assertDatabaseHas('farmer_public_profiles', [
            'id'             => $profile->id,
            'website_status' => ProfileWebsiteStatus::Draft->value,
        ]);
    }

    public function test_public_subdomain_serves_published_farmer_profile(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        $template = ProfileTemplate::where('code', 'harvest-prestige')->first();

        FarmerPublicProfile::create([
            'farmer_id'           => $farmer->id,
            'profile_template_id' => $template->id,
            'business_name'       => 'Pak Joko Organik Farm',
            'headline'            => 'Beras Pandan Wangi Terbaik',
            'subdomain'           => 'pakjoko',
            'website_status'      => ProfileWebsiteStatus::Published,
            'verification_status' => ProfileVerificationStatus::Verified,
        ]);

        $baseDomain = config('domains.base', 'localhost');

        $response = $this->get("http://pakjoko.{$baseDomain}/");

        $response->assertOk()
            ->assertSee('Pak Joko Organik Farm')
            ->assertSee('Beras Pandan Wangi Terbaik')
            ->assertSee('Terverifikasi P.A.D.I.');
    }

    public function test_public_subdomain_returns_404_for_draft_or_nonexistent_profile(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        $template = ProfileTemplate::where('code', 'harvest-prestige')->first();

        FarmerPublicProfile::create([
            'farmer_id'           => $farmer->id,
            'profile_template_id' => $template->id,
            'business_name'       => 'Draft Farm',
            'subdomain'           => 'draftfarm',
            'website_status'      => ProfileWebsiteStatus::Draft,
        ]);

        $baseDomain = config('domains.base', 'localhost');

        // Draft profile should 404
        $draftResponse = $this->get("http://draftfarm.{$baseDomain}/");
        $draftResponse->assertNotFound();

        // Non-existent subdomain should 404
        $nonExistentResponse = $this->get("http://nonexistent.{$baseDomain}/");
        $nonExistentResponse->assertNotFound();
    }


    public function test_admin_can_verify_and_suspend_farmer_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        $template = ProfileTemplate::where('code', 'harvest-prestige')->first();

        $profile = FarmerPublicProfile::create([
            'farmer_id'           => $farmer->id,
            'profile_template_id' => $template->id,
            'business_name'       => 'Petani Contoh',
            'subdomain'           => 'petanicontoh',
            'website_status'      => ProfileWebsiteStatus::Published,
            'verification_status' => ProfileVerificationStatus::Unverified,
        ]);

        // Admin verifies
        $verifyResponse = $this->actingAs($admin)
            ->post(route('admin.farmer-profiles.verify', $profile));

        $verifyResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('farmer_public_profiles', [
            'id'                  => $profile->id,
            'verification_status' => ProfileVerificationStatus::Verified->value,
        ]);

        // Admin suspends
        $suspendResponse = $this->actingAs($admin)
            ->post(route('admin.farmer-profiles.suspend', $profile));

        $suspendResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('farmer_public_profiles', [
            'id'             => $profile->id,
            'website_status' => ProfileWebsiteStatus::Suspended->value,
        ]);
    }
}
