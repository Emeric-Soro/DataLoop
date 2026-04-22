<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'utilisateur_id',
        'annotation_id',
        'type',
        'libelle',
        'montant',
        'solde_avant',
        'solde_apres',
        'reference_tache',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function annotation(): BelongsTo
    {
        return $this->belongsTo(Annotation::class);
    }
}
