<?php

namespace Tests\Unit;

use App\Models\Contribution;
use App\Models\ContributionReview;
use App\Models\Dataset;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ContributionConsensusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContributionConsensusServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_and_apply_integrates_contribution_and_rewards_users_when_consensus_is_reached(): void
    {
        $contributor = User::factory()->create();
        $reviewer1 = User::factory()->create();
        $reviewer2 = User::factory()->create();
        $reviewer3 = User::factory()->create();

        $contribution = Contribution::query()->create([
            'utilisateur_id' => $contributor->id,
            'type_contenu' => 'texte',
            'texte_contenu' => 'Texte contribution test',
            'langue' => 'francais',
            'description' => 'Description test',
            'statut' => 'en_revue',
            'nb_reviews_requises' => 3,
        ]);

        foreach ([$reviewer1, $reviewer2, $reviewer3] as $reviewer) {
            ContributionReview::query()->create([
                'contribution_id' => $contribution->id,
                'reviewer_id' => $reviewer->id,
                'note_veracite' => 4,
                'is_valid' => true,
                'commentaire' => 'Valide',
            ]);
        }

        $service = app(ContributionConsensusService::class);
        $service->checkAndApply($contribution);

        $contribution->refresh();

        $this->assertSame('integree', $contribution->statut);
        $this->assertSame('100.00', $contribution->score_consensus);

        $dataset = Dataset::query()->first();
        $this->assertNotNull($dataset);

        $this->assertDatabaseHas('contribution_dataset', [
            'contribution_id' => $contribution->id,
            'dataset_id' => $dataset->id,
        ]);

        $this->assertSame(4, Transaction::query()->where('type', 'gain')->count());
        $this->assertDatabaseHas('transactions', [
            'utilisateur_id' => $contributor->id,
            'type' => 'gain',
        ]);
    }
}
