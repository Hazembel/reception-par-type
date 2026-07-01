<?php

namespace Database\Seeders;

use App\Models\VehicleTranslation;
use Illuminate\Database\Seeder;

/**
 * Seeder : VehicleTranslationSeeder
 *
 * Peuple la table `vehicle_translations` avec les codes ASTRA et leurs libellés
 * dans les 4 langues du site (FR / DE / IT / EN).
 *
 * Codes source : fichiers ASTRA TG-Automobil.txt (dossier 2000).
 * Catégories couvertes :
 *   - energie       : type de carburant / Antrieb (champ "22 bauart treibstoff")
 *   - boite_vitesse : type de boîte     / Getriebe (champ "15 getriebe 1")
 */
class VehicleTranslationSeeder extends Seeder
{
    public function run(): void
    {
        // Flush the translation cache so UI picks up new values immediately
        $this->clearCache();

        $this->seedEnergie();
        $this->seedBoiteVitesse();

        $this->command->info('✅  VehicleTranslation seeded: ' . VehicleTranslation::count() . ' rows.');
    }

    // ── Énergie / Antrieb ────────────────────────────────────────────────────

    private function seedEnergie(): void
    {
        $rows = [
            // Numeric codes (older ASTRA format)
            ['code' => '01', 'label_fr' => 'Essence',                    'label_de' => 'Benzin',                      'label_it' => 'Benzina',                      'label_en' => 'Petrol',                        'sort_order' => 1],
            ['code' => '02', 'label_fr' => 'Diesel',                     'label_de' => 'Diesel',                      'label_it' => 'Diesel',                       'label_en' => 'Diesel',                        'sort_order' => 2],
            ['code' => '03', 'label_fr' => 'Électrique',                  'label_de' => 'Elektrisch',                  'label_it' => 'Elettrico',                    'label_en' => 'Electric',                      'sort_order' => 3],
            ['code' => '04', 'label_fr' => 'Hybride essence-électrique',  'label_de' => 'Benzin-Elektrisch-Hybrid',    'label_it' => 'Ibrido benzina-elettrico',      'label_en' => 'Petrol-electric hybrid',        'sort_order' => 4],
            ['code' => '05', 'label_fr' => 'Gaz naturel (CNG)',           'label_de' => 'Erdgas (CNG)',                'label_it' => 'Gas naturale (CNG)',            'label_en' => 'Natural gas (CNG)',              'sort_order' => 5],
            ['code' => '06', 'label_fr' => 'Hybride diesel-électrique',   'label_de' => 'Diesel-Elektrisch-Hybrid',   'label_it' => 'Ibrido diesel-elettrico',       'label_en' => 'Diesel-electric hybrid',        'sort_order' => 6],
            ['code' => '07', 'label_fr' => 'Hydrogène',                   'label_de' => 'Wasserstoff',                 'label_it' => 'Idrogeno',                     'label_en' => 'Hydrogen',                      'sort_order' => 7],
            ['code' => '08', 'label_fr' => 'GPL',                         'label_de' => 'Flüssiggas (LPG)',            'label_it' => 'GPL',                          'label_en' => 'LPG',                           'sort_order' => 8],
            ['code' => '09', 'label_fr' => 'Éthanol (E85)',               'label_de' => 'Ethanol (E85)',               'label_it' => 'Etanolo (E85)',                 'label_en' => 'Ethanol (E85)',                  'sort_order' => 9],
            ['code' => '10', 'label_fr' => 'Hybride rechargeable essence','label_de' => 'Plug-in-Hybrid Benzin',       'label_it' => 'Plug-in ibrido benzina',        'label_en' => 'Plug-in hybrid (petrol)',        'sort_order' => 10],
            ['code' => '11', 'label_fr' => 'Hybride rechargeable diesel', 'label_de' => 'Plug-in-Hybrid Diesel',      'label_it' => 'Plug-in ibrido diesel',         'label_en' => 'Plug-in hybrid (diesel)',        'sort_order' => 11],
            ['code' => '12', 'label_fr' => 'Carburant alternatif',        'label_de' => 'Alternativkraftstoff',        'label_it' => 'Carburante alternativo',        'label_en' => 'Alternative fuel',              'sort_order' => 12],
            ['code' => '13', 'label_fr' => 'Hybride (autre)',             'label_de' => 'Hybrid (sonstig)',            'label_it' => 'Ibrido (altro)',                'label_en' => 'Hybrid (other)',                 'sort_order' => 13],
            ['code' => '14', 'label_fr' => 'Hybride rechargeable',        'label_de' => 'Plug-in-Hybrid',              'label_it' => 'Plug-in ibrido',               'label_en' => 'Plug-in hybrid',                'sort_order' => 14],
            ['code' => '15', 'label_fr' => 'Pile à combustible',          'label_de' => 'Brennstoffzelle',             'label_it' => 'Cella a combustibile',          'label_en' => 'Fuel cell',                     'sort_order' => 15],

            // Letter codes (newer RT format or alternative notation)
            ['code' => 'B',  'label_fr' => 'Essence',                    'label_de' => 'Benzin',                      'label_it' => 'Benzina',                      'label_en' => 'Petrol',                        'sort_order' => 20],
            ['code' => 'C',  'label_fr' => 'Gaz naturel (CNG)',           'label_de' => 'Erdgas (CNG)',                'label_it' => 'Gas naturale (CNG)',            'label_en' => 'Natural gas (CNG)',              'sort_order' => 21],
            ['code' => 'D',  'label_fr' => 'Diesel',                     'label_de' => 'Diesel',                      'label_it' => 'Diesel',                       'label_en' => 'Diesel',                        'sort_order' => 22],
            ['code' => 'E',  'label_fr' => 'Électrique',                  'label_de' => 'Elektrisch',                  'label_it' => 'Elettrico',                    'label_en' => 'Electric',                      'sort_order' => 23],
            ['code' => 'G',  'label_fr' => 'GPL',                         'label_de' => 'Flüssiggas (LPG)',            'label_it' => 'GPL',                          'label_en' => 'LPG',                           'sort_order' => 24],
            ['code' => 'H',  'label_fr' => 'Hybride',                    'label_de' => 'Hybrid',                      'label_it' => 'Ibrido',                       'label_en' => 'Hybrid',                        'sort_order' => 25],
        ];

        foreach ($rows as $row) {
            VehicleTranslation::updateOrCreate(
                ['category' => 'energie', 'code' => $row['code']],
                array_merge($row, ['category' => 'energie'])
            );
        }
    }

    // ── Boîte de vitesses / Getriebe ─────────────────────────────────────────

    private function seedBoiteVitesse(): void
    {
        $rows = [
            // ── Principales ───────────────────────────────────────────────────
            ['code' => 'M',   'label_fr' => 'Manuelle',             'label_de' => 'Manuell',           'label_it' => 'Manuale',              'label_en' => 'Manual',              'sort_order' => 1],
            ['code' => 'A',   'label_fr' => 'Automatique',          'label_de' => 'Automatik',         'label_it' => 'Automatica',           'label_en' => 'Automatic',           'sort_order' => 2],
            ['code' => 'DSG', 'label_fr' => 'Boîte DSG',            'label_de' => 'DSG-Getriebe',      'label_it' => 'Cambio DSG',           'label_en' => 'DSG (dual-clutch)',    'sort_order' => 3],
            ['code' => 's',   'label_fr' => 'Séquentielle',         'label_de' => 'Sequenziell',       'label_it' => 'Sequenziale',          'label_en' => 'Sequential',          'sort_order' => 4],
            ['code' => 'sm',  'label_fr' => 'Semi-manuelle',        'label_de' => 'Halbmanuell',       'label_it' => 'Semi-manuale',         'label_en' => 'Semi-manual',         'sort_order' => 5],
            ['code' => 'hsm', 'label_fr' => 'Semi-manuelle hydraulique', 'label_de' => 'Hydromechanisch', 'label_it' => 'Semi-manuale idraulica', 'label_en' => 'Hydrostatic semi-manual', 'sort_order' => 6],

            // ── Manuelles par nombre de vitesses ─────────────────────────────
            ['code' => 'm1',  'label_fr' => 'Manuelle 1 vitesse',   'label_de' => 'Manuell 1 Gang',    'label_it' => 'Manuale 1 marcia',     'label_en' => 'Manual 1-speed',      'sort_order' => 11],
            ['code' => 'm1a', 'label_fr' => 'Manuelle 1 vitesse',   'label_de' => 'Manuell 1 Gang',    'label_it' => 'Manuale 1 marcia',     'label_en' => 'Manual 1-speed',      'sort_order' => 12],
            ['code' => 'm2',  'label_fr' => 'Manuelle 2 vitesses',  'label_de' => 'Manuell 2 Gänge',   'label_it' => 'Manuale 2 marce',      'label_en' => 'Manual 2-speed',      'sort_order' => 13],
            ['code' => 'm2a', 'label_fr' => 'Manuelle 2 vitesses',  'label_de' => 'Manuell 2 Gänge',   'label_it' => 'Manuale 2 marce',      'label_en' => 'Manual 2-speed',      'sort_order' => 14],
            ['code' => 'm3',  'label_fr' => 'Manuelle 3 vitesses',  'label_de' => 'Manuell 3 Gänge',   'label_it' => 'Manuale 3 marce',      'label_en' => 'Manual 3-speed',      'sort_order' => 15],
            ['code' => 'm4',  'label_fr' => 'Manuelle 4 vitesses',  'label_de' => 'Manuell 4 Gänge',   'label_it' => 'Manuale 4 marce',      'label_en' => 'Manual 4-speed',      'sort_order' => 16],
            ['code' => 'm5',  'label_fr' => 'Manuelle 5 vitesses',  'label_de' => 'Manuell 5 Gänge',   'label_it' => 'Manuale 5 marce',      'label_en' => 'Manual 5-speed',      'sort_order' => 17],
            ['code' => 'm5s', 'label_fr' => 'Manuelle 5 vitesses séquentielle', 'label_de' => 'Manuell 5 Gänge sequenziell', 'label_it' => 'Manuale 5 marce sequenziale', 'label_en' => 'Manual 5-speed sequential', 'sort_order' => 18],
            ['code' => 'm6',  'label_fr' => 'Manuelle 6 vitesses',  'label_de' => 'Manuell 6 Gänge',   'label_it' => 'Manuale 6 marce',      'label_en' => 'Manual 6-speed',      'sort_order' => 19],
            ['code' => 'm6a', 'label_fr' => 'Manuelle 6 vitesses',  'label_de' => 'Manuell 6 Gänge',   'label_it' => 'Manuale 6 marce',      'label_en' => 'Manual 6-speed',      'sort_order' => 20],
            ['code' => 'm6s', 'label_fr' => 'Manuelle 6 vitesses séquentielle', 'label_de' => 'Manuell 6 Gänge sequenziell', 'label_it' => 'Manuale 6 marce sequenziale', 'label_en' => 'Manual 6-speed sequential', 'sort_order' => 21],
            ['code' => 'm7a', 'label_fr' => 'Manuelle 7 vitesses',  'label_de' => 'Manuell 7 Gänge',   'label_it' => 'Manuale 7 marce',      'label_en' => 'Manual 7-speed',      'sort_order' => 22],
            ['code' => 'm8',  'label_fr' => 'Manuelle 8 vitesses',  'label_de' => 'Manuell 8 Gänge',   'label_it' => 'Manuale 8 marce',      'label_en' => 'Manual 8-speed',      'sort_order' => 23],
            ['code' => 'm9',  'label_fr' => 'Manuelle 9 vitesses',  'label_de' => 'Manuell 9 Gänge',   'label_it' => 'Manuale 9 marce',      'label_en' => 'Manual 9-speed',      'sort_order' => 24],
            ['code' => 'm10', 'label_fr' => 'Manuelle 10 vitesses', 'label_de' => 'Manuell 10 Gänge',  'label_it' => 'Manuale 10 marce',     'label_en' => 'Manual 10-speed',     'sort_order' => 25],
            ['code' => 'm11', 'label_fr' => 'Manuelle 11 vitesses', 'label_de' => 'Manuell 11 Gänge',  'label_it' => 'Manuale 11 marce',     'label_en' => 'Manual 11-speed',     'sort_order' => 26],
            ['code' => 'm14', 'label_fr' => 'Manuelle 14 vitesses', 'label_de' => 'Manuell 14 Gänge',  'label_it' => 'Manuale 14 marce',     'label_en' => 'Manual 14-speed',     'sort_order' => 27],
            ['code' => 'm?',  'label_fr' => 'Manuelle (n.d.)',      'label_de' => 'Manuell (unbekannt)', 'label_it' => 'Manuale (n.d.)',      'label_en' => 'Manual (unknown)',     'sort_order' => 28],
            ['code' => 'm??', 'label_fr' => 'Manuelle (n.d.)',      'label_de' => 'Manuell (unbekannt)', 'label_it' => 'Manuale (n.d.)',      'label_en' => 'Manual (unknown)',     'sort_order' => 29],

            // ── Automatiques par nombre de rapports ───────────────────────────
            ['code' => 'a2',  'label_fr' => 'Automatique 2 rapports', 'label_de' => 'Automatik 2 Gänge', 'label_it' => 'Automatica 2 rapporti', 'label_en' => 'Automatic 2-speed', 'sort_order' => 31],

            // ── Séquentielles ─────────────────────────────────────────────────
            ['code' => 's1',  'label_fr' => 'Séquentielle 1 rapport', 'label_de' => 'Sequenziell 1 Gang', 'label_it' => 'Sequenziale 1 rapporto', 'label_en' => 'Sequential 1-speed', 'sort_order' => 41],
            ['code' => 's2',  'label_fr' => 'Séquentielle 2 rapports', 'label_de' => 'Sequenziell 2 Gänge', 'label_it' => 'Sequenziale 2 rapporti', 'label_en' => 'Sequential 2-speed', 'sort_order' => 42],
            ['code' => 's2m', 'label_fr' => 'Séquentielle 2 rapports', 'label_de' => 'Sequenziell 2 Gänge', 'label_it' => 'Sequenziale 2 rapporti', 'label_en' => 'Sequential 2-speed', 'sort_order' => 43],
            ['code' => 's3',  'label_fr' => 'Séquentielle 3 rapports', 'label_de' => 'Sequenziell 3 Gänge', 'label_it' => 'Sequenziale 3 rapporti', 'label_en' => 'Sequential 3-speed', 'sort_order' => 44],
            ['code' => 's3m', 'label_fr' => 'Séquentielle 3 rapports', 'label_de' => 'Sequenziell 3 Gänge', 'label_it' => 'Sequenziale 3 rapporti', 'label_en' => 'Sequential 3-speed', 'sort_order' => 45],
        ];

        foreach ($rows as $row) {
            VehicleTranslation::updateOrCreate(
                ['category' => 'boite_vitesse', 'code' => $row['code']],
                array_merge($row, ['category' => 'boite_vitesse'])
            );
        }
    }

    // ── Cache ────────────────────────────────────────────────────────────────

    private function clearCache(): void
    {
        // Clear all vt_* cache keys by flushing the store
        // (targeted key deletion is not possible with file driver without listing)
        try {
            cache()->flush();
        } catch (\Throwable) {
            // Non-critical — cache will expire naturally
        }
    }
}
