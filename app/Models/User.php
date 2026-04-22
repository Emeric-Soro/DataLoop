<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'telephone', 'email', 'password', 'role', 'statut', 'motif_statut', 'score_confiance', 'solde_virtuel'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'score_confiance' => 'decimal:2',
            'solde_virtuel' => 'decimal:2',
        ];
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(Annotation::class, 'utilisateur_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'utilisateur_id');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class, 'utilisateur_id');
    }

    public function contributionReviews(): HasMany
    {
        return $this->hasMany(ContributionReview::class, 'reviewer_id');
    }
}
