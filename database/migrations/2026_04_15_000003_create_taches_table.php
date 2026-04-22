<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('taches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_id')->unique()->constrained('images')->cascadeOnDelete();
            $table->string('type_tache');
            $table->text('question');
            $table->json('options_reponse')->nullable();
            $table->unsignedInteger('nb_annotations_requises')->default(3);
            $table->enum('statut', ['nouvelle', 'en_cours', 'terminee', 'suspendue'])->default('nouvelle')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taches');
    }
};
