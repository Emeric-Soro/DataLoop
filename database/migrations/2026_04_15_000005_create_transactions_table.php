<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('annotation_id')->unique()->constrained('annotations')->cascadeOnDelete();
            $table->enum('type', ['gain', 'retrait']);
            $table->decimal('montant', 12, 2);
            $table->decimal('solde_avant', 12, 2);
            $table->decimal('solde_apres', 12, 2);
            $table->string('reference_tache')->nullable();
            $table->timestamps();

            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
