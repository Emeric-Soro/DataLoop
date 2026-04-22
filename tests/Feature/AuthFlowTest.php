<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_ignores_admin_role_and_creates_contributeur(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'nom' => 'Alice',
            'telephone' => '0700000001',
            'email' => 'alice@example.test',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.role', 'contributeur');

        $this->assertDatabaseHas('users', [
            'telephone' => '0700000001',
            'role' => 'contributeur',
        ]);
    }

    public function test_login_returns_token_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'telephone' => '0700000002',
            'password' => Hash::make('secret1234'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'telephone' => $user->telephone,
            'password' => 'secret1234',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure(['access_token']);
    }

    public function test_verify_otp_returns_token_for_existing_user(): void
    {
        $user = User::factory()->create([
            'telephone' => '0700000099',
        ]);

        Cache::put('otp:' . $user->telephone, '123456', now()->addMinutes(5));

        $response = $this->postJson('/api/v1/auth/otp/verify', [
            'telephone' => $user->telephone,
            'code' => '123456',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure(['access_token']);

        $this->assertNull(Cache::get('otp:' . $user->telephone));
    }
}
