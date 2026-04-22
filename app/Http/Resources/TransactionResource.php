<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'utilisateur_id' => $this->utilisateur_id,
            'annotation_id' => $this->annotation_id,
            'type' => $this->type,
            'libelle' => $this->libelle,
            'montant' => $this->montant,
            'solde_avant' => $this->solde_avant,
            'solde_apres' => $this->solde_apres,
            'reference_tache' => $this->reference_tache,
            'created_at' => $this->created_at,
        ];
    }
}
