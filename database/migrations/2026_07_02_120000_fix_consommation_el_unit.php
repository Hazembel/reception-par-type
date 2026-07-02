<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // EL_Verbrauch_WLTP was stored raw in Wh/km; convert to kWh/100km (÷10).
        // Values above 50 are clearly in Wh/km (no EV uses 50+ kWh/100km).
        DB::statement('UPDATE vehicles SET consommation_el = ROUND(consommation_el / 10, 2) WHERE consommation_el > 50');
    }

    public function down(): void
    {
        DB::statement('UPDATE vehicles SET consommation_el = ROUND(consommation_el * 10, 2) WHERE consommation_el IS NOT NULL');
    }
};
