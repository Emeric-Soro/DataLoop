<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tache_id' => $this->tache_id,
            'utilisateur_id' => $this->utilisateur_id,
            'reponse_choisie' => $this->reponse_choisie,
            'temps_execution_ms' => $this->temps_execution_ms,
            'transaction' => new TransactionResource($this->whenLoaded('transaction')),
            'tache' => new TaskResource($this->whenLoaded('tache')),
            'created_at' => $this->created_at,
        ];
    }
}
