<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\TrustScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustScoreServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_after_consensus_increases_score_for_correct_answer(): void
    {
        $user = User::factory()->create([
            'score_confiance' => 50.00,
            'statut' => 'actif',
        ]);

        $service = app(TrustScoreService::class);
        $service->updateAfterConsensus($user, true);

        $user->refresh();

        $this->assertSame('52.00', $user->score_confiance);
        $this->assertSame('actif', $user->statut);
    }

    public function test_update_after_consensus_suspends_user_when_score_drops_below_threshold(): void
    {
        $user = User::factory()->create([
            'score_confiance' => 21.00,
            'statut' => 'actif',
            'motif_statut' => null,
        ]);

        $service = app(TrustScoreService::class);
        $service->updateAfterConsensus($user, false);

        $user->refresh();

        $this->assertSame('16.00', $user->score_confiance);
        $this->assertSame('suspendu', $user->statut);
        $this->assertNotNull($user->motif_statut);
    }
}
