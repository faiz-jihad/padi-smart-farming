<?php

namespace Tests\Feature;

use App\Models\CropSeason;
use App\Models\DiseaseScan;
use App\Models\Farm;
use App\Models\FarmActivity;
use App\Models\GovernmentPayment;
use App\Models\GovernmentSubscription;
use App\Models\Harvest;
use App\Models\RiceVariety;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GovernmentB2GApiTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminUser(): User
    {
        return User::factory()->create([
            'role'   => 'admin',
            'status' => 'active',
        ]);
    }

    protected function createFarmerUser(): User
    {
        return User::factory()->create([
            'role'   => 'farmer',
            'status' => 'active',
        ]);
    }

    /**
     * 1. Public B2G Landing Page is Accessible
     */
    public function test_public_government_landing_page_is_accessible(): void
    {
        $response = $this->get('/government');

        $response->assertStatus(200);
        $response->assertSee('Akses Data Pertanian Terpadu');
        $response->assertSee('Government Data Access');
        $response->assertSee('Daftarkan Instansi Anda');
    }

    /**
     * 2. Public API Docs Page is Accessible
     */
    public function test_public_api_docs_page_is_accessible(): void
    {
        $response = $this->get('/government/docs');

        $response->assertStatus(200);
        $response->assertSee('Dokumentasi API B2G P.A.D.I.');
        $response->assertSee('/overview');
        $response->assertSee('/activities');
        $response->assertSee('/diseases');
        $response->assertSee('/productivity');
        $response->assertSee('/insights');
    }

    /**
     * 3. Public Subscription Form Validation
     */
    public function test_public_subscription_form_validation(): void
    {
        $response = $this->post('/government/subscribe', []);

        $response->assertSessionHasErrors(['agency_name', 'agency_email', 'pic_name', 'pic_phone']);
    }

    /**
     * 4. Subscription Registration & Midtrans Payment Initiation
     */
    public function test_subscription_registration_creates_pending_subscription_and_payment(): void
    {
        $response = $this->post('/government/subscribe', [
            'agency_name'  => 'Dinas Pertanian Kab. Indramayu',
            'agency_email' => 'distan@indramayukab.go.id',
            'pic_name'     => 'Ir. H. Budi Santoso',
            'pic_phone'    => '081234567890',
            'purpose'      => 'Monitoring ketahanan pangan dan sebaran penyakit tanaman padi.',
        ]);

        $this->assertDatabaseHas('government_subscriptions', [
            'agency_name'  => 'Dinas Pertanian Kab. Indramayu',
            'agency_email' => 'distan@indramayukab.go.id',
            'status'       => 'PENDING_PAYMENT',
            'plan_days'    => 30,
        ]);

        $subscription = GovernmentSubscription::first();
        $this->assertNotNull($subscription);

        $this->assertDatabaseHas('government_payments', [
            'government_subscription_id' => $subscription->id,
            'transaction_status'         => 'pending',
        ]);

        $response->assertRedirect(route('government.checkout', ['subscription' => $subscription->id]));
    }

    /**
     * 5. Midtrans Webhook Notification Updates Status to PENDING_APPROVAL
     */
    public function test_midtrans_webhook_updates_payment_and_subscription_status(): void
    {
        $subscription = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Pertanian Kab. Subang',
            'agency_email' => 'distan@subang.go.id',
            'pic_name'     => 'Ahmad Sobari',
            'pic_phone'    => '081234567891',
            'plan_name'    => 'Government Data Access',
            'plan_days'    => 30,
            'amount'       => 2500000,
            'status'       => GovernmentSubscription::STATUS_PENDING_PAYMENT,
        ]);

        $payment = GovernmentPayment::create([
            'government_subscription_id' => $subscription->id,
            'order_id'                   => 'PADI-B2G-' . $subscription->id . '-TEST01',
            'amount'                     => 2500000,
            'transaction_status'         => 'pending',
        ]);

        $webhookPayload = [
            'order_id'           => $payment->order_id,
            'transaction_status' => 'settlement',
            'transaction_id'     => 'midtrans-trx-999',
            'payment_type'       => 'bank_transfer',
            'gross_amount'       => '2500000.00',
        ];

        $response = $this->postJson('/api/v1/payment/midtrans/notification', $webhookPayload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        $payment->refresh();
        $subscription->refresh();

        $this->assertEquals('settlement', $payment->transaction_status);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals(GovernmentSubscription::STATUS_PENDING_APPROVAL, $subscription->status);
    }

    /**
     * 6. Admin can View Government Data Center & Subscriptions
     */
    public function test_admin_can_view_government_data_center(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/government-data');

        $response->assertStatus(200);
        $response->assertSee('Government Data Center');
        $response->assertSee('Daftar Langganan');
    }

    /**
     * 7. Admin Approval Generates Exact 6-Digit Token & Activates Subscription
     */
    public function test_admin_approval_generates_exact_6_digit_token(): void
    {
        $admin = $this->createAdminUser();

        $subscription = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Pertanian Kab. Karawang',
            'agency_email' => 'distan@karawangkab.go.id',
            'pic_name'     => 'Drs. Hendra',
            'pic_phone'    => '081234567892',
            'plan_name'    => 'Government Data Access',
            'plan_days'    => 30,
            'amount'       => 2500000,
            'status'       => GovernmentSubscription::STATUS_PENDING_APPROVAL,
        ]);

        $response = $this->actingAs($admin)->post("/admin/government-data/{$subscription->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('generated_token');

        $rawToken = session('generated_token');
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $rawToken);

        $subscription->refresh();
        $this->assertEquals(GovernmentSubscription::STATUS_ACTIVE, $subscription->status);
        $this->assertEquals(hash('sha256', $rawToken), $subscription->token_hash);
        $this->assertTrue($subscription->isActive());
        $this->assertNotNull($subscription->started_at);
        $this->assertNotNull($subscription->expires_at);
        $this->assertTrue($subscription->expires_at->isFuture());
    }

    /**
     * 8. Active Subscription with Valid 6-Digit Token can Access All B2G API Endpoints
     */
    public function test_valid_6_digit_token_can_access_all_b2g_api_endpoints(): void
    {
        $subscription = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Ketahanan Pangan Provinsi Jabar',
            'agency_email' => 'dkp@jabarprov.go.id',
            'pic_name'     => 'Dr. Ir. Rahmat',
            'pic_phone'    => '081234567893',
            'plan_name'    => 'Government Data Access',
            'plan_days'    => 30,
            'amount'       => 2500000,
            'status'       => GovernmentSubscription::STATUS_ACTIVE,
        ]);

        $rawToken = $subscription->generateSixDigitToken();

        // 8a. GET /api/v1/government/overview
        $resOverview = $this->withHeader('Authorization', 'Bearer ' . $rawToken)
            ->getJson('/api/v1/government/overview');
        $resOverview->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_farmers',
                    'total_farms',
                    'total_activities',
                    'total_disease_detections',
                    'productive_farms',
                    'need_attention_farms',
                    'insufficient_data_farms',
                ],
            ]);

        // 8b. GET /api/v1/government/activities
        $resActivities = $this->withHeader('Authorization', 'Bearer ' . $rawToken)
            ->getJson('/api/v1/government/activities');
        $resActivities->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        // 8c. GET /api/v1/government/diseases
        $resDiseases = $this->withHeader('Authorization', 'Bearer ' . $rawToken)
            ->getJson('/api/v1/government/diseases');
        $resDiseases->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'pagination',
            ]);

        // 8d. GET /api/v1/government/productivity
        $resProd = $this->withHeader('Authorization', 'Bearer ' . $rawToken)
            ->getJson('/api/v1/government/productivity');
        $resProd->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'pagination',
            ]);

        // 8e. GET /api/v1/government/insights
        $resInsights = $this->withHeader('Authorization', 'Bearer ' . $rawToken)
            ->getJson('/api/v1/government/insights');
        $resInsights->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_active_farmers',
                    'total_active_farms',
                    'total_activities',
                    'top_activities',
                    'total_disease_detections',
                    'top_diseases',
                    'affected_commodities',
                    'productive_rate_pct',
                ],
            ]);
    }

    /**
     * 9. Invalid Token is Rejected with 401 Unauthorized
     */
    public function test_invalid_token_is_rejected(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer 999999')
            ->getJson('/api/v1/government/overview');

        $response->assertStatus(401)
            ->assertJson([
                'success'    => false,
                'error_code' => 'INVALID_TOKEN',
            ]);
    }

    /**
     * 10. Expired Token is Rejected with 403 Forbidden
     */
    public function test_expired_token_is_rejected(): void
    {
        $subscription = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Pertanian Expired',
            'agency_email' => 'expired@distan.go.id',
            'pic_name'     => 'Test Expired',
            'pic_phone'    => '081234567894',
            'plan_name'    => 'Government Data Access',
            'plan_days'    => 30,
            'amount'       => 2500000,
            'status'       => GovernmentSubscription::STATUS_ACTIVE,
        ]);

        $rawToken = $subscription->generateSixDigitToken();

        // Simulate expired
        $subscription->update([
            'expires_at' => Carbon::now()->subDay(),
            'status'     => GovernmentSubscription::STATUS_EXPIRED,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $rawToken)
            ->getJson('/api/v1/government/overview');

        $response->assertStatus(403)
            ->assertJson([
                'success'    => false,
                'error_code' => 'TOKEN_EXPIRED',
            ]);
    }

    /**
     * 11. Revoked Token is Rejected with 403 Forbidden
     */
    public function test_revoked_token_is_rejected(): void
    {
        $subscription = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Pertanian Revoked',
            'agency_email' => 'revoked@distan.go.id',
            'pic_name'     => 'Test Revoked',
            'pic_phone'    => '081234567895',
            'plan_name'    => 'Government Data Access',
            'plan_days'    => 30,
            'amount'       => 2500000,
            'status'       => GovernmentSubscription::STATUS_ACTIVE,
        ]);

        $rawToken = $subscription->generateSixDigitToken();

        // Revoke
        $subscription->revoke();

        $response = $this->withHeader('Authorization', 'Bearer ' . $rawToken)
            ->getJson('/api/v1/government/overview');

        $response->assertStatus(403)
            ->assertJson([
                'success'    => false,
                'error_code' => 'TOKEN_REVOKED',
            ]);
    }

    /**
     * 12. B2G Activities Filtering in Database
     */
    public function test_b2g_activities_filter_works(): void
    {
        $subscription = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Pertanian Filter Test',
            'agency_email' => 'filter@distan.go.id',
            'pic_name'     => 'Test Filter',
            'pic_phone'    => '081234567896',
            'plan_name'    => 'Government Data Access',
            'plan_days'    => 30,
            'amount'       => 2500000,
            'status'       => GovernmentSubscription::STATUS_ACTIVE,
        ]);

        $rawToken = $subscription->generateSixDigitToken();

        $farmer = $this->createFarmerUser();
        $variety = RiceVariety::create(['name' => 'Inpari 32']);
        $farm = Farm::create([
            'farmer_user_id'  => $farmer->id,
            'name'            => 'Sawah Blok Percobaan',
            'area_ha'         => 2.0,
            'latitude'        => -6.3275,
            'longitude'       => 108.3241,
            'irrigation_type' => 'irigasi_teknis',
        ]);
        $season = CropSeason::create([
            'farm_id'      => $farm->id,
            'variety_id'   => $variety->id,
            'status'       => 'active',
            'planting_date' => '2026-08-01',
        ]);

        FarmActivity::create([
            'crop_season_id' => $season->id,
            'type'           => 'fertilizing',
            'occurred_at'    => '2026-08-10 08:00:00',
            'notes'          => 'Urea 50kg',
        ]);

        FarmActivity::create([
            'crop_season_id' => $season->id,
            'type'           => 'spraying',
            'occurred_at'    => '2026-08-20 09:00:00',
            'notes'          => 'Pestisida nabati',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $rawToken)
            ->getJson('/api/v1/government/activities?activity_type=fertilizing');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Fertilizing', $data[0]['activity_type']);
    }

    /**
     * 13. Farmer Can View Ringkasan Pertanian Overview
     */
    public function test_farmer_can_view_ringkasan_pertanian_overview(): void
    {
        $farmer = $this->createFarmerUser();

        $response = $this->actingAs($farmer, 'farmer')->get('/farmer/ringkasan-pertanian');

        $response->assertStatus(200);
        $response->assertSee('Ringkasan Pertanian & Produktivitas');
    }
}
