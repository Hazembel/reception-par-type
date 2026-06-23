<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Table `anomaly_reports`
 *
 * Stocke les signalements d'anomalies soumis via le modal "Signaler une anomalie".
 * Permet à l'admin de tracer et traiter les rapports sans dépendre uniquement
 * des e-mails (qui peuvent se perdre dans les spams).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anomaly_reports', function (Blueprint $table) {

            $table->id();

            // ── Référence véhicule ────────────────────────────────────────────
            $table->foreignUlid('vehicle_id')
                ->nullable()
                ->constrained('vehicles')
                ->nullOnDelete();

            $table->string('numero_tg', 50)->index(); // Redondant pour retrouver si vehicle supprimé

            // ── Détails du signalement ────────────────────────────────────────
            /**
             * field_reported : Champ technique concerné par l'anomalie.
             * Ex: "entraxe", "poids_vide", "pneus_origine", "co2"
             */
            $table->string('field_reported', 100)->nullable();

            /**
             * description : Description libre du problème (max 1000 chars).
             */
            $table->string('description', 1000);

            // ── Auteur ────────────────────────────────────────────────────────
            /**
             * reporter_user_id : Utilisateur connecté (null si anonyme).
             */
            $table->foreignUlid('reporter_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /**
             * reporter_email : E-mail de contact (requis si non connecté).
             */
            $table->string('reporter_email', 255)->nullable();

            // ── Statut de traitement ──────────────────────────────────────────
            $table->enum('status', ['pending', 'in_review', 'resolved', 'rejected'])
                ->default('pending')
                ->index();

            /**
             * admin_note : Note interne de l'admin lors du traitement.
             */
            $table->text('admin_note')->nullable();

            /**
             * ip_address : Pour la détection des signalements abusifs.
             */
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomaly_reports');
    }
};
