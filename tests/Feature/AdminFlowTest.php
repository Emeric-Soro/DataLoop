<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_tasks_with_images(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/admin/tasks/upload', [
            'images' => [
                UploadedFile::fake()->image('img-1.jpg', 500, 500),
                UploadedFile::fake()->image('img-2.jpg', 500, 500),
            ],
            'type_tache' => 'classification',
            'question' => 'Que vois-tu ?',
            'options' => ['chat', 'chien'],
        ]);

        $response->assertCreated()
            ->assertJsonPath('count', 2);

        $this->assertDatabaseCount('images', 2);
        $this->assertDatabaseCount('taches', 2);
        $this->assertDatabaseHas('taches', [
            'type_tache' => 'classification',
            'question' => 'Que vois-tu ?',
            'statut' => 'nouvelle',
        ]);
    }

    public function test_non_admin_cannot_access_admin_task_upload_route(): void
    {
        $user = User::factory()->create([
            'role' => 'contributeur',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/admin/tasks/upload', [
            'images' => [
                UploadedFile::fake()->image('img-1.jpg', 500, 500),
            ],
            'type_tache' => 'classification',
            'question' => 'Question test',
        ])->assertStatus(403);
    }

    public function test_admin_can_update_system_config(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson('/api/v1/admin/config', [
            'seuil_consensus' => 75,
            'freq_sentinelle' => 10,
        ]);

        $response->assertOk()
            ->assertJsonPath('config.seuil_consensus', 75)
            ->assertJsonPath('config.freq_sentinelle', 10);

        $this->assertSame(75, Cache::get('system_config')['seuil_consensus']);
        $this->assertSame(10, Cache::get('system_config')['freq_sentinelle']);
    }
}
