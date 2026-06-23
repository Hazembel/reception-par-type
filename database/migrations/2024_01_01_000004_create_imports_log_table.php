<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Table `imports_log`
 *
 * Historique complet de chaque import ASTRA (dossier 2000 et 5000).
 * Permet de :
 *  - Tracer chaque exécution (succès, échec partiel, échec total)
 *  - Calculer les performances (durée, lignes/seconde)
 *  - Alerter en cas d'anomalie (trop peu de lignes insérées → fichier corrompu ?)
 *  - Rejouer un import manqué depuis l'interface admin
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imports_log', function (Blueprint $table) {

            $table->id();

            // ── Identification du fichier traité ──────────────────────────────
            /**
             * import_type : "2000" (gros fichier mensuel) ou "5000" (newsletter hebdo)
             */
            $table->enum('import_type', ['2000', '5000'])->index();

            /**
             * filename : Nom du fichier traité (ex: "TG-Automobil.txt", "TG-Newsletter-2024-03.txt")
             * Stocké sans le chemin complet pour la portabilité.
             */
            $table->string('filename', 255);

            /**
             * file_path : Chemin complet absolu au moment de l'import.
             * Utile pour rejouer l'import ou déboguer.
             */
            $table->string('file_path', 500)->nullable();

            /**
             * file_size_bytes : Taille du fichier en octets.
             * Permet de détecter un fichier tronqué ou anormalement petit.
             */
            $table->unsignedBigInteger('file_size_bytes')->nullable();

            /**
             * file_hash : SHA-256 du fichier.
             * Évite de réimporter un fichier identique au précédent (idempotence).
             */
            $table->string('file_hash', 64)->nullable()->index();

            // ── Statut & Progression ──────────────────────────────────────────
            /**
             * status :
             *   pending   → Job créé, pas encore démarré
             *   running   → Import en cours
             *   completed → Import terminé sans erreur
             *   partial   → Import terminé avec des lignes en erreur (< 5%)
             *   failed    → Import échoué (erreur critique ou > 5% de lignes KO)
             */
            $table->enum('status', ['pending', 'running', 'completed', 'partial', 'failed'])
                ->default('pending')
                ->index();

            // ── Statistiques d'import ─────────────────────────────────────────
            $table->unsignedInteger('total_lines')->default(0);     // Total lignes dans le fichier
            $table->unsignedInteger('lines_inserted')->default(0);  // Nouvelles fiches créées
            $table->unsignedInteger('lines_updated')->default(0);   // Fiches mises à jour
            $table->unsignedInteger('lines_skipped')->default(0);   // Lignes ignorées (doublons, vides)
            $table->unsignedInteger('lines_failed')->default(0);    // Lignes en erreur

            // ── Timing ────────────────────────────────────────────────────────
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            /**
             * duration_seconds : Calculé à la fin = finished_at - started_at.
             * Stocké dénormalisé pour les requêtes de reporting rapide.
             */
            $table->unsignedInteger('duration_seconds')->nullable();

            // ── Détails d'erreur ──────────────────────────────────────────────
            /**
             * error_message : Message d'erreur principal si status = failed.
             */
            $table->text('error_message')->nullable();

            /**
             * error_details : JSON des erreurs par ligne (max 100 entrées stockées).
             * Format : [{"line": 1234, "tg": "xx.xxx", "error": "..."}]
             */
            $table->json('error_details')->nullable();

            // ── Déclencheur ───────────────────────────────────────────────────
            /**
             * triggered_by : "scheduler" (automatique) ou "admin:{user_ulid}" (manuel)
             */
            $table->string('triggered_by', 100)->default('scheduler');

            $table->timestamps();

            // ── Index pour les requêtes admin fréquentes ──────────────────────
            $table->index(['import_type', 'status'], 'idx_type_status');
            $table->index(['import_type', 'created_at'], 'idx_type_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imports_log');
    }
};
