<?php

namespace Tests\Feature;

use App\Models\Contribution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContributionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_text_contribution(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/contributions', [
            'type_contenu' => 'texte',
            'texte_contenu' => 'Phrase test en nouchi.',
            'langue' => 'nouchi',
            'description' => 'Contribution texte test.',
            'categorie' => 'langue',
        ]);

        $response->assertCreated()
            ->assertJsonPath('contribution.type_contenu', 'texte')
            ->assertJsonPath('contribution.statut', 'en_revue');

        $this->assertDatabaseHas('contributions', [
            'utilisateur_id' => $user->id,
            'type_contenu' => 'texte',
            'langue' => 'nouchi',
            'statut' => 'en_revue',
        ]);
    }

    public function test_contribution_is_integrated_after_three_positive_reviews(): void
    {
        $contributor = User::factory()->create();
        $reviewer1 = User::factory()->create();
        $reviewer2 = User::factory()->create();
        $reviewer3 = User::factory()->create();

        Sanctum::actingAs($contributor);
        $submitResponse = $this->postJson('/api/v1/contributions', [
            'type_contenu' => 'texte',
            'texte_contenu' => 'Texte original de contribution.',
            'langue' => 'francais',
            'description' => 'Description de test',
            'nb_reviews_requises' => 3,
        ]);

        $submitResponse->assertCreated();
        $contributionId = (int) $submitResponse->json('contribution.id');

        foreach ([$reviewer1, $reviewer2, $reviewer3] as $reviewer) {
            Sanctum::actingAs($reviewer);

            $this->postJson('/api/v1/contributions/' . $contributionId . '/review', [
                'note_veracite' => 4,
                'is_valid' => true,
                'commentaire' => 'Valide',
            ])->assertCreated();
        }

        $contribution = Contribution::query()->findOrFail($contributionId);

        $this->assertSame('integree', $contribution->statut);
        $this->assertDatabaseHas('contribution_reviews', [
            'contribution_id' => $contributionId,
            'reviewer_id' => $reviewer1->id,
        ]);
        $this->assertDatabaseHas('contribution_reviews', [
            'contribution_id' => $contributionId,
            'reviewer_id' => $reviewer2->id,
        ]);
        $this->assertDatabaseHas('contribution_reviews', [
            'contribution_id' => $contributionId,
            'reviewer_id' => $reviewer3->id,
        ]);

        $this->assertDatabaseHas('transactions', [
            'utilisateur_id' => $contributor->id,
            'type' => 'gain',
        ]);
        $this->assertDatabaseHas('transactions', [
            'utilisateur_id' => $reviewer1->id,
            'type' => 'gain',
        ]);
        $this->assertDatabaseHas('transactions', [
            'utilisateur_id' => $reviewer2->id,
            'type' => 'gain',
        ]);
        $this->assertDatabaseHas('transactions', [
            'utilisateur_id' => $reviewer3->id,
            'type' => 'gain',
        ]);
    }

    public function test_contribution_is_rejected_when_consensus_is_negative(): void
    {
        $contributor = User::factory()->create();
        $reviewer1 = User::factory()->create();
        $reviewer2 = User::factory()->create();
        $reviewer3 = User::factory()->create();

        Sanctum::actingAs($contributor);
        $submitResponse = $this->postJson('/api/v1/contributions', [
            'type_contenu' => 'texte',
            'texte_contenu' => 'Contribution contestable',
            'langue' => 'francais',
            'description' => 'Test rejet',
            'nb_reviews_requises' => 3,
        ]);

        $submitResponse->assertCreated();
        $contributionId = (int) $submitResponse->json('contribution.id');

        Sanctum::actingAs($reviewer1);
        $this->postJson('/api/v1/contributions/' . $contributionId . '/review', [
            'note_veracite' => 2,
            'is_valid' => false,
        ])->assertCreated();

        Sanctum::actingAs($reviewer2);
        $this->postJson('/api/v1/contributions/' . $contributionId . '/review', [
            'note_veracite' => 1,
            'is_valid' => false,
        ])->assertCreated();

        Sanctum::actingAs($reviewer3);
        $this->postJson('/api/v1/contributions/' . $contributionId . '/review', [
            'note_veracite' => 4,
            'is_valid' => true,
        ])->assertCreated();

        $contribution = Contribution::query()->findOrFail($contributionId);

        $this->assertSame('rejetee', $contribution->statut);
        $this->assertDatabaseCount('contribution_dataset', 0);
        $this->assertDatabaseMissing('transactions', [
            'utilisateur_id' => $contributor->id,
            'type' => 'gain',
        ]);
    }

    public function test_user_cannot_review_own_contribution_and_cannot_review_twice(): void
    {
        $contributor = User::factory()->create();
        $reviewer = User::factory()->create();

        Sanctum::actingAs($contributor);
        $submitResponse = $this->postJson('/api/v1/contributions', [
            'type_contenu' => 'texte',
            'texte_contenu' => 'Mon propre contenu',
            'langue' => 'francais',
            'description' => 'Test regles review',
        ]);

        $submitResponse->assertCreated();
        $contributionId = (int) $submitResponse->json('contribution.id');

        $this->postJson('/api/v1/contributions/' . $contributionId . '/review', [
            'note_veracite' => 5,
            'is_valid' => true,
        ])->assertStatus(403);

        Sanctum::actingAs($reviewer);
        $this->postJson('/api/v1/contributions/' . $contributionId . '/review', [
            'note_veracite' => 5,
            'is_valid' => true,
        ])->assertCreated();

        $this->postJson('/api/v1/contributions/' . $contributionId . '/review', [
            'note_veracite' => 4,
            'is_valid' => true,
        ])->assertStatus(409);
    }
}
