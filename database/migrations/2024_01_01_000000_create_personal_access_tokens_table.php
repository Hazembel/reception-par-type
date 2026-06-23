<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration Sanctum — table `personal_access_tokens`
 *
 * Migration standard de Laravel Sanctum, incluse explicitement pour garantir
 * qu'elle s'exécute AVANT la migration d'extension (000008) qui lui ajoute
 * les colonnes métier B2B (plan_id, monthly_quota, etc.).
 *
 * Note : `tokenable_id` est en string pour supporter les ULID de la table users.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            // morphs en string pour compatibilité ULID (users.id est un ULID)
            $table->string('tokenable_type');
            $table->string('tokenable_id'); // ULID-compatible
            $table->index(['tokenable_type', 'tokenable_id']);
            $table->string('name');
            $table->string('token', 64)->unique();   // SHA-256 du token en clair
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
