<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE imports_log MODIFY COLUMN import_type ENUM('2000','5000','2000-moto','emissions','verbrauch') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE imports_log MODIFY COLUMN import_type ENUM('2000','5000','2000-moto','emissions') NOT NULL");
    }
};
