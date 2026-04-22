<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type_tache' => $this->type_tache,
            'question' => $this->question,
            'options_reponse' => $this->options_reponse,
            'nb_annotations_requises' => $this->nb_annotations_requises,
            'statut' => $this->statut,
            'is_sentinelle' => $this->is_sentinelle,
            'image' => [
                'id' => $this->image?->id,
                'url_stockage' => $this->image?->url_stockage,
                'categorie' => $this->image?->categorie,
            ],
            'annotations_count' => $this->whenCounted('annotations'),
            'created_at' => $this->created_at,
        ];
    }
}
