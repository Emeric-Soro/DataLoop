<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tache extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_id',
        'type_tache',
        'question',
        'options_reponse',
        'nb_annotations_requises',
        'statut',
    ];

    protected $casts = [
        'options_reponse' => 'array',
    ];

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(Annotation::class);
    }
}
