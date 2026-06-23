<?php
// ═══════════════════════════════════════════════════════════════════
// MIGRATION 2 : Table `affiliate_clicks`
// ═══════════════════════════════════════════════════════════════════
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('affiliate_clicks', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('affiliate_id')
                ->constrained('affiliates')
                ->cascadeOnDelete();

            /** IP source hachée (SHA-256 — RGPD: non réversible) */
            $table->string('ip_hash', 64)->index();

            /** URL de la page source (referrer HTTP, tronqué) */
            $table->string('referrer_url', 500)->nullable();

            /** User-Agent tronqué */
            $table->string('user_agent', 255)->nullable();

            /** Conversion : l'utilisateur a-t-il souscrit après ce clic ? */
            $table->boolean('converted')->default(false)->index();

            $table->timestamp('created_at')->useCurrent()->index();
            // Pas de updated_at : les clics sont immuables

            // Index pour déduplication (anti double-comptage par IP/jour)
            $table->index(['affiliate_id', 'ip_hash', 'created_at'], 'idx_aff_ip_date');
        });
    }
    public function down(): void { Schema::dropIfExists('affiliate_clicks'); }
};
