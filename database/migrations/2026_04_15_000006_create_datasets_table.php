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
        Schema::create('datasets', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('version')->default('v1.0');
            $table->unsignedInteger('nb_images')->default(0);
            $table->unsignedInteger('nb_annotations_validees')->default(0);
            $table->string('format_export')->default('json');
            $table->timestamps();

            $table->index('version');
            $table->index('format_export');
        });

        Schema::create('annotation_dataset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annotation_id')->constrained('annotations')->cascadeOnDelete();
            $table->foreignId('dataset_id')->constrained('datasets')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['annotation_id', 'dataset_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annotation_dataset');
        Schema::dropIfExists('datasets');
    }
};
