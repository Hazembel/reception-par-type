<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('UPDATE vehicles SET autonomie_min = NULL WHERE autonomie_min = 0');
        DB::statement('UPDATE vehicles SET autonomie_max = NULL WHERE autonomie_max = 0');
        DB::statement('UPDATE vehicles SET co2_wltp = NULL WHERE co2_wltp = 0');
    }

    public function down(): void
    {
        // Non-reversible data cleanup.
    }
};
