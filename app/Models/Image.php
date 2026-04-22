<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'url_stockage',
        'categorie',
        'source',
        'metadata_geo',
        'taille_fichier',
    ];

    protected $casts = [
        'metadata_geo' => 'array',
    ];

    public function tache(): HasOne
    {
        return $this->hasOne(Tache::class);
    }
}
