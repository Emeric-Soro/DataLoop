<?php

namespace App\Services;

use App\Models\Annotation;
use App\Models\Tache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ConsensusService
{
    public function __construct(
        private TrustScoreService $trustScoreService,
        private RewardService $rewardService,
    ) {}

    /**
     * Vérifie si une tâche a atteint le consensus.
     *
     * @return string|null La réponse consensuelle, ou null si pas de consensus
     */
    public function checkConsensus(Tache $task): ?string
    {
        $annotations = $task->annotations()->get();
        $required = $task->nb_annotations_requises;

        if ($annotations->count() < $required) {
            return null;
        }

        $config = Cache::get('system_config', []);
        $seuil = ($config['seuil_consensus'] ?? 66) / 100;

        // Compter les votes par réponse
        $votes = $annotations->groupBy('reponse_choisie');
        $bestAnswer = $votes->sortByDesc(fn (Collection $group): int => $group->count())->keys()->first();
        $bestCount = $votes[$bestAnswer]->count();

        $ratio = $bestCount / $annotations->count();

        if ($ratio >= $seuil) {
            return $bestAnswer;
        }

        return null;
    }

    /**
     * Applique le résultat du consensus :
     * - Met à jour le statut de la tâche → 'terminee'
     * - Récompense les annotateurs en accord avec le consensus
     * - Pénalise le score de confiance des annotateurs en désaccord
     */
    public function applyConsensus(Tache $task, string $consensusAnswer): void
    {
        $task->update(['statut' => 'terminee']);

        $annotations = $task->annotations()->with('utilisateur')->get();

        foreach ($annotations as $annotation) {
            /** @var Annotation $annotation */
            $user = $annotation->utilisateur;
            $isCorrect = $annotation->reponse_choisie === $consensusAnswer;

            // Mettre à jour le score de confiance
            $this->trustScoreService->updateAfterConsensus($user, $isCorrect);

            // Récompenser uniquement les annotateurs en accord
            if ($isCorrect) {
                // Vérifier qu'une transaction n'existe pas déjà pour cette annotation
                if (!$annotation->transaction()->exists()) {
                    $this->rewardService->rewardForAnnotation($user, $annotation);
                }
            }
        }
    }

    /**
     * Vérifie si une annotation correspond à une tâche sentinelle
     * et met à jour le score de confiance en conséquence.
     *
     * @return bool true si c'était une sentinelle
     */
    public function handleSentinelle(Tache $task, Annotation $annotation): bool
    {
        if (!$task->is_sentinelle || !$task->reponse_attendue) {
            return false;
        }

        $isCorrect = $annotation->reponse_choisie === $task->reponse_attendue;
        $user = $annotation->utilisateur;

        // Impact plus fort pour les sentinelles (+3 / -8)
        $this->trustScoreService->updateAfterSentinelle($user, $isCorrect);

        return true;
    }
}
