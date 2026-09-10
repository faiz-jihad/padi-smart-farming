<?php

namespace Tests\Feature;

use App\Models\CropSeason;
use App\Models\DiseaseRecommendation;
use App\Models\DiseaseScan;
use App\Models\Farm;
use App\Models\FarmActivity;
use App\Models\GovernmentSubscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GovernmentDataPortalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Portal access / authentication page is accessible
     */
    public function test_portal_access_page_is_accessible(): void
    {
        $response = $this->get('/government/portal');

        $response->assertStatus(200);
        $response->assertSee('Government Data Portal');
        $response->assertSee('6-Digit Government Access Token');
        $response->assertSee('Buka Government Dashboard');
    }

    /**
     * 2. Access fails with invalid token format (not 6 digits)
     */
    public function test_access_fails_with_invalid_token_format(): void
    {
        $response = $this->post('/government/portal/access', [
            'token' => '123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Format token tidak valid', session('error'));
        $this->assertNull(session('b2g_portal_subscription_id'));
    }

    /**
     * 3. Access fails with nonexistent token
     */
    public function test_access_fails_with_nonexistent_token(): void
    {
        $response = $this->post('/government/portal/access', [
            'token' => '999999',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('tidak ditemukan', session('error'));
        $this->assertNull(session('b2g_portal_subscription_id'));
    }

    /**
     * 4. Access fails when subscription is not ACTIVE (e.g. PENDING_PAYMENT)
     */
    public function test_access_fails_when_subscription_is_not_active(): void
    {
        $subscription = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Pertanian Test',
            'agency_email' => 'distan@test.go.id',
            'pic_name'     => 'Budi',
            'pic_phone'    => '085321163909',
            'plan_name'    => 'Government Data Access',
            'plan_days'    => 30,
            'amount'       => 600000,
            'status'       => GovernmentSubscription::STATUS_PENDING_PAYMENT,
            'token_hash'   => hash('sha256', '123456'),
        ]);

        $response = $this->post('/government/portal/access', [
            'token' => '123456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('tidak aktif', session('error'));
        $this->assertNull(session('b2g_portal_subscription_id'));
    }

    /**
     * 5. Access fails when subscription is REVOKED
     */
    public function test_access_fails_when_subscription_is_revoked(): void
    {
        $subscription = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Pertanian Revoked',
            'agency_email' => 'distan@revoked.go.id',
            'pic_name'     => 'Budi',
            'pic_phone'    => '085321163909',
            'plan_name'    => 'Government Data Access',
            'plan_days'    => 30,
            'amount'       => 600000,
            'status'       => GovernmentSubscription::STATUS_REVOKED,
            'token_hash'   => hash('sha256', '654321'),
            'revoked_at'   => Carbon::now(),
        ]);

        $response = $this->post('/government/portal/access', [
            'token' => '654321',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('dicabut (revoked)', session('error'));
        $this->assertNull(session('b2g_portal_subscription_id'));
    }

    /**
     * 6. Access fails when subscription is EXPIRED
     */
    public function test_access_fails_when_subscription_is_expired(): void
    {
        $subscription = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Pertanian Expired',
            'agency_email' => 'distan@expired.go.id',
            'pic_name'     => 'Budi',
            'pic_phone'    => '085321163909',
            'plan_name'    => 'Government Data Access',
            'plan_days'    => 30,
            'amount'       => 600000,
            'status'       => GovernmentSubscription::STATUS_ACTIVE,
            'token_hash'   => hash('sha256', '888888'),
            'expires_at'   => Carbon::now()->subDays(2),
        ]);

        $response = $this->post('/government/portal/access', [
            'token' => '888888',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('telah berakhir', session('error'));
        $this->assertNull(session('b2g_portal_subscription_id'));
    }

    /**
     * 7. Access succeeds with valid 6-digit token and establishes portal session
     */
    public function test_access_succeeds_with_valid_token_and_sets_session(): void
    {
        $subscription = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Tanaman Pangan Kab. Subang',
            'agency_email' => 'distan@subangkab.go.id',
            'pic_name'     => 'Dr. H. Agus Mulyana',
            'pic_phone'    => '085321163909',
            'plan_name'    => 'Government Data Access',
            'plan_days'    => 30,
            'amount'       => 600000,
            'status'       => GovernmentSubscription::STATUS_PENDING_APPROVAL,
        ]);

        // Generate token via standard flow
        $rawToken = $subscription->generateSixDigitToken();

        $response = $this->post('/government/portal/access', [
            'token' => $rawToken,
        ]);

        $response->assertRedirect(route('government.portal.dashboard'));
        $this->assertEquals($subscription->id, session('b2g_portal_subscription_id'));
        $this->assertEquals($subscription->token_hash, session('b2g_portal_token_hash'));
    }

    /**
     * 8. Dashboard requires authentication
     */
    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/government/portal/dashboard');

        $response->assertRedirect(route('government.portal.index'));
        $response->assertSessionHas('error');
    }

    /**
     * 9. Authenticated portal session displays dashboard and real GovernmentDataService data
     */
    public function test_authenticated_user_can_view_dashboard_data(): void
    {
        $subscription = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Pertanian Kab. Karawang',
            'agency_email' => 'distan@karawangkab.go.id',
            'pic_name'     => 'Ir. H. Budi Santoso',
            'pic_phone'    => '085321163909',
            'plan_name'    => 'Government Data Access',
            'plan_days'    => 30,
            'amount'       => 600000,
            'status'       => GovernmentSubscription::STATUS_ACTIVE,
            'token_hash'   => hash('sha256', '599979'),
            'expires_at'   => Carbon::now()->addDays(30),
        ]);

        // Create actual farm, crop season, activity, and disease scan
        $farmer = User::factory()->create(['role' => 'farmer']);
        $farm = Farm::create([
            'farmer_user_id'  => $farmer->id,
            'name'            => 'Lahan Sawah Karawang Subur',
            'area_ha'         => 2.5,
            'latitude'        => -6.3051,
            'longitude'       => 107.3015,
            'irrigation_type' => 'irigasi_teknis',
        ]);
        $season = CropSeason::create([
            'farm_id'       => $farm->id,
            'status'        => 'active',
            'planting_date' => '2026-08-01',
        ]);
        FarmActivity::create([
            'crop_season_id' => $season->id,
            'type'           => 'fertilizing',
            'occurred_at'    => '2026-08-15 10:30:00',
            'notes'          => 'Pemupukan NPK',
        ]);
        $scan = DiseaseScan::create([
            'farmer_id'       => $farmer->id,
            'farm_id'         => $farm->id,
            'image_url'       => 'https://example.com/leaf.jpg',
            'predicted_class' => 'Bacterial Leaf Blight',
            'quality_status'  => 'infected',
            'scanned_at'      => '2026-08-18 14:00:00',
        ]);
        DiseaseRecommendation::create([
            'scan_id'     => $scan->id,
            'source'      => 'ai',
            'llm_model'   => 'gemini-1.5-pro',
            'explanation' => 'Infeksi bakteri Xanthomonas oryzae.',
            'action'      => 'Semprot bakterisida tembaga dan kurangi debit genangan.',
        ]);

        $response = $this->withSession([
            'b2g_portal_subscription_id' => $subscription->id,
            'b2g_portal_token_hash'       => $subscription->token_hash,
        ])->get('/government/portal/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Government Data Portal');
        $response->assertSee('Dinas Pertanian Kab. Karawang');
        $response->assertSee('Ir. H. Budi Santoso');
        $response->assertSee('Cetak Laporan');
        $response->assertSee('Data Lahan Pertanian');
        $response->assertSee('Aktivitas Budidaya Tani');
        $response->assertSee('Laporan Deteksi Penyakit Pertanian');
        $response->assertSee('Insight Pertanian');
        $response->assertSee('Pemupukan NPK');
        $response->assertSee('Bacterial Leaf Blight');
        $response->assertSee('Semprot bakterisida tembaga dan kurangi debit genangan.');

        // Security check: Raw token must NOT be leaked in HTML
        $response->assertDontSee('599979');
    }

    /**
     * 10. Printable report view renders formal document
     */
    public function test_report_view_renders_official_printable_document(): void
    {
        $subscription = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Ketahanan Pangan Indramayu',
            'agency_email' => 'dkp@indramayukab.go.id',
            'pic_name'     => 'H. Ahmad Supriadi',
            'pic_phone'    => '085321163909',
            'plan_name'    => 'Government Data Access',
            'plan_days'    => 30,
            'status'       => GovernmentSubscription::STATUS_ACTIVE,
            'token_hash'   => hash('sha256', '482910'),
            'expires_at'   => Carbon::now()->addDays(20),
        ]);

        $farmer = User::factory()->create(['role' => 'farmer']);
        $farm = Farm::create([
            'farmer_user_id'  => $farmer->id,
            'name'            => 'Lahan Sawah Indramayu',
            'area_ha'         => 3.0,
            'latitude'        => -6.3275,
            'longitude'       => 108.3249,
            'irrigation_type' => 'irigasi_teknis',
        ]);
        $scan = DiseaseScan::create([
            'farmer_id'       => $farmer->id,
            'farm_id'         => $farm->id,
            'image_url'       => 'https://example.com/indramayu_leaf.jpg',
            'predicted_class' => 'Tungro',
            'quality_status'  => 'infected',
            'scanned_at'      => '2026-08-20 09:00:00',
        ]);
        DiseaseRecommendation::create([
            'scan_id'     => $scan->id,
            'source'      => 'ai',
            'llm_model'   => 'gemini-1.5-pro',
            'explanation' => 'Infeksi virus tungro ditularkan wereng hijau.',
            'action'      => 'Kendalikan vektor wereng hijau dan musnahkan tanaman terinfeksi berat.',
        ]);

        $response = $this->withSession([
            'b2g_portal_subscription_id' => $subscription->id,
            'b2g_portal_token_hash'       => $subscription->token_hash,
        ])->get('/government/portal/report');

        $response->assertStatus(200);
        $response->assertSee('LAPORAN DATA PERTANIAN TERPADU');
        $response->assertSee('Dinas Ketahanan Pangan Indramayu');
        $response->assertSee('H. Ahmad Supriadi');
        $response->assertSee('RINGKASAN DATA PERTANIAN');
        $response->assertSee('DATA LAHAN PERTANIAN & PRODUKTIVITAS');
        $response->assertSee('Tungro');
        $response->assertSee('Kendalikan vektor wereng hijau dan musnahkan tanaman terinfeksi berat.');
    }

    /**
     * 11. Logout clears portal session
     */
    public function test_logout_clears_portal_session(): void
    {
        $response = $this->withSession([
            'b2g_portal_subscription_id' => 99,
            'b2g_portal_token_hash'       => 'somehash',
        ])->post('/government/portal/logout');

        $response->assertRedirect(route('government.portal.index'));
        $this->assertNull(session('b2g_portal_subscription_id'));
        $this->assertNull(session('b2g_portal_token_hash'));
    }

    /**
     * 12. Authenticated user accessing /government/portal is redirected to dashboard
     */
    public function test_authenticated_user_accessing_portal_login_is_redirected_to_dashboard(): void
    {
        $subscription = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Pertanian Active',
            'agency_email' => 'distan@active.go.id',
            'pic_name'     => 'Budi',
            'pic_phone'    => '085321163909',
            'status'       => GovernmentSubscription::STATUS_ACTIVE,
            'token_hash'   => hash('sha256', '111111'),
            'expires_at'   => Carbon::now()->addDays(10),
        ]);

        $response = $this->withSession([
            'b2g_portal_subscription_id' => $subscription->id,
            'b2g_portal_token_hash'       => $subscription->token_hash,
        ])->get('/government/portal');

        $response->assertRedirect(route('government.portal.dashboard'));
    }

    /**
     * 13. DiseaseRecommendation accessors for treatment_title and action_summary work properly
     */
    public function test_disease_recommendation_has_backward_compatible_accessors(): void
    {
        $rec = new DiseaseRecommendation([
            'action' => 'Isolasi dan sanitasi petak sawah',
        ]);

        $this->assertEquals('Isolasi dan sanitasi petak sawah', $rec->action);
        $this->assertEquals('Isolasi dan sanitasi petak sawah', $rec->treatment_title);
        $this->assertEquals('Isolasi dan sanitasi petak sawah', $rec->action_summary);
    }
}
