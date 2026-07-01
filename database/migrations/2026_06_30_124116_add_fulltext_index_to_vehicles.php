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
        // FULLTEXT index allows MATCH...AGAINST instead of LIKE '%term%'
        // LIKE with leading % = full table scan on 220k+ rows every search
        Schema::table('vehicles', function (Blueprint $table) {
            $table->fullText(['marque', 'modele', 'variante'], 'ft_marque_modele_variante');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropFullText('ft_marque_modele_variante');
        });
    }
};
