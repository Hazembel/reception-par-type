<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Combined consumption NEDC (l/100km or kWh/100km) — verbrauch.txt ZT_Verbrauch
            $table->decimal('consommation_mixte', 5, 2)->nullable()->after('co2');
            // Combined CO2 WLTP (g/km) — verbrauch.txt ZT_CO2_WLTP
            $table->unsignedSmallInteger('co2_wltp')->nullable()->after('consommation_mixte');
            // Combined consumption WLTP (l/100km or kWh/100km) — verbrauch.txt ZT_Verbrauch_WLTP
            $table->decimal('consommation_wltp', 5, 2)->nullable()->after('co2_wltp');
            // Electric consumption (kWh/100km) — verbrauch.txt EL_Verbrauch_WLTP
            $table->decimal('consommation_el', 5, 2)->nullable()->after('consommation_wltp');
            // Electric range min/max km — verbrauch.txt EL_Reichweite_von/bis_WLTP
            $table->unsignedSmallInteger('autonomie_min')->nullable()->after('consommation_el');
            $table->unsignedSmallInteger('autonomie_max')->nullable()->after('autonomie_min');
            // Energy efficiency label (A+, A, B, C…) — verbrauch.txt Energieeffizienzkategorie
            $table->char('energie_label', 3)->nullable()->after('autonomie_max');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'consommation_mixte', 'co2_wltp', 'consommation_wltp',
                'consommation_el', 'autonomie_min', 'autonomie_max', 'energie_label',
            ]);
        });
    }
};
