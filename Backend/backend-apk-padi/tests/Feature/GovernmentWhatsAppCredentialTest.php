<?php

namespace Tests\Feature;

use App\Models\GovernmentSubscription;
use App\Models\User;
use App\Services\Government\GovernmentNotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GovernmentWhatsAppCredentialTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdminUser(): User
    {
        return User::factory()->create([
            'role'   => 'admin',
            'status' => 'active',
        ]);
    }

    /**
     * Test phone number normalization for all Indonesian number patterns and edge cases
     */
    public function test_phone_number_normalization(): void
    {
        $service = new GovernmentNotificationService();

        // 08 prefix -> 628
        $this->assertEquals('6281234567890', $service->normalizePhoneNumber('081234567890'));

        // 628 prefix -> 628 (no duplication)
        $this->assertEquals('6281234567890', $service->normalizePhoneNumber('6281234567890'));

        // 8 prefix -> 628
        $this->assertEquals('6281234567890', $service->normalizePhoneNumber('81234567890'));

        // International with + and spaces
        $this->assertEquals('6281234567890', $service->normalizePhoneNumber('+62 812-3456-7890'));

        // Formatting with parentheses and dashes
        $this->assertEquals('6281234567890', $service->normalizePhoneNumber('(0812) 3456-7890'));

        // Empty, null, or invalid
        $this->assertNull($service->normalizePhoneNumber(null));
        $this->assertNull($service->normalizePhoneNumber(''));
        $this->assertNull($service->normalizePhoneNumber('    '));
        $this->assertNull($service->normalizePhoneNumber('abc-xyz'));
        $this->assertNull($service->normalizePhoneNumber('123')); // too short
    }

    /**
     * Test credential message contains all required fields and correct format
     */
    public function test_credential_message_structure_and_content(): void
    {
        $service = new GovernmentNotificationService();

        $subscription = new GovernmentSubscription([
            'agency_name' => 'Dinas Pertanian Jawa Barat',
            'pic_name'    => 'Dr. Budi Santoso',
            'pic_phone'   => '081234567890',
            'plan_name'   => 'Government Data Access',
            'plan_days'   => 30,
            'expires_at'  => Carbon::parse('2026-10-31'),
        ]);

        $rawToken = '789123';
        $message = $service->buildCredentialMessage($subscription, $rawToken);

        // Assert greetings & PIC Name
        $this->assertStringContainsString('Halo Dr. Budi Santoso,', $message);

        // Assert approval statement
        $this->assertStringContainsString('Pendaftaran akses data Government B2G P.A.D.I. telah disetujui.', $message);

        // Assert Detail Akses section
        $this->assertStringContainsString('Detail akses:', $message);
        $this->assertStringContainsString('- Instansi: Dinas Pertanian Jawa Barat', $message);
        $this->assertStringContainsString('- Paket: Government Data Access', $message);
        $this->assertStringContainsString('- Durasi: 30 Hari', $message);
        $this->assertStringContainsString('- Status: AKTIF', $message);
        $this->assertStringContainsString('- Token API: 789123', $message);
        $this->assertStringContainsString('Credential:', $message);

        // Assert Portal URL, Base API and Documentation
        $this->assertStringContainsString('Portal Data Pemerintah:', $message);
        $this->assertStringContainsString('/government/portal', $message);
        $this->assertStringContainsString('Base API:', $message);
        $this->assertStringContainsString('Dokumentasi API:', $message);

        // Assert security notice & signature
        $this->assertStringContainsString('Token digunakan untuk autentikasi akses data.', $message);
        $this->assertStringContainsString('Mohon simpan credential ini dengan aman.', $message);
        $this->assertStringContainsString('P.A.D.I. Smart Farming', $message);
    }

    /**
     * Test WhatsApp URL is valid wa.me link with rawurlencode
     */
    public function test_whatsapp_url_generation(): void
    {
        $service = new GovernmentNotificationService();

        $subscription = new GovernmentSubscription([
            'agency_name' => 'Dinas Ketahanan Pangan',
            'pic_name'    => 'Ir. Siti Aminah',
            'pic_phone'   => '+62 856-7890-1234',
            'plan_name'   => 'Government Data Access',
            'plan_days'   => 30,
            'expires_at'  => Carbon::parse('2026-12-01'),
        ]);

        $rawToken = '654321';
        $url = $service->generateWhatsAppUrl($subscription, $rawToken);

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://wa.me/6285678901234?text=', $url);

        // Verify decoded text contains credential
        $queryString = parse_url($url, PHP_URL_QUERY);
        parse_str($queryString, $queryParams);

        $this->assertArrayHasKey('text', $queryParams);
        $this->assertStringContainsString('Ir. Siti Aminah', $queryParams['text']);
        $this->assertStringContainsString('- Token API: 654321', $queryParams['text']);
        $this->assertStringContainsString('/government/portal', $queryParams['text']);
        // Must NOT put token as query param in portal URL
        $this->assertStringNotContainsString('/government/portal?token=', $queryParams['text']);
    }

    /**
     * Test Admin approval triggers token generation and provides WhatsApp URL in session
     */
    public function test_admin_approval_creates_whatsapp_session_url(): void
    {
        $admin = $this->createAdminUser();

        $subscription = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Pertanian Kab. Cianjur',
            'agency_email' => 'distan@cianjurkab.go.id',
            'pic_name'     => 'Drs. Asep Sunandar',
            'pic_phone'    => '081298765432',
            'plan_name'    => 'Government Data Access',
            'plan_days'    => 30,
            'amount'       => 600000,
            'status'       => GovernmentSubscription::STATUS_PENDING_APPROVAL,
        ]);

        $response = $this->actingAs($admin)->post("/admin/government-data/{$subscription->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('generated_token');
        $response->assertSessionHas('generated_agency', 'Dinas Pertanian Kab. Cianjur');
        $response->assertSessionHas('whatsapp_url');

        $rawToken = session('generated_token');
        $whatsappUrl = session('whatsapp_url');

        // Check 6-digit numeric token
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $rawToken);

        // Check WhatsApp URL points to normalized phone (6281298765432)
        $this->assertStringStartsWith('https://wa.me/6281298765432?text=', $whatsappUrl);

        // Check WhatsApp message includes the token
        $this->assertStringContainsString(rawurlencode($rawToken), $whatsappUrl);

        // Database checks: Token hash SHA-256 only
        $subscription->refresh();
        $this->assertEquals(GovernmentSubscription::STATUS_ACTIVE, $subscription->status);
        $this->assertEquals(hash('sha256', $rawToken), $subscription->token_hash);
        $this->assertTrue($subscription->isActive());
    }

    /**
     * Test admin page renders WhatsApp button when session has whatsapp_url
     */
    public function test_admin_view_renders_whatsapp_button(): void
    {
        $admin = $this->createAdminUser();

        $testUrl = 'https://wa.me/6281234567890?text=Halo%20Testing';

        $response = $this->actingAs($admin)
            ->withSession([
                'generated_token'  => '123456',
                'generated_agency' => 'Dinas Pertanian Test',
                'generated_expiry' => '10 Oktober 2026',
                'whatsapp_url'     => $testUrl,
            ])
            ->get('/admin/government-data');

        $response->assertStatus(200);
        $response->assertSee('123456');
        $response->assertSee('Dinas Pertanian Test');
        $response->assertSee('Kirim Credential via WhatsApp');
        $response->assertSee($testUrl, false);
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    /**
     * Edge case: Invalid / empty phone number handled gracefully
     */
    public function test_admin_approval_handles_invalid_phone_gracefully(): void
    {
        $admin = $this->createAdminUser();

        $subscription = GovernmentSubscription::create([
            'agency_name'  => 'Dinas Pertanian Tanpa Nomor',
            'agency_email' => 'distan@test.go.id',
            'pic_name'     => 'Budi',
            'pic_phone'    => 'invalid-phone',
            'plan_name'    => 'Government Data Access',
            'plan_days'    => 30,
            'amount'       => 600000,
            'status'       => GovernmentSubscription::STATUS_PENDING_APPROVAL,
        ]);

        $response = $this->actingAs($admin)->post("/admin/government-data/{$subscription->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('generated_token');
        // whatsapp_url should not be present or is null
        $this->assertNull(session('whatsapp_url'));
    }
}
