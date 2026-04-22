<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_withdraw_when_balance_is_sufficient(): void
    {
        $user = User::factory()->create([
            'solde_virtuel' => 1000,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/wallet/withdraw', [
            'montant' => 200,
            'methode_paiement' => 'mobile_money',
        ]);

        $response->assertCreated()
            ->assertJsonPath('transaction.type', 'retrait');

        $this->assertDatabaseHas('transactions', [
            'utilisateur_id' => $user->id,
            'type' => 'retrait',
            'montant' => 200,
        ]);
    }

    public function test_user_cannot_withdraw_when_balance_is_insufficient(): void
    {
        $user = User::factory()->create([
            'solde_virtuel' => 50,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/wallet/withdraw', [
            'montant' => 100,
            'methode_paiement' => 'mobile_money',
        ])->assertStatus(422);
    }
}
