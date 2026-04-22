<?php

namespace Tests\Unit;

use App\Models\Annotation;
use App\Models\Image;
use App\Models\Tache;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ConsensusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsensusServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_consensus_updates_task_scores_and_rewards_only_correct_annotators(): void
    {
        $userA = User::factory()->create(['score_confiance' => 50.00]);
        $userB = User::factory()->create(['score_confiance' => 50.00]);
        $userC = User::factory()->create(['score_confiance' => 50.00]);

        $image = Image::query()->create([
            'url_stockage' => 'uploads/tasks/test.jpg',
            'categorie' => 'test',
            'taille_fichier' => 100,
        ]);

        $task = Tache::query()->create([
            'image_id' => $image->id,
            'type_tache' => 'classification',
            'question' => 'Quel objet est visible ?',
            'options_reponse' => ['voiture', 'moto'],
            'nb_annotations_requises' => 3,
            'statut' => 'en_cours',
        ]);

        $annotationA = Annotation::query()->create([
            'utilisateur_id' => $userA->id,
            'tache_id' => $task->id,
            'reponse_choisie' => 'voiture',
            'temps_execution_ms' => 1000,
        ]);

        $annotationB = Annotation::query()->create([
            'utilisateur_id' => $userB->id,
            'tache_id' => $task->id,
            'reponse_choisie' => 'voiture',
            'temps_execution_ms' => 1200,
        ]);

        Annotation::query()->create([
            'utilisateur_id' => $userC->id,
            'tache_id' => $task->id,
            'reponse_choisie' => 'moto',
            'temps_execution_ms' => 1300,
        ]);

        $service = app(ConsensusService::class);

        $consensusAnswer = $service->checkConsensus($task);
        $this->assertSame('voiture', $consensusAnswer);

        $service->applyConsensus($task, $consensusAnswer);

        $task->refresh();
        $userA->refresh();
        $userB->refresh();
        $userC->refresh();

        $this->assertSame('terminee', $task->statut);
        $this->assertSame('52.00', $userA->score_confiance);
        $this->assertSame('52.00', $userB->score_confiance);
        $this->assertSame('45.00', $userC->score_confiance);

        $this->assertDatabaseHas('transactions', [
            'utilisateur_id' => $userA->id,
            'annotation_id' => $annotationA->id,
            'type' => 'gain',
        ]);

        $this->assertDatabaseHas('transactions', [
            'utilisateur_id' => $userB->id,
            'annotation_id' => $annotationB->id,
            'type' => 'gain',
        ]);

        $this->assertSame(2, Transaction::query()->where('type', 'gain')->count());
    }
}
