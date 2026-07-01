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
        // unsignedSmallInteger max = 65,535 kg — trucks/trailers exceed this.
        // unsignedMediumInteger max = 16,777,215 kg — sufficient for all vehicles.
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedMediumInteger('poids_vide')->nullable()->change();
            $table->unsignedMediumInteger('poids_total')->nullable()->change();
            $table->unsignedMediumInteger('poids_remorquable')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedSmallInteger('poids_vide')->nullable()->change();
            $table->unsignedSmallInteger('poids_total')->nullable()->change();
            $table->unsignedSmallInteger('poids_remorquable')->nullable()->change();
        });
    }
};
