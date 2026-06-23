<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Extension de `personal_access_tokens` (Sanctum)
 *
 * Sanctum stocke déjà les tokens en SHA-256 par défaut depuis v3.
 * Cette migration ajoute les colonnes métier manquantes pour le B2B :
 *  - Nom descriptif de la clé (ex: "Production ERP", "Test Staging")
 *  - Plan tarifaire lié
 *  - Quotas individuels (override du plan si besoin)
 *  - Date d'expiration
 *  - Statistiques d'usage
 *
 * SÉCURITÉ :
 * Sanctum stocke `token` comme SHA-256(token_en_clair).
 * Le token en clair n'est retourné QU'À LA CRÉATION (une seule fois).
 * Après création, il est impossible de le retrouver depuis la BDD.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {

            // ── Identification ─────────────────────────────────────────────
            /**
             * key_label : Nom lisible de la clé (ex: "ERP Production", "Test CI/CD")
             */
            $table->string('key_label', 100)->nullable()->after('name');

            // ── Plan & Quotas ──────────────────────────────────────────────
            /**
             * plan_id : Niveau de plan lié (6=Business, 7=Business+, 8=Enterprise)
             * Dupliqué depuis users.subscription_level pour les lookups rapides.
             */
            $table->unsignedTinyInteger('plan_id')->default(6)->after('key_label');

            /**
             * monthly_quota : Limite mensuelle d'appels (override si non null).
             * NULL = utilise le quota par défaut du plan.
             */
            $table->unsignedInteger('monthly_quota')->nullable()->after('plan_id');

            /**
             * rate_per_minute : Limite de requêtes par minute (override).
             * NULL = utilise le taux par défaut du plan.
             */
            $table->unsignedSmallInteger('rate_per_minute')->nullable()->after('monthly_quota');

            // ── Statistiques ───────────────────────────────────────────────
            /**
             * calls_this_month : Compteur mensuel d'appels (mis à jour via DB::increment).
             * Remis à 0 par le Scheduler le 1er de chaque mois.
             */
            $table->unsignedInteger('calls_this_month')->default(0)->after('rate_per_minute');

            /**
             * calls_total : Total cumulatif depuis la création de la clé.
             */
            $table->unsignedBigInteger('calls_total')->default(0)->after('calls_this_month');

            /**
             * last_used_ip : Dernière IP source (pour la détection d'anomalies).
             */
            $table->string('last_used_ip', 45)->nullable()->after('last_used_at');

            // ── Statut ─────────────────────────────────────────────────────
            // Note : expires_at existe déjà dans la table Sanctum standard,
            // on ne le recrée pas ici (éviterait une erreur "duplicate column").

            /**
             * is_active : Permet de suspendre une clé sans la supprimer
             * (ex: client en retard de paiement, enquête de sécurité).
             */
            $table->boolean('is_active')->default(true)->index()->after('last_used_ip');

            // ── Index pour les lookups fréquents ───────────────────────────
            $table->index(['tokenable_id', 'is_active'], 'idx_user_active_keys');
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn([
                'key_label', 'plan_id', 'monthly_quota', 'rate_per_minute',
                'calls_this_month', 'calls_total', 'last_used_ip',
                'is_active',
            ]);
        });
    }
};
