<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'role' => $this->role,
            'statut' => $this->statut,
            'score_confiance' => $this->score_confiance,
            'solde_virtuel' => $this->solde_virtuel,
            'created_at' => $this->created_at,
        ];
    }
}
