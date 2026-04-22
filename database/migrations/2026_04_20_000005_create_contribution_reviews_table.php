<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contribution_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contribution_id')->constrained('contributions')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('note_veracite');
            $table->boolean('is_valid');
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->unique(['contribution_id', 'reviewer_id']);
            $table->index('contribution_id');
        });

        Schema::create('contribution_dataset', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contribution_id')->constrained('contributions')->cascadeOnDelete();
            $table->foreignId('dataset_id')->constrained('datasets')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['contribution_id', 'dataset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contribution_dataset');
        Schema::dropIfExists('contribution_reviews');
    }
};
