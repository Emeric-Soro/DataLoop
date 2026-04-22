<?php

namespace App\Services;

use App\Models\Contribution;
use App\Models\Dataset;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ContributionConsensusService
{
    public function __construct(
        private RewardService $rewardService,
    ) {
    }

    public function checkAndApply(Contribution $contribution): void
    {
        DB::transaction(function () use ($contribution): void {
            $contribution = Contribution::query()
                ->with(['utilisateur', 'reviews.reviewer'])
                ->lockForUpdate()
                ->findOrFail($contribution->id);

            if (in_array($contribution->statut, ['validee', 'rejetee', 'integree'], true)) {
                return;
            }

            $reviews = $contribution->reviews;

            if ($reviews->count() < $contribution->nb_reviews_requises) {
                return;
            }

            $positives = $reviews->where('is_valid', true)->count();
            $total = $reviews->count();
            $ratio = $positives / max(1, $total);

            $contribution->update([
                'nb_reviews_positives' => $positives,
                'nb_reviews_negatives' => $total - $positives,
                'score_consensus' => round($ratio * 100, 2),
            ]);

            $config = Cache::get('system_config', []);
            $seuil = ($config['seuil_consensus'] ?? 66) / 100;

            if ($ratio < $seuil) {
                $contribution->update(['statut' => 'rejetee']);

                return;
            }

            $contribution->update(['statut' => 'validee']);

            $this->rewardService->rewardForContribution(
                $contribution->utilisateur,
                100.00,
                'Contribution #' . $contribution->id . ' validee par la communaute'
            );

            foreach ($reviews as $review) {
                $this->rewardService->rewardForReview(
                    $review->reviewer,
                    25.00,
                    'Review contribution #' . $contribution->id
                );
            }

            $this->integrateToDataset($contribution);
        });
    }

    private function integrateToDataset(Contribution $contribution): void
    {
        $dataset = Dataset::query()->latest('id')->first();

        if (!$dataset) {
            $dataset = Dataset::create([
                'nom' => 'Contributions communautaires',
                'description' => 'Dataset auto-cree pour les contributions validees.',
                'version' => 'v1.0',
                'format_export' => 'json',
            ]);
        }

        $dataset->contributions()->syncWithoutDetaching([$contribution->id]);

        $contribution->update(['statut' => 'integree']);
    }
}
