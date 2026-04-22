<?php

namespace App\Services;

use App\Models\Annotation;
use App\Models\Transaction;
use App\Models\User;

class RewardService
{
    /**
     * Récompense un utilisateur pour une annotation validée par consensus.
     */
    public function rewardForAnnotation(
        User $user,
        Annotation $annotation,
        float $amount = 50.00,
        string $label = 'Annotation validée par consensus',
    ): Transaction {
        return $this->credit($user, $amount, $label, $annotation);
    }

    /**
     * Récompense un utilisateur pour une review de contribution.
     */
    public function rewardForReview(
        User $user,
        float $amount = 25.00,
        string $label = 'Review de contribution',
    ): Transaction {
        return $this->credit($user, $amount, $label);
    }

    /**
     * Récompense le contributeur original quand sa contribution est validée.
     */
    public function rewardForContribution(
        User $user,
        float $amount = 100.00,
        string $label = 'Contribution validée par la communauté',
    ): Transaction {
        return $this->credit($user, $amount, $label);
    }

    /**
     * Crédite le solde d'un utilisateur avec lock pessimiste.
     */
    private function credit(
        User $user,
        float $amount,
        string $label,
        ?Annotation $annotation = null,
    ): Transaction {
        $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

        $soldeAvant = (float) $lockedUser->solde_virtuel;
        $soldeApres = $soldeAvant + $amount;

        $lockedUser->update([
            'solde_virtuel' => $soldeApres,
        ]);

        return Transaction::create([
            'utilisateur_id' => $lockedUser->id,
            'annotation_id' => $annotation?->id,
            'type' => 'gain',
            'libelle' => $label,
            'montant' => $amount,
            'solde_avant' => $soldeAvant,
            'solde_apres' => $soldeApres,
            'reference_tache' => $annotation ? (string) $annotation->tache_id : null,
        ]);
    }
}
