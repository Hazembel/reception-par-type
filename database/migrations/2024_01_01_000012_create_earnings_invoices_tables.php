<?php
// ═══════════════════════════════════════════════════════════════════
// MIGRATION 3 : Table `affiliate_earnings`
// ═══════════════════════════════════════════════════════════════════
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        Schema::create('affiliate_earnings', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('affiliate_id')
                ->constrained('affiliates')
                ->cascadeOnDelete();

            /** Utilisateur converti (l'acheteur) */
            $table->foreignUlid('buyer_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /** Référence de la transaction PayPal */
            $table->string('paypal_order_id', 50)->unique()->index();

            /** Montant total de la vente en centimes CHF */
            $table->unsignedInteger('sale_amount_cts');

            /** Commission calculée en centimes CHF */
            $table->unsignedInteger('commission_cts');

            /** Taux appliqué (snapshot au moment de la vente) en pour-mille */
            $table->unsignedSmallInteger('rate_permille');

            /**
             * Status :
             *   pending   → En attente de validation admin
             *   approved  → Validé, prêt pour virement
             *   paid      → Virement effectué
             *   cancelled → Remboursement/litige — commission annulée
             */
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])
                ->default('pending')
                ->index();

            /** Référence de virement quand status = paid */
            $table->string('payment_reference', 100)->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['affiliate_id', 'status'], 'idx_aff_status');
            $table->index(['affiliate_id', 'created_at'], 'idx_aff_date');
        });

        // ═══════════════════════════════════════════════════════════
        // MIGRATION 4 : Table `invoices`
        // ═══════════════════════════════════════════════════════════
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignUlid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /**
             * invoice_number : Numéro séquentiel suisse formaté
             * Format : RPT-{ANNÉE}-{NUMÉRO_PADDED_5}
             * Ex: RPT-2024-00001, RPT-2024-00042
             */
            $table->string('invoice_number', 20)->unique()->index();

            /** Référence PayPal liée — UNIQUE : garantit l'idempotence (une seule
             *  facture par ordre PayPal, même sous double-webhook concurrent).
             *  nullable car certaines factures peuvent être créées manuellement. */
            $table->string('paypal_order_id', 50)->nullable()->unique();

            // ── Montants en centimes (toujours entiers) ──────────────
            $table->unsignedInteger('subtotal_cts');   // Montant HT
            $table->unsignedInteger('tax_rate_bp');    // Taux TVA en points de base (ex: 810 = 8.10%)
            $table->unsignedInteger('tax_amount_cts'); // Montant TVA
            $table->unsignedInteger('total_cts');      // Total TTC

            /**
             * is_vat_exempt : Vrai si exonéré de TVA (chiffre d'affaires < 100 000 CHF/an)
             * Dans ce cas tax_rate_bp = 0 et tax_amount_cts = 0
             */
            $table->boolean('is_vat_exempt')->default(true);

            /** Devise (toujours CHF pour reception-par-type.ch) */
            $table->string('currency', 3)->default('CHF');

            // ── Adresse client (snapshot au moment de la facture) ─────
            $table->json('billing_address');
            // Format: {"name":"...", "line1":"...", "line2":"...", "postal":"...", "city":"...", "country":"CH"}

            // ── Description des lignes ────────────────────────────────
            $table->json('line_items');
            // Format: [{"description":"Abonnement Pro 1 mois","qty":1,"unit_cts":4900,"total_cts":4900}]

            // ── Fichier PDF ───────────────────────────────────────────
            $table->string('pdf_path', 500)->nullable();

            /**
             * Status :
             *   draft    → Générée mais pas encore envoyée
             *   sent     → Envoyée par e-mail
             *   paid     → Marquée comme payée (confirmé PayPal)
             *   cancelled→ Avoir émis
             */
            $table->enum('status', ['draft', 'sent', 'paid', 'cancelled'])
                ->default('draft')
                ->index();

            $table->timestamp('issued_at');
            $table->timestamp('due_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'idx_user_invoices');
        });
    }

    public function down(): void {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('affiliate_earnings');
    }
};
