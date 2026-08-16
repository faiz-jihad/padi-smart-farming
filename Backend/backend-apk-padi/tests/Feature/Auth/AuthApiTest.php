<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('budi@example.com|127.0.0.1');
        $this->seed(RoleSeeder::class);
    }

    public function test_farmer_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', $this->registerPayload([
            'account_type' => UserRole::Farmer->value,
        ]));

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'budi@example.com')
            ->assertJsonPath('data.user.role', UserRole::Farmer->value)
            ->assertJsonStructure(['data' => ['token']])
            ->assertJsonMissing(['password']);

        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'status' => UserStatus::Active->value,
        ]);
    }

    public function test_buyer_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', $this->registerPayload([
            'email' => 'buyer@example.com',
            'phone' => '081234567891',
            'account_type' => UserRole::Buyer->value,
        ]));

        $response
            ->assertCreated()
            ->assertJsonPath('data.user.role', UserRole::Buyer->value);
    }

    public function test_duplicate_email_registration_fails(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $this->postJson('/api/v1/auth/register', $this->registerPayload())
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('email');
    }

    public function test_public_registration_as_admin_is_rejected(): void
    {
        $this->postJson('/api/v1/auth/register', $this->registerPayload([
            'account_type' => UserRole::Admin->value,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('account_type');
    }

    public function test_login_with_valid_credentials_succeeds(): void
    {
        $user = User::factory()->create([
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole(UserRole::Farmer->value);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'BUDI@example.com',
            'password' => 'password',
            'device_name' => 'Feature Test',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'budi@example.com')
            ->assertJsonStructure(['data' => ['token']]);

        $this->assertNotNull($user->refresh()->last_login_at);
    }

    public function test_login_with_wrong_password_fails(): void
    {
        User::factory()->create([
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'budi@example.com',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_inactive_account_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
            'status' => UserStatus::Inactive->value,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'budi@example.com',
            'password' => 'password',
        ])
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_me_returns_active_user_with_token(): void
    {
        [$user, $token] = $this->userWithToken();

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonMissing(['password']);
    }

    public function test_me_without_token_returns_unauthorized(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);
    }

    public function test_logout_revokes_current_token_only(): void
    {
        [$user, $token] = $this->userWithToken();
        $user->createToken('second-device');

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_logout_all_revokes_all_tokens(): void
    {
        [$user, $token] = $this->userWithToken();
        $user->createToken('second-device');

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout-all')
            ->assertOk();

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_update_profile_succeeds(): void
    {
        [, $token] = $this->userWithToken();

        $this->withToken($token)
            ->patchJson('/api/v1/profile', [
                'name' => 'Budi Baru',
                'phone' => '0812 2222 3333',
            ])
            ->assertOk()
            ->assertJsonPath('data.user.name', 'Budi Baru')
            ->assertJsonPath('data.user.phone', '081222223333');
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        [, $token] = $this->userWithToken();

        $this->withToken($token)
            ->putJson('/api/v1/profile/password', [
                'current_password' => 'salah',
                'password' => 'PasswordBaru123',
                'password_confirmation' => 'PasswordBaru123',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');
    }

    public function test_password_change_succeeds_and_revokes_other_tokens(): void
    {
        [$user, $token] = $this->userWithToken();
        $user->createToken('second-device');

        $this->withToken($token)
            ->putJson('/api/v1/profile/password', [
                'current_password' => 'password',
                'password' => 'PasswordBaru123',
                'password_confirmation' => 'PasswordBaru123',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('PasswordBaru123', $user->refresh()->password));
        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_login_rate_limit_works(): void
    {
        User::factory()->create([
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'budi@example.com',
                'password' => 'wrong-password',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'budi@example.com',
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    }

    public function test_auth_responses_do_not_expose_sensitive_fields(): void
    {
        $this->postJson('/api/v1/auth/register', $this->registerPayload())
            ->assertCreated()
            ->assertJsonMissingPath('data.user.password')
            ->assertJsonMissingPath('data.user.remember_token')
            ->assertJsonMissingPath('data.user.permissions')
            ->assertJsonMissingPath('data.user.tokens');
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function registerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'password' => 'PasswordKuat123',
            'password_confirmation' => 'PasswordKuat123',
            'account_type' => UserRole::Farmer->value,
            'device_name' => 'Feature Test',
        ], $overrides);
    }

    /**
     * @return array{User, string}
     */
    private function userWithToken(): array
    {
        $user = User::factory()->create([
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole(UserRole::Farmer->value);

        return [$user, $user->createToken('Feature Test')->plainTextToken];
    }
}
