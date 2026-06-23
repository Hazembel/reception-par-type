<?php

namespace App\Providers;

use App\Services\CantonalTaxService;
use App\Services\TyreService;
use App\Services\WheelService;
use Illuminate\Support\ServiceProvider;

/**
 * Provider : VehicleServicesProvider
 *
 * Enregistre les trois services du Module 6 comme singletons dans le
 * conteneur IoC de Laravel. Le pattern Singleton est approprié ici car
 * ces services sont sans état (stateless) entre les requêtes.
 *
 * Enregistrement dans bootstrap/providers.php :
 *   App\Providers\VehicleServicesProvider::class,
 */
class VehicleServicesProvider extends ServiceProvider
{
    public function register(): void
    {
        // TyreService : calculs pneus, stateless → Singleton
        $this->app->singleton(TyreService::class, fn() => new TyreService());

        // WheelService : requêtes BDD cross-véhicule → Singleton
        $this->app->singleton(WheelService::class, fn() => new WheelService());

        // CantonalTaxService : contient la configuration des 12 cantons → Singleton
        // (évite de re-construire la config à chaque requête)
        $this->app->singleton(CantonalTaxService::class, fn() => new CantonalTaxService());
    }

    public function boot(): void
    {
        // Rien à démarrer — services purement calculatoires
    }
}
