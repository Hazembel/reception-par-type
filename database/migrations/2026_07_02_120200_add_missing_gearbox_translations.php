<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [];
        $now  = now();

        // Automatic gearbox codes: a3–a12
        foreach (range(3, 12) as $n) {
            $rows[] = [
                'category'   => 'boite_vitesse',
                'code'       => "a{$n}",
                'label_fr'   => "Automatique {$n} rapports",
                'label_de'   => "Automatik {$n}-Gang",
                'label_it'   => "Automatico {$n} marce",
                'label_en'   => "Automatic {$n}-speed",
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Manual gearbox codes not yet covered
        foreach ([11, 12, 13, 14, 15, 16] as $n) {
            // m14 already exists in seeder — skip duplicates
            if (DB::table('vehicle_translations')->where('category', 'boite_vitesse')->where('code', "m{$n}")->exists()) {
                continue;
            }
            $rows[] = [
                'category'   => 'boite_vitesse',
                'code'       => "m{$n}",
                'label_fr'   => "Manuelle {$n} vitesses",
                'label_de'   => "Manuell {$n}-Gang",
                'label_it'   => "Manuale {$n} marce",
                'label_en'   => "Manual {$n}-speed",
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Automated-manual variants (suffix 'a' = automatisée)
        $amtCodes = [
            'm?a'  => ['fr' => 'Manuelle automatisée (n.d.)',  'de' => 'Automatisiertes Schaltgetriebe (n.b.)', 'it' => 'Manuale automatizzato (n.d.)', 'en' => 'Automated manual (n/a)'],
            'm??a' => ['fr' => 'Manuelle automatisée (n.d.)',  'de' => 'Automatisiertes Schaltgetriebe (n.b.)', 'it' => 'Manuale automatizzato (n.d.)', 'en' => 'Automated manual (n/a)'],
        ];
        foreach ($amtCodes as $code => $labels) {
            if (DB::table('vehicle_translations')->where('category', 'boite_vitesse')->where('code', $code)->exists()) {
                continue;
            }
            $rows[] = [
                'category'   => 'boite_vitesse',
                'code'       => $code,
                'label_fr'   => $labels['fr'],
                'label_de'   => $labels['de'],
                'label_it'   => $labels['it'],
                'label_en'   => $labels['en'],
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($rows)) {
            DB::table('vehicle_translations')->insert($rows);
        }
    }

    public function down(): void
    {
        $codes = array_merge(
            array_map(fn($n) => "a{$n}", range(3, 12)),
            array_map(fn($n) => "m{$n}", [11, 12, 13, 15, 16]),
            ['m?a', 'm??a']
        );
        DB::table('vehicle_translations')
            ->where('category', 'boite_vitesse')
            ->whereIn('code', $codes)
            ->delete();
    }
};
