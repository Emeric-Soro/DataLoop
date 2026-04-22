<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContributionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'utilisateur_id' => $this->utilisateur_id,
            'type_contenu' => $this->type_contenu,
            'fichier_url' => $this->fichier_url,
            'taille_fichier' => $this->taille_fichier,
            'texte_contenu' => $this->texte_contenu,
            'langue' => $this->langue,
            'description' => $this->description,
            'categorie' => $this->categorie,
            'statut' => $this->statut,
            'nb_reviews_requises' => $this->nb_reviews_requises,
            'nb_reviews_positives' => $this->nb_reviews_positives,
            'nb_reviews_negatives' => $this->nb_reviews_negatives,
            'score_consensus' => $this->score_consensus,
            'metadata' => $this->metadata,
            'reviews_count' => $this->whenCounted('reviews'),
            'utilisateur' => new UserResource($this->whenLoaded('utilisateur')),
            'created_at' => $this->created_at,
        ];
    }
}
