<?php

use App\Http\Controllers\Api\V1\ApiKeyController;
use App\Http\Controllers\Api\V1\VehicleApiController;
use Illuminate\Support\Facades\Route;

/**
 * Routes API V1 — reception-par-type.ch
 *
 * Toutes les routes API sont :
 *  - Sous le préfixe /api/v1
 *  - Protégées par auth:sanctum (token Bearer)
 *  - Soumises au middleware api.quota (rate limiting + quota)
 *
 * En-tête d'authentification requis :
 *   Authorization: Bearer rpt_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 *
 * À inclure dans routes/api.php :
 *   require __DIR__ . '/api_v1.php';
 */

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        // ── Endpoints véhicules (protégés par quota) ──────────────────────────
        Route::middleware('api.quota')->group(function () {

            /**
             * GET /api/v1/vehicle/tg/{numero_tg}
             * Fiche technique complète par numéro de réception par type.
             *
             * Plans : Business (6), Business+ (7), Enterprise (8)
             * Exemple : GET /api/v1/vehicle/tg/27.012.000.08.00004
             */
            Route::get('/vehicle/tg/{numero_tg}',
                [VehicleApiController::class, 'getByTg']
            )->name('vehicle.tg')
             ->where('numero_tg', '[\d\.\-\s]{5,25}'); // Contrainte regex Laravel

            /**
             * GET /api/v1/vehicle/tyres/{numero_tg}
             * Données pneumatiques + dimensions alternatives légales ASA Suisse.
             *
             * Plans : Business (6), Business+ (7), Enterprise (8)
             */
            Route::get('/vehicle/tyres/{numero_tg}',
                [VehicleApiController::class, 'getTyres']
            )->name('vehicle.tyres')
             ->where('numero_tg', '[\d\.\-\s]{5,25}');

            /**
             * GET /api/v1/vehicle/wheels/{numero_tg}
             * Spécifications jantes + véhicules à montage compatible.
             *
             * Plans : Business (6), Business+ (7), Enterprise (8)
             */
            Route::get('/vehicle/wheels/{numero_tg}',
                [VehicleApiController::class, 'getWheels']
            )->name('vehicle.wheels')
             ->where('numero_tg', '[\d\.\-\s]{5,25}');

            /**
             * GET /api/v1/vehicle/tax/{numero_tg}?canton=VD
             * Simulateur d'impôt cantonal (plans 7 et 8 uniquement).
             */
            Route::get('/vehicle/tax/{numero_tg}',
                [\App\Http\Controllers\VehicleIntelligenceController::class, 'cantonalTax']
            )->name('vehicle.tax')
             ->where('numero_tg', '[\d\.\-\s]{5,25}');

            /**
             * GET /api/v1/vehicle/homologation/{numero_tg}
             * Génère et renvoie la fiche d'homologation PDF (niveaux 5+).
             * Non facturé si la génération échoue (4xx/5xx exclus du quota).
             */
            Route::get('/vehicle/homologation/{numero_tg}',
                [VehicleApiController::class, 'getHomologationPdf']
            )->name('vehicle.homologation')
             ->where('numero_tg', '[\d\.\-\s]{5,25}');

            /**
             * GET /api/v1/search?q=volkswagen+golf&energie=02
             * Recherche par marque/modèle (plans 7 et 8 uniquement).
             */
            Route::get('/search',
                [\App\Http\Controllers\SearchController::class, 'results']
            )->name('search.query');

        });

        // ── Gestion des clés API (sans quota — opérations de gestion) ─────────
        Route::prefix('keys')->name('keys.')->group(function () {

            /**
             * GET    /api/v1/keys         → Lister les clés de l'utilisateur
             * POST   /api/v1/keys         → Créer une nouvelle clé
             * DELETE /api/v1/keys/{id}    → Révoquer une clé
             * GET    /api/v1/keys/{id}/stats → Statistiques d'une clé
             */
            Route::get('/',          [ApiKeyController::class, 'index'])->name('index');
            Route::post('/',         [ApiKeyController::class, 'store'])->name('store');
            Route::delete('/{keyId}',[ApiKeyController::class, 'revoke'])->name('revoke');
            Route::get('/{keyId}/stats', [ApiKeyController::class, 'stats'])->name('stats');
        });

        // ── Health check (sans auth — pour les monitors uptime) ───────────────
        Route::get('/health', function () {
            return response()->json([
                'success'   => true,
                'status'    => 'operational',
                'version'   => '1.0',
                'timestamp' => now()->toIso8601String(),
            ]);
        })->withoutMiddleware(['auth:sanctum'])->name('health');

    });
