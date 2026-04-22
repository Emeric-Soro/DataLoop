<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributionReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'contribution_id',
        'reviewer_id',
        'note_veracite',
        'is_valid',
        'commentaire',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
    ];

    public function contribution(): BelongsTo
    {
        return $this->belongsTo(Contribution::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
