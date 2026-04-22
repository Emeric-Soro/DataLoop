<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'utilisateur_id',
        'type_contenu',
        'fichier_url',
        'taille_fichier',
        'texte_contenu',
        'langue',
        'description',
        'categorie',
        'statut',
        'nb_reviews_requises',
        'nb_reviews_positives',
        'nb_reviews_negatives',
        'score_consensus',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'score_consensus' => 'decimal:2',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ContributionReview::class);
    }

    public function datasets(): BelongsToMany
    {
        return $this->belongsToMany(Dataset::class, 'contribution_dataset')
            ->withTimestamps();
    }
}
