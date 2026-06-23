<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : extension de la table `vehicles`
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Ajoute les colonnes nécessaires aux nouvelles fonctionnalités :
 *
 *  1. RECHERCHE PAR VIN SÉCURISÉE
 *     - vin_prefix : 9 premiers caractères du VIN (WMI + VDS partiel). La suite
 *       (numéro de série individuel) n'est PAS présente dans le fichier ASTRA et
 *       ne doit jamais être stockée : on ne conserve donc qu'un préfixe non
 *       individualisant, partagé par une série de véhicules. Indexé pour les
 *       recherches exactes.
 *
 *  2. HOMOLOGATION EUROPÉENNE
 *     - eu_type_approval : numéro de réception européen (ex: "e1*2007/46*0123*05").
 *       Indexé car peut servir de second critère de recherche.
 *
 *  3. DISCRIMINATION VOITURE / MOTO
 *     - vehicle_type : 'car' | 'motorcycle'. Permet de séparer proprement les
 *       deux fichiers ASTRA (TG-Automobil.txt vs TG-Moto.txt) et d'adapter les
 *       calculs d'impôts cantonaux (barèmes différents selon le type).
 *
 *  4. NORME ANTIPOLLUTION (couplage emissionen.txt)
 *     - pollution_norm : norme détaillée issue du fichier des émissions
 *       (ex: "Euro 6d-ISC-FCM"). Distincte de `code_emissions` (déjà présent),
 *       qui reste le code synthétique des fichiers TG.
 * ─────────────────────────────────────────────────────────────────────────────
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {

            // ── 1. VIN (préfixe 9 caractères, non individualisant) ────────────
            $table->char('vin_prefix', 9)
                ->nullable()
                ->after('numero_tg')
                ->index();

            // ── 2. Homologation européenne ────────────────────────────────────
            $table->string('eu_type_approval', 50)
                ->nullable()
                ->after('vin_prefix')
                ->index();

            // ── 3. Discrimination voiture / moto ──────────────────────────────
            // enum natif : contraint la valeur au niveau base de données.
            $table->enum('vehicle_type', ['car', 'motorcycle'])
                ->default('car')
                ->after('variante')
                ->index();

            // ── 4. Norme antipollution détaillée (fichier émissions) ──────────
            $table->string('pollution_norm', 40)
                ->nullable()
                ->after('code_emissions');

            // ── Index composite : recherche VIN limitée aux fiches actives ────
            $table->index(['vin_prefix', 'is_active'], 'idx_vin_active');
            // ── Index composite : filtrage par type fréquent dans les listings ─
            $table->index(['vehicle_type', 'is_active'], 'idx_type_active');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex('idx_vin_active');
            $table->dropIndex('idx_type_active');
            $table->dropIndex(['vin_prefix']);          // index simple auto-nommé
            $table->dropIndex(['eu_type_approval']);
            $table->dropIndex(['vehicle_type']);
            $table->dropColumn([
                'vin_prefix',
                'eu_type_approval',
                'vehicle_type',
                'pollution_norm',
            ]);
        });
    }
};
