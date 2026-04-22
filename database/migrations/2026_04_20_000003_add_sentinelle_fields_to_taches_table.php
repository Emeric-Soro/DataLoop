<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('taches', function (Blueprint $table): void {
            $table->boolean('is_sentinelle')->default(false)->after('statut');
            $table->string('reponse_attendue')->nullable()->after('is_sentinelle');
        });
    }

    public function down(): void
    {
        Schema::table('taches', function (Blueprint $table): void {
            $table->dropColumn(['is_sentinelle', 'reponse_attendue']);
        });
    }
};
