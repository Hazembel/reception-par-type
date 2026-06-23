<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Table `users`
 *
 * Utilise des ULID (Universally Unique Lexicographically Sortable Identifier)
 * à la place des ID auto-incrémentaux pour éviter l'énumération et le scraping.
 * Implémente MustVerifyEmail + champs Freemium.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            // ── Identité ──────────────────────────────────────────────────────
            // ULID : tri chronologique + non-devinable, idéal pour les URLs publiques
            $table->ulid('id')->primary();

            $table->string('name');
            $table->string('email')->unique();

            // ── Sécurité / Authentification ───────────────────────────────────
            // email_verified_at requis par l'interface MustVerifyEmail de Laravel
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // ── Préférences utilisateur ───────────────────────────────────────
            // Langue préférée de l'interface (fr | de | it | en)
            $table->string('preferred_locale', 5)->default('fr')->index();

            // ── Modèle Freemium / Abonnement ──────────────────────────────────
            /**
             * subscription_level : 1 = Gratuit, 2-7 = Paliers Pro, 8 = Entreprise
             * Permet une montée en gamme progressive sans refonte de BDD.
             */
            $table->unsignedTinyInteger('subscription_level')->default(1)->index();

            /**
             * web_tokens_balance : Solde de tokens achetables à la demande (pay-as-you-go).
             * Utilisé pour les recherches API ou exports premium.
             */
            $table->unsignedInteger('web_tokens_balance')->default(0);

            /**
             * web_monthly_counter : Compteur de recherches/consultations sur le mois en cours.
             * Remis à zéro par un job planifié (Scheduler) le 1er de chaque mois.
             */
            $table->unsignedInteger('web_monthly_counter')->default(0);

            /**
             * subscribed_until : Date d'expiration de l'abonnement actif.
             * NULL = compte gratuit (niveau 1), sinon abonnement actif.
             */
            $table->timestamp('subscribed_until')->nullable()->index();

            // ── Timestamps standards Laravel ──────────────────────────────────
            $table->timestamps();
            $table->softDeletes(); // Suppression logique pour RGPD / audit
        });

        // Table de réinitialisation de mot de passe (Laravel natif)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Table de sessions (driver database recommandé en prod)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUlid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
