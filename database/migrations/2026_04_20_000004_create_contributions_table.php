<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contributions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type_contenu', ['image', 'texte', 'audio']);
            $table->string('fichier_url')->nullable();
            $table->unsignedBigInteger('taille_fichier')->nullable();
            $table->text('texte_contenu')->nullable();
            $table->enum('langue', ['francais', 'nouchi', 'dioula', 'baoule', 'bete', 'autre'])->default('francais');
            $table->text('description');
            $table->string('categorie')->nullable()->index();
            $table->enum('statut', ['en_attente', 'en_revue', 'validee', 'rejetee', 'integree'])->default('en_attente')->index();
            $table->unsignedInteger('nb_reviews_requises')->default(3);
            $table->unsignedInteger('nb_reviews_positives')->default(0);
            $table->unsignedInteger('nb_reviews_negatives')->default(0);
            $table->decimal('score_consensus', 5, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['utilisateur_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
