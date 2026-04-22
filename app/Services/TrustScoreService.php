<?php

namespace App\Services;

use App\Models\User;

class TrustScoreService
{
    /**
     * Met à jour le score de confiance après un consensus sur une tâche classique.
     *
     * +2.0 si l'utilisateur était en accord avec le consensus
     * -5.0 si l'utilisateur était en désaccord
     * Score borné entre 0 et 100.
     */
    public function updateAfterConsensus(User $user, bool $wasCorrect): void
    {
        $delta = $wasCorrect ? 2.0 : -5.0;
        $this->applyDelta($user, $delta);
    }

    /**
     * Met à jour le score de confiance après une tâche sentinelle.
     * Impact plus fort que le consensus classique.
     *
     * +3.0 si correct
     * -8.0 si incorrect
     */
    public function updateAfterSentinelle(User $user, bool $wasCorrect): void
    {
        $delta = $wasCorrect ? 3.0 : -8.0;
        $this->applyDelta($user, $delta);
    }

    /**
     * Applique un delta au score de confiance de l'utilisateur.
     * Suspend automatiquement l'utilisateur si le score tombe sous 20.
     */
    private function applyDelta(User $user, float $delta): void
    {
        $currentScore = (float) $user->score_confiance;
        $newScore = max(0, min(100, $currentScore + $delta));

        $updates = ['score_confiance' => $newScore];

        // Suspension automatique si score trop bas
        if ($newScore < 20.0 && $user->statut === 'actif') {
            $updates['statut'] = 'suspendu';
            $updates['motif_statut'] = 'Score de confiance trop bas (' . number_format($newScore, 2) . ')';
        }

        $user->update($updates);
    }
}
