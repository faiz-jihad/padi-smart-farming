<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\GovernmentPayment;
use App\Models\GovernmentSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GovernmentDataCenterAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    protected function createAdmin(): User
    {
        $admin = User::factory()->create([
            'name'   => 'Admin Test',
            'email'  => 'admin.test@padi.test',
            'role'   => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ]);
        $admin->assignRole(UserRole::Admin->value);

        return $admin;
    }

    public function test_guest_is_redirected_from_government_data_center(): void
    {
        $this->get(route('admin.government-data.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_government_data_center_index(): void
    {
        $admin = $this->createAdmin();

        $sub = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Pertanian Kab. Indramayu',
            'agency_email' => 'dinas@indramayukab.go.id',
            'pic_name'     => 'Ir. H. Budi',
            'pic_phone'    => '081234567890',
            'plan_name'    => 'B2G Access 30 Hari',
            'plan_days'    => 30,
            'amount'       => 20000,
            'status'       => 'PENDING_APPROVAL',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.government-data.index'));

        $response->assertOk()
            ->assertSee('Government Data Center (B2G)')
            ->assertSee('css/admin/government-data.css', false)
            ->assertSee('Dinas Pertanian Kab. Indramayu')
            ->assertSee('Daftar Langganan &amp; Token', false)
            ->assertSee('Riwayat Aktivitas Tani')
            ->assertSee('Pemantauan Penyakit')
            ->assertSee('Produktivitas Pertanian')
            ->assertSee('Agregasi &amp; Insight Dinas', false);
    }

    public function test_admin_can_view_subscription_detail(): void
    {
        $admin = $this->createAdmin();

        $sub = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Tanaman Pangan Prov. Jabar',
            'agency_email' => 'distanhut@jabarprov.go.id',
            'pic_name'     => 'Dr. Agus',
            'pic_phone'    => '081987654321',
            'plan_name'    => 'B2G Access 30 Hari',
            'plan_days'    => 30,
            'amount'       => 20000,
            'status'       => 'PENDING_PAYMENT',
        ]);

        $payment = GovernmentPayment::create([
            'government_subscription_id' => $sub->id,
            'order_id'                   => 'B2G-ORDER-999',
            'amount'                     => 20000,
            'transaction_status'         => 'pending',
            'payment_method'             => 'qris',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.government-data.show', $sub->id));

        $response->assertOk()
            ->assertSee('Detail Registrasi B2G #' . $sub->id)
            ->assertSee('Dinas Tanaman Pangan Prov. Jabar')
            ->assertSee('css/admin/government-data.css', false)
            ->assertSee('B2G-ORDER-999')
            ->assertSee('Rp 20.000');
    }

    public function test_admin_can_approve_subscription_and_generate_token(): void
    {
        $admin = $this->createAdmin();

        $sub = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Pertanian Kab. Karawang',
            'agency_email' => 'distan@karawangkab.go.id',
            'pic_name'     => 'H. Mulyadi',
            'pic_phone'    => '081234567899',
            'plan_name'    => 'B2G Access 30 Hari',
            'plan_days'    => 30,
            'amount'       => 20000,
            'status'       => 'PENDING_APPROVAL',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.government-data.approve', $sub->id));

        $response->assertRedirect();
        $response->assertSessionHas('generated_token');
        $response->assertSessionHas('status');

        $sub->refresh();
        $this->assertEquals('ACTIVE', $sub->status);
        $this->assertNotNull($sub->token_hash);
        $this->assertNotNull($sub->token_preview);
        $this->assertEquals(6, strlen($sub->token_preview));
    }

    public function test_admin_can_revoke_active_subscription(): void
    {
        $admin = $this->createAdmin();

        $sub = GovernmentSubscription::create([
            'agency_name'   => 'Dinas Pertanian Kab. Subang',
            'agency_email'  => 'distan@subangkab.go.id',
            'pic_name'      => 'Asep',
            'pic_phone'     => '081234567888',
            'plan_name'     => 'B2G Access 30 Hari',
            'plan_days'     => 30,
            'amount'        => 20000,
            'token_hash'    => hash('sha256', '123456'),
            'token_preview' => '12****',
            'status'        => 'ACTIVE',
            'started_at'    => now(),
            'expires_at'    => now()->addDays(30),
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.government-data.revoke', $sub->id));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $sub->refresh();
        $this->assertEquals('REVOKED', $sub->status);
    }

    public function test_admin_can_confirm_manual_payment_to_pending_approval(): void
    {
        $admin = $this->createAdmin();

        $sub = GovernmentSubscription::create([
            'agency_name'   => 'Dinas Pertanian Kab. Majalengka',
            'agency_email'  => 'distan@majalengkakab.go.id',
            'pic_name'      => 'Dedi',
            'pic_phone'     => '081234567877',
            'plan_name'     => 'B2G Access 30 Hari',
            'plan_days'     => 30,
            'amount'        => 20000,
            'status'        => 'PENDING_PAYMENT',
        ]);

        $payment = GovernmentPayment::create([
            'government_subscription_id' => $sub->id,
            'order_id'                   => 'B2G-WA-TEST-01',
            'amount'                     => 20000,
            'transaction_status'         => 'pending',
            'payment_method'             => 'whatsapp_manual',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.government-data.confirm-payment', $sub->id));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $sub->refresh();
        $payment->refresh();

        $this->assertEquals('PENDING_APPROVAL', $sub->status);
        $this->assertEquals('settlement', $payment->transaction_status);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_public_checkout_page_renders_whatsapp_billing_button_and_details(): void
    {
        $sub = GovernmentSubscription::create([
            'agency_name'   => 'Dinas Pertanian Kab. Cirebon',
            'agency_email'  => 'distan@cirebonkab.go.id',
            'pic_name'      => 'H. Sunarto',
            'pic_phone'     => '081234567777',
            'plan_name'     => 'B2G Access 30 Hari',
            'plan_days'     => 30,
            'amount'        => 20000,
            'status'        => 'PENDING_PAYMENT',
        ]);

        $response = $this->get(route('government.checkout', $sub->id));

        $response->assertOk()
            ->assertSee('Billing &amp; Konfirmasi Pembayaran B2G', false)
            ->assertSee('Dinas Pertanian Kab. Cirebon')
            ->assertSee('Rp 20.000')
            ->assertSee('Hubungi Admin via WhatsApp')
            ->assertSee('https://wa.me/', false)
            ->assertSee(config('b2g.whatsapp_number'));
    }
}
