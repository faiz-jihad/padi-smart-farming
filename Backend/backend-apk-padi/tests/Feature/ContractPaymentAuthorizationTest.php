<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\ContractPayment;
use App\Models\Farm;
use App\Models\MarketListing;
use App\Models\PurchaseContract;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractPaymentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function createActor(string $role): User
    {
        $user = User::factory()->create([
            'role' => $role,
            'status' => UserStatus::Active->value,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function createListing(User $farmer): MarketListing
    {
        $farm = Farm::query()->create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah ' . $farmer->name,
            'area_ha' => 2.0,
            'latitude' => -6.30,
            'longitude' => 107.30,
            'irrigation_type' => 'technical',
        ]);

        $season = \App\Models\CropSeason::query()->create([
            'farm_id' => $farm->id,
            'season_name' => 'Musim Tanam 1',
            'variety' => 'Inpari 32',
            'status' => 'active',
            'planting_date' => now()->subMonths(3)->toDateString(),
            'estimated_harvest_date' => now()->addWeek()->toDateString(),
        ]);

        return MarketListing::query()->create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'crop_season_id' => $season->id,
            'commodity' => 'Gabah Kering Panen',
            'quantity' => 2000,
            'unit' => 'kg',
            'price_per_unit' => 7000,
            'total_price' => 14000000,
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    private function createContractWithPayment(User $farmer, User $partner, string $paymentRef, float $amount = 5000000): array
    {
        $listing = $this->createListing($farmer);

        $contract = PurchaseContract::query()->create([
            'listing_id' => $listing->id,
            'farmer_id' => $farmer->id,
            'partner_id' => $partner->id,
            'quantity' => 1000,
            'agreed_price' => 7000,
            'total_amount' => 7000000,
            'status' => 'active',
            'contracted_at' => now(),
        ]);

        $payment = ContractPayment::query()->create([
            'contract_id' => $contract->id,
            'amount' => $amount,
            'payment_method' => 'bank_transfer',
            'status' => 'paid',
            'reference' => $paymentRef,
            'paid_at' => now(),
        ]);

        return [$contract, $payment];
    }

    public function test_unauthenticated_user_cannot_view_contract_payments(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $partner = $this->createActor(UserRole::Buyer->value);
        $this->createContractWithPayment($farmer, $partner, 'PAY-UNAUTH-001');

        $response = $this->getJson('/api/v1/contract-payments');

        $response->assertUnauthorized();
    }

    public function test_farmer_only_sees_payments_for_their_own_contracts(): void
    {
        $farmer1 = $this->createActor(UserRole::Farmer->value);
        $farmer2 = $this->createActor(UserRole::Farmer->value);
        $partner = $this->createActor(UserRole::Buyer->value);

        [, $payment1] = $this->createContractWithPayment($farmer1, $partner, 'PAY-FARMER1-001', 3000000);
        [, $payment2] = $this->createContractWithPayment($farmer2, $partner, 'PAY-FARMER2-001', 4000000);

        $tokenFarmer1 = $farmer1->createToken('Farmer1 Token')->plainTextToken;

        $response = $this->withToken($tokenFarmer1)
            ->getJson('/api/v1/contract-payments');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $payment1->id)
            ->assertJsonPath('data.0.reference', 'PAY-FARMER1-001');
    }

    public function test_farmer_cannot_see_payments_for_another_farmers_contract(): void
    {
        $farmerVictim = $this->createActor(UserRole::Farmer->value);
        $farmerAttacker = $this->createActor(UserRole::Farmer->value);
        $partner = $this->createActor(UserRole::Buyer->value);

        $this->createContractWithPayment($farmerVictim, $partner, 'PAY-VICTIM-001');

        $tokenAttacker = $farmerAttacker->createToken('Attacker Token')->plainTextToken;

        $response = $this->withToken($tokenAttacker)
            ->getJson('/api/v1/contract-payments');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_buyer_partner_only_sees_payments_for_their_own_contracts(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $partner1 = $this->createActor(UserRole::Buyer->value);
        $partner2 = $this->createActor(UserRole::Buyer->value);

        [, $payment1] = $this->createContractWithPayment($farmer, $partner1, 'PAY-PARTNER1-001', 6000000);
        [, $payment2] = $this->createContractWithPayment($farmer, $partner2, 'PAY-PARTNER2-001', 7000000);

        $tokenPartner1 = $partner1->createToken('Partner1 Token')->plainTextToken;

        $response = $this->withToken($tokenPartner1)
            ->getJson('/api/v1/contract-payments');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $payment1->id)
            ->assertJsonPath('data.0.reference', 'PAY-PARTNER1-001');
    }

    public function test_buyer_partner_cannot_see_payments_for_another_partners_contract(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $partnerVictim = $this->createActor(UserRole::Buyer->value);
        $partnerAttacker = $this->createActor(UserRole::Buyer->value);

        $this->createContractWithPayment($farmer, $partnerVictim, 'PAY-SECRET-001');

        $tokenAttacker = $partnerAttacker->createToken('Attacker Token')->plainTextToken;

        $response = $this->withToken($tokenAttacker)
            ->getJson('/api/v1/contract-payments');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_admin_can_view_all_contract_payments(): void
    {
        $farmer1 = $this->createActor(UserRole::Farmer->value);
        $farmer2 = $this->createActor(UserRole::Farmer->value);
        $partner1 = $this->createActor(UserRole::Buyer->value);
        $partner2 = $this->createActor(UserRole::Buyer->value);

        [, $payment1] = $this->createContractWithPayment($farmer1, $partner1, 'PAY-ALL-001', 1000000);
        [, $payment2] = $this->createContractWithPayment($farmer2, $partner2, 'PAY-ALL-002', 2000000);

        $admin = $this->createActor(UserRole::Admin->value);
        $tokenAdmin = $admin->createToken('Admin Token')->plainTextToken;

        $response = $this->withToken($tokenAdmin)
            ->getJson('/api/v1/contract-payments');

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        $references = collect($response->json('data'))->pluck('reference')->all();
        $this->assertContains('PAY-ALL-001', $references);
        $this->assertContains('PAY-ALL-002', $references);
    }
}
