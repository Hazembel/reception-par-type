<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Table `vehicles`
 *
 * Contient les données techniques automobiles issues du fichier TARGA de l'ASTRA.
 * Clé principale : `numero_tg` (Numéro de Réception par Type / Typengenehmigung).
 * Utilise un ULID comme PK pour les URLs publiques sécurisées.
 * Un `slug` SEO est généré à partir de marque + modèle + variante + numéro_tg.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {

            // ── Clé primaire sécurisée ────────────────────────────────────────
            $table->ulid('id')->primary();

            // ── Identifiant métier ASTRA ──────────────────────────────────────
            /**
             * numero_tg : Numéro de réception par type (ex: "00.082.000.09.00011")
             * Nettoyé et normalisé lors de l'import (espaces, tirets harmonisés).
             * Clé d'entrée principale du site.
             */
            $table->string('numero_tg', 50)->unique()->index();

            // ── Identification du véhicule ────────────────────────────────────
            $table->string('marque', 100)->index();
            $table->string('modele', 150)->index();
            $table->string('variante', 200)->nullable();

            // ── Slug SEO ──────────────────────────────────────────────────────
            /**
             * slug : URL-friendly, généré via Str::slug(marque + modele + variante + numero_tg)
             * Exemple : "volkswagen-golf-viii-2-0-tdi-00082000090011"
             * Indexé pour les lookups URL, unique pour éviter les doublons.
             */
            $table->string('slug', 255)->unique()->index();

            // ── Motorisation & Énergie ────────────────────────────────────────
            /**
             * energie : Code ASTRA (ex: "01" = Essence, "02" = Diesel, "14" = Électrique)
             * Traduit via la table vehicle_translations.
             */
            $table->string('energie', 10)->nullable()->index();

            /**
             * puissance_kw : Puissance maximale en kilowatts (standard européen).
             */
            $table->unsignedSmallInteger('puissance_kw')->nullable();

            /**
             * cylindree : Cylindrée moteur en cm³ (0 pour les véhicules électriques).
             */
            $table->unsignedInteger('cylindree')->nullable();

            /**
             * boite_vitesse : Code ASTRA (ex: "M" = Manuelle, "A" = Automatique)
             * Traduit via la table vehicle_translations.
             */
            $table->string('boite_vitesse', 5)->nullable();

            // ── Masses ───────────────────────────────────────────────────────
            /**
             * Toutes les masses sont en kilogrammes, conformément aux données ASTRA.
             */
            $table->unsignedSmallInteger('poids_vide')->nullable();       // Masse à vide
            $table->unsignedSmallInteger('poids_total')->nullable();       // PMA (Poids Maximum Autorisé)
            $table->unsignedSmallInteger('poids_remorquable')->nullable(); // Charge remorquable max (freiné)

            // ── Émissions ─────────────────────────────────────────────────────
            /**
             * co2 : Émissions de CO2 en g/km (cycle WLTP ou NEDC selon année).
             * NULL pour les véhicules 100% électriques.
             */
            $table->unsignedSmallInteger('co2')->nullable();

            /**
             * code_emissions : Norme d'émissions (ex: "EURO6d", "EURO5", "EURO4")
             * Traduit via la table vehicle_translations.
             */
            $table->string('code_emissions', 20)->nullable()->index();

            // ── Jantes & Pneumatiques ─────────────────────────────────────────
            /**
             * Données de montage des roues pour la fiche technique complète.
             */
            $table->unsignedTinyInteger('nb_trous')->nullable();    // Nombre de trous de fixation (4, 5...)
            $table->unsignedSmallInteger('entraxe')->nullable();    // Entraxe en mm (ex: 112)
            $table->unsignedSmallInteger('alesage')->nullable();    // Alésage du moyeu en mm (ex: 57.1)

            /**
             * deport_et : Déport (ET/Offset) en mm.
             * Peut être négatif sur certains véhicules (valeur signée).
             */
            $table->smallInteger('deport_et')->nullable();

            /**
             * pneus_origine : Dimensions des pneumatiques d'origine (ex: "205/55 R16 91H")
             * Stocké en texte brut car peut contenir plusieurs tailles optionnelles.
             */
            $table->string('pneus_origine', 255)->nullable();

            // ── Métadonnées & Statut ──────────────────────────────────────────
            /**
             * is_active : Permet de désactiver une fiche sans la supprimer (rétractation ASTRA)
             */
            $table->boolean('is_active')->default(true)->index();

            /**
             * imported_at : Date du dernier import depuis les fichiers ASTRA.
             * Permet de détecter les fiches obsolètes.
             */
            $table->timestamp('imported_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Index composites pour les recherches fréquentes ───────────────
            $table->index(['marque', 'modele'], 'idx_marque_modele');
            $table->index(['energie', 'is_active'], 'idx_energie_active');
            $table->index(['code_emissions', 'is_active'], 'idx_emissions_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
