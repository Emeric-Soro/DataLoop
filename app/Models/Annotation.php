<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Annotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'utilisateur_id',
        'tache_id',
        'reponse_choisie',
        'temps_execution_ms',
        'ip_address',
        'device_info',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function tache(): BelongsTo
    {
        return $this->belongsTo(Tache::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public function datasets(): BelongsToMany
    {
        return $this->belongsToMany(Dataset::class, 'annotation_dataset')
            ->withTimestamps();
    }
}
