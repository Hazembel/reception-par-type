<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table `processed_payments` — garde d'idempotence des paiements PayPal.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * PROBLÈME RÉSOLU : PayPal peut envoyer le même webhook plusieurs fois (retries
 * réseau, livraison "at-least-once"). Sans garde, ActivateSubscription créditait
 * deux fois les jetons / prolongeait deux fois l'abonnement.
 *
 * Cette table sert de verrou : la première insertion d'un paypal_order_id réussit,
 * toute insertion ultérieure du même ordre échoue (contrainte UNIQUE). Le listener
 * traite donc le paiement UNE seule fois, quelle que soit la fréquence des webhooks.
 *
 * La contrainte d'unicité au niveau base de données protège même contre les
 * race conditions (deux webhooks traités en parallèle par deux workers).
 * ─────────────────────────────────────────────────────────────────────────────
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processed_payments', function (Blueprint $table) {
            $table->id();
            // L'identifiant d'ordre PayPal — clé d'idempotence, strictement unique.
            $table->string('paypal_order_id')->unique();
            // Trace de ce qui a été appliqué (pour audit / débogage).
            $table->string('intent')->nullable();       // ex: "plan:4" | "tokens:50"
            $table->unsignedBigInteger('amount_cts')->default(0);
            $table->string('buyer_id')->nullable();      // ULID de l'acheteur (si identifié)
            $table->timestamp('processed_at')->useCurrent();
            $table->timestamps();

            $table->index('buyer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_payments');
    }
};
