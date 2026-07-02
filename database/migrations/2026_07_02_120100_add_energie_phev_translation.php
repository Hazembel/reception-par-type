<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ASTRA uses 'C' for both CNG (old letter code) and PHEV (combined fuel).
        // We add a synthetic 'C-PHEV' code used only in the blade when the vehicle
        // has declared electric range, distinguishing PHEVs from true CNG vehicles.
        DB::table('vehicle_translations')->insert([
            'category'   => 'energie',
            'code'       => 'C-PHEV',
            'label_fr'   => 'Hybride rechargeable (PHEV)',
            'label_de'   => 'Plug-in-Hybrid (PHEV)',
            'label_it'   => 'Ibrido plug-in (PHEV)',
            'label_en'   => 'Plug-in Hybrid (PHEV)',
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('vehicle_translations')->where('code', 'C-PHEV')->delete();
    }
};
