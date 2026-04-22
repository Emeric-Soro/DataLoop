<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dataset extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'version',
        'nb_images',
        'nb_annotations_validees',
        'format_export',
    ];

    public function annotations(): BelongsToMany
    {
        return $this->belongsToMany(Annotation::class, 'annotation_dataset')
            ->withTimestamps();
    }

    public function contributions(): BelongsToMany
    {
        return $this->belongsToMany(Contribution::class, 'contribution_dataset')
            ->withTimestamps();
    }
}
