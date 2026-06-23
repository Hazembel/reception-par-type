<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : Table `vehicle_translations`
 *
 * Traduit les codes techniques codifiés de l'ASTRA vers les 4 langues du site.
 * Exemples :
 *   - Code énergie "01" → FR: "Essence", DE: "Benzin", IT: "Benzina", EN: "Petrol"
 *   - Code boîte "A"  → FR: "Automatique", DE: "Automatik", IT: "Automatica", EN: "Automatic"
 *   - Code émissions "EURO6d" → même terme dans toutes les langues (pas de traduction)
 *
 * Cette table sert de dictionnaire centralisé, évitant la duplication dans `vehicles`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_translations', function (Blueprint $table) {

            $table->id(); // Auto-increment OK ici : table interne, jamais exposée en URL

            // ── Type de code traduit ───────────────────────────────────────────
            /**
             * category : Famille du code (ex: "energie", "boite_vitesse",
             *             "code_emissions", "carrosserie", "couleur")
             */
            $table->string('category', 50)->index();

            /**
             * code : Valeur brute telle qu'elle apparaît dans le fichier ASTRA
             * (ex: "01", "A", "EURO6d", "M6")
             */
            $table->string('code', 30)->index();

            // ── Traductions par langue ────────────────────────────────────────
            $table->string('label_fr', 150); // Libellé Français
            $table->string('label_de', 150); // Libellé Allemand (Deutsch)
            $table->string('label_it', 150); // Libellé Italien (Italiano)
            $table->string('label_en', 150); // Libellé Anglais (English)

            /**
             * sort_order : Ordre d'affichage dans les filtres/listes déroulantes.
             */
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            // ── Contrainte d'unicité : un seul libellé par catégorie + code ──
            $table->unique(['category', 'code'], 'uniq_category_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_translations');
    }
};
