<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Table `pricing_plans`
 *
 * Source de vérité unique pour les tarifs et quotas du freemium.
 * Remplace les valeurs hardcodées dans config/freemium.php et config/api_plans.php.
 * Les middlewares interrogent cette table (via cache) pour appliquer dynamiquement
 * les restrictions — plus besoin de redéployer pour ajuster les prix.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_plans', function (Blueprint $table) {

            $table->id();

            // ── Identification du niveau ───────────────────────────────────
            /**
             * level : Identifiant métier (1 = Gratuit … 8 = Entreprise).
             * Correspond à users.subscription_level.
             */
            $table->unsignedTinyInteger('level')->unique()->index();

            // ── Noms multilingues ──────────────────────────────────────────
            $table->string('name_fr', 60);                        // "Pro"
            $table->string('name_de', 60)->nullable();            // "Pro"
            $table->string('name_it', 60)->nullable();            // "Pro"
            $table->string('name_en', 60)->nullable();            // "Pro"

            // ── Tarification ───────────────────────────────────────────────
            /**
             * price_monthly_chf : Prix mensuel en CHF (centimes entiers pour éviter les flottants).
             * Ex: 4900 = 49.00 CHF. 0 = gratuit. NULL = sur devis.
             */
            $table->unsignedInteger('price_monthly_chf')->default(0);

            /**
             * price_yearly_chf : Prix annuel (généralement ×10 mois = 2 mois offerts).
             */
            $table->unsignedInteger('price_yearly_chf')->nullable();

            /**
             * token_price_chf : Prix d'un jeton unitaire en centimes (ex: 200 = 2.00 CHF).
             */
            $table->unsignedSmallInteger('token_price_chf')->default(200);

            // ── Quotas ─────────────────────────────────────────────────────
            /**
             * web_monthly_limit : Nombre de consultations web par mois.
             * -1 = illimité (niveaux 5-8).
             */
            $table->integer('web_monthly_limit')->default(10);

            /**
             * api_monthly_limit : Nombre d'appels API par mois.
             * 0 = aucun accès API. -1 = illimité.
             */
            $table->integer('api_monthly_limit')->default(0);

            /**
             * api_rate_per_minute : Taux de requêtes API par minute.
             * 0 = pas d'accès API.
             */
            $table->unsignedSmallInteger('api_rate_per_minute')->default(0);

            // ── Fonctionnalités ────────────────────────────────────────────
            $table->boolean('can_export_pdf')->default(false);
            $table->boolean('can_export_csv')->default(false);
            $table->boolean('can_use_api')->default(false);
            $table->boolean('can_compare')->default(false);
            $table->boolean('can_use_tax_calc')->default(false);

            // ── Visibilité & Statut ────────────────────────────────────────
            /**
             * is_public : Affiché sur la page tarifaire publique.
             * FALSE pour les niveaux internes (niveau 8 = sur devis).
             */
            $table->boolean('is_public')->default(true);

            /**
             * is_active : Plan activé ou désactivé (ex: plan legacy retiré de la vente).
             */
            $table->boolean('is_active')->default(true);

            /**
             * display_order : Ordre d'affichage sur la page /pricing.
             */
            $table->unsignedTinyInteger('display_order')->default(0);

            // ── Métadonnées ────────────────────────────────────────────────
            /**
             * stripe_price_id / paypal_plan_id : Identifiants passerelle paiement.
             * Nullable car le niveau 1 est gratuit et le niveau 8 est sur devis.
             */
            $table->string('stripe_price_id', 50)->nullable();
            $table->string('paypal_plan_id', 50)->nullable();

            $table->text('description_fr')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};
