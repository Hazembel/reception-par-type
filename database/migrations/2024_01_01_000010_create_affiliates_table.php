<?php
// ═══════════════════════════════════════════════════════════════════
// MIGRATION 1 : Table `affiliates`
// ═══════════════════════════════════════════════════════════════════
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();

            $table->foreignUlid('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            /**
             * affiliate_code : Code unique lisible (ex: "GARAGE10", "HONDA-GE")
             * Visible dans les URLs : ?ref=GARAGE10
             * Majuscules + chiffres + tirets uniquement.
             */
            $table->string('affiliate_code', 30)->unique()->index();

            /**
             * commission_rate : Taux en pour-mille (‰) pour éviter les flottants.
             * Ex: 100 = 10.0%, 150 = 15.0%, 200 = 20.0%
             */
            $table->unsignedSmallInteger('commission_rate_permille')->default(100);

            /**
             * iban : IBAN suisse pour les virements de commission (CH.. format).
             * Stocké chiffré via Laravel Encryption (cast 'encrypted').
             */
            $table->text('iban_encrypted')->nullable();

            /**
             * beneficiary_name : Nom du bénéficiaire pour le virement.
             */
            $table->string('beneficiary_name', 150)->nullable();

            // ── Statut ──────────────────────────────────────────────────
            $table->enum('status', ['pending', 'active', 'suspended', 'terminated'])
                ->default('pending')
                ->index();

            // ── Statistiques dénormalisées (mis à jour par job) ──────────
            $table->unsignedInteger('total_clicks')->default(0);
            $table->unsignedInteger('total_conversions')->default(0);
            $table->unsignedInteger('total_earned_cts')->default(0); // Centimes CHF

            /**
             * Seuil minimum de paiement (centimes CHF). Défaut : 5000 = 50.00 CHF
             */
            $table->unsignedInteger('payout_threshold_cts')->default(5000);

            $table->timestamp('last_paid_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('affiliates'); }
};
