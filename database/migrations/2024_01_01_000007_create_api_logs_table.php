<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Table `api_logs`
 *
 * Enregistre chaque appel API entrant pour :
 *  - La facturation au volume (B2B niveaux 6/7/8)
 *  - Le monitoring et les alertes d'anomalies
 *  - La conformité légale (audit trail RGPD/LPD)
 *  - L'analyse d'usage pour la tarification
 *
 * Volume estimé : jusqu'à 500 000 lignes/mois pour les clients Enterprise.
 * Partitionnement mensuel recommandé en production.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {

            // Auto-increment standard : cette table est interne,
            // jamais exposée dans les URLs publiques.
            $table->bigIncrements('id');

            // ── Identification de la requête ───────────────────────────────
            /**
             * user_id : Propriétaire de la clé API.
             * Nullable pour les requêtes avec token invalide
             * (on logue quand même pour détecter les attaques).
             */
            $table->foreignUlid('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /**
             * api_key_id : Clé Sanctum utilisée.
             * Nullable si token non identifié (tentative frauduleuse).
             */
            $table->foreignId('api_key_id')
                ->nullable()
                ->constrained('personal_access_tokens')
                ->nullOnDelete();

            // ── Détails de la requête ──────────────────────────────────────
            /**
             * endpoint : Route appelée normalisée (ex: "vehicle.tg", "vehicle.tyres").
             * Identifiant court, pas l'URL complète, pour éviter les injections
             * via des URLs malformées longues.
             */
            $table->string('endpoint', 50)->index();

            /**
             * method : Verbe HTTP (GET, POST, etc.)
             */
            $table->string('method', 10)->default('GET');

            /**
             * numero_tg : Numéro TG interrogé (extrait de la requête).
             * Indexé pour permettre les requêtes "qui a consulté ce TG ?".
             */
            $table->string('numero_tg', 50)->nullable()->index();

            /**
             * status_code : Code HTTP retourné (200, 404, 429, 401, etc.)
             */
            $table->unsignedSmallInteger('status_code')->index();

            /**
             * response_time_ms : Durée de traitement en millisecondes.
             * Utile pour le monitoring des performances et les SLA B2B.
             */
            $table->unsignedSmallInteger('response_time_ms')->nullable();

            // ── Source de la requête ───────────────────────────────────────
            /**
             * ip_address : IP source (IPv4 ou IPv6).
             * Utilisé pour la détection d'abus et le rate limiting.
             */
            $table->string('ip_address', 45)->nullable()->index();

            /**
             * user_agent : En-tête User-Agent tronqué à 255 chars.
             */
            $table->string('user_agent', 255)->nullable();

            // ── Billing ────────────────────────────────────────────────────
            /**
             * billed : Indique si cet appel a été comptabilisé dans le quota.
             * FALSE pour les erreurs 4xx non facturées (ex: 404 TG inconnu).
             * TRUE pour les succès 2xx et certains 4xx (401, 403, 429).
             */
            $table->boolean('billed')->default(false)->index();

            /**
             * tokens_charged : Nombre de tokens débités pour cet appel.
             * 0 pour les endpoints gratuits, 1 pour les endpoints standard,
             * N pour les endpoints bulk (non implémentés ici mais prévu).
             */
            $table->unsignedTinyInteger('tokens_charged')->default(0);

            // ── Timestamp unique (pas de updated_at nécessaire) ────────────
            $table->timestamp('created_at')->useCurrent()->index();

            // ── Index composites pour les rapports de facturation ──────────
            $table->index(['user_id', 'created_at'],         'idx_user_date');
            $table->index(['user_id', 'billed', 'created_at'], 'idx_billing_report');
            $table->index(['status_code', 'created_at'],     'idx_status_date');
            $table->index(['endpoint', 'created_at'],        'idx_endpoint_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
