<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder principal : DatabaseSeeder
 *
 * Appelé par `php artisan db:seed` ou `php artisan migrate --seed`.
 * Exécute tous les seeders dans l'ordre de dépendance.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PricingPlanSeeder::class,   // 1. Plans tarifaires (8 niveaux) — aucune dépendance
            AdminUserSeeder::class,     // 2. Compte admin (dépend de PricingPlan pour valider le niveau 8)
        ]);

        // Seeders optionnels (données de test — développement uniquement)
        if (app()->isLocal()) {
            // $this->call(VehicleTestSeeder::class);
        }
    }
}
