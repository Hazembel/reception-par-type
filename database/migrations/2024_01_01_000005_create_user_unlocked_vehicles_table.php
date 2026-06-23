<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Table `user_unlocked_vehicles`
 *
 * Table pivot qui mémorise les véhicules débloqués par un utilisateur
 * via ses jetons (niveaux 2 et 3). L'accès est valide pour le mois calendaire
 * en cours — un cron remet à zéro les droits le 1er de chaque mois.
 *
 * Logique métier :
 *   - Niveau 2/3 : 1 jeton = 1 déverrouillage mémorisé ici.
 *   - Même véhicule consulté N fois ce mois-ci → 0 jeton supplémentaire.
 *   - Au mois suivant → la ligne est archivée et l'accès est recalculé.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_unlocked_vehicles', function (Blueprint $table) {

            $table->id();

            // ── Clés étrangères ───────────────────────────────────────────────
            // ULID pour user_id (correspond à la PK de la table users)
            $table->foreignUlid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // ULID pour vehicle_id (correspond à la PK de la table vehicles)
            $table->foreignUlid('vehicle_id')
                ->constrained('vehicles')
                ->cascadeOnDelete();

            // ── Données de déverrouillage ─────────────────────────────────────
            /**
             * unlocked_at : Timestamp exact du déverrouillage.
             * Utilisé pour vérifier si le déverrouillage est encore valide
             * ce mois-ci (YEAR + MONTH identiques à aujourd'hui).
             */
            $table->timestamp('unlocked_at');

            /**
             * tokens_spent : Nombre de jetons consommés pour ce déverrouillage.
             * Généralement 1, mais extensible pour des contenus premium futurs.
             */
            $table->unsignedTinyInteger('tokens_spent')->default(1);

            $table->timestamps();

            // ── Unicité : un seul déverrouillage actif par couple user/vehicle ─
            // (On peut avoir plusieurs lignes historiques mais une seule active)
            $table->unique(['user_id', 'vehicle_id'], 'uniq_user_vehicle');

            // ── Index pour les requêtes fréquentes ────────────────────────────
            $table->index(['user_id', 'unlocked_at'], 'idx_user_unlocked_at');
            $table->index('vehicle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_unlocked_vehicles');
    }
};
