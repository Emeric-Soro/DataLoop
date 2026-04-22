<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createTask(): Tache
    {
        $image = Image::query()->create([
            'url_stockage' => 'uploads/tasks/demo.jpg',
            'categorie' => 'test',
            'taille_fichier' => 100,
        ]);

        return Tache::query()->create([
            'image_id' => $image->id,
            'type_tache' => 'classification',
            'question' => 'Que vois-tu ?',
            'options_reponse' => ['chat', 'chien'],
            'nb_annotations_requises' => 3,
            'statut' => 'nouvelle',
        ]);
    }

    public function test_authenticated_user_can_fetch_next_task(): void
    {
        $user = User::factory()->create();
        $task = $this->createTask();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/tasks/next');

        $response->assertOk()
            ->assertJsonPath('task.id', $task->id);
    }

    public function test_user_cannot_annotate_same_task_twice(): void
    {
        $user = User::factory()->create();
        $task = $this->createTask();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/tasks/' . $task->id . '/annotate', [
            'reponse_choisie' => 'chat',
            'temps_execution_ms' => 800,
        ])->assertCreated();

        $this->postJson('/api/v1/tasks/' . $task->id . '/annotate', [
            'reponse_choisie' => 'chien',
            'temps_execution_ms' => 900,
        ])->assertStatus(409);
    }
}
