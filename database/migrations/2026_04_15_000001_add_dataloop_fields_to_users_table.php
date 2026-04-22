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
        Schema::table('users', function (Blueprint $table) {
            $table->string('telephone', 20)->unique()->after('name');
            $table->string('mot_de_passe_hash')->after('email');
            $table->enum('role', ['contributeur', 'admin'])->default('contributeur')->after('password');
            $table->decimal('score_confiance', 5, 2)->default(50.00)->index('idx_score')->after('role');
            $table->decimal('solde_virtuel', 12, 2)->default(0)->after('score_confiance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_score');
            $table->dropUnique(['telephone']);
            $table->dropColumn(['telephone', 'mot_de_passe_hash', 'role', 'score_confiance', 'solde_virtuel']);
        });
    }
};
