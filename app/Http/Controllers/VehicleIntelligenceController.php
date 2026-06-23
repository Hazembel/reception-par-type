<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\CantonalTaxService;
use App\Services\TyreService;
use App\Services\WheelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller : VehicleIntelligenceController
 *
 * Expose les fonctionnalités du Module 6 :
 *  - Alternatives de pneumatiques légales (TyreService)
 *  - Véhicules compatibles jantes (WheelService)
 *  - Simulateur d'impôt cantonal (CantonalTaxService)
 *
 * Toutes les routes retournent du JSON (appelées en AJAX depuis la fiche véhicule).
 */
class VehicleIntelligenceController extends BaseController
{
    public function __construct(
        private readonly TyreService       $tyreService,
        private readonly WheelService      $wheelService,
        private readonly CantonalTaxService $taxService,
    ) {}

    // ── 1. Alternatives de pneumatiques ───────────────────────────────────────

    /**
     * GET /api/v1/vehicles/{vehicle}/tyre-alternatives
     *
     * Retourne la liste des dimensions alternatives légales pour un véhicule.
     * Protégé : niveau 2+ (données avancées requises).
     */
    public function tyreAlternatives(Request $request, Vehicle $vehicle): JsonResponse
    {
        if (empty($vehicle->pneus_origine)) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune donnée de pneumatiques disponible pour ce véhicule.',
                'data'    => [],
            ], 404);
        }

        try {
            // Décodage de la dimension d'origine
            $original = $this->tyreService->decode($vehicle->pneus_origine);

            // Génération des alternatives légales (±8% diamètre, même jante)
            $sameRimOnly = !$request->boolean('allow_rim_change', false);
            $alternatives = $this->tyreService->getLegalAlternatives(
                $vehicle->pneus_origine,
                $sameRimOnly
            );

            return response()->json([
                'success'      => true,
                'original'     => $original,
                'alternatives' => $alternatives,
                'total'        => count($alternatives),
                'tolerance_pct'=> 8,
                'note'         => 'Selon les normes ASA Suisse. Toujours consulter un professionnel agréé.',
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => "Format de pneu non reconnu : {$vehicle->pneus_origine}",
                'raw'     => $vehicle->pneus_origine,
            ], 422);
        }
    }

    // ── 2. Compatibilité de jantes (cross-véhicule) ───────────────────────────

    /**
     * GET /api/v1/vehicles/{vehicle}/wheel-compatibility
     *
     * Retourne les véhicules partageant le même montage de jante.
     */
    public function wheelCompatibility(Request $request, Vehicle $vehicle): JsonResponse
    {
        $result = $this->wheelService->findFromVehicle(
            $vehicle,
            (int) $request->input('limit', 10)
        );

        if (isset($result['error']) && $result['error'] === 'missing_wheel_data') {
            return response()->json([
                'success' => false,
                'message' => 'Données de jante manquantes pour ce véhicule.',
                'data'    => [],
            ], 404);
        }

        return response()->json([
            'success'       => true,
            'pcd'           => $result['pcd'],
            'source_specs'  => $result['source_specs'],
            'compatible'    => $result['compatible'],
            'total'         => $result['compatible']->count(),
            'has_more'      => $result['compatible']->count() === (int) $request->input('limit', 10),
        ]);
    }

    /**
     * POST /api/v1/vehicles/{vehicle}/check-wheel
     *
     * Vérifie si une jante custom est compatible avec un véhicule donné.
     */
    public function checkWheelCompatibility(Request $request, Vehicle $vehicle): JsonResponse
    {
        $validated = $request->validate([
            'nb_trous' => ['required', 'integer', 'min:3', 'max:10'],
            'entraxe'  => ['required', 'integer', 'min:80', 'max:400'],
            'alesage'  => ['required', 'numeric',  'min:50', 'max:120'],
            'deport'   => ['required', 'integer', 'min:-60', 'max:100'],
        ]);

        $result = $this->wheelService->checkWheelCompatibility(
            $vehicle,
            $validated['nb_trous'],
            $validated['entraxe'],
            (int) $validated['alesage'],
            $validated['deport']
        );

        return response()->json([
            'success' => true,
            'vehicle' => "{$vehicle->marque} {$vehicle->modele}",
            'result'  => $result,
        ]);
    }

    // ── 3. Simulateur d'impôt cantonal ────────────────────────────────────────

    /**
     * GET /api/v1/vehicles/{vehicle}/cantonal-tax?canton=VD
     *
     * Calcule l'impôt annuel estimé pour le canton demandé.
     */
    public function cantonalTax(Request $request, Vehicle $vehicle): JsonResponse
    {
        $canton = strtoupper(trim($request->input('canton', '')));

        if (empty($canton)) {
            // Retourner tous les cantons si aucun n'est spécifié
            $all = $this->taxService->calculateAll($this->vehicleDataArray($vehicle));
            return response()->json([
                'success' => true,
                'vehicle' => "{$vehicle->marque} {$vehicle->modele}",
                'results' => $all,
                'disclaimer' => 'Estimation indicative. Consultez le service cantonal officiel.',
            ]);
        }

        try {
            $result = $this->taxService->calculate(
                $canton,
                $this->vehicleDataArray($vehicle)
            );

            return response()->json([
                'success'    => true,
                'vehicle'    => "{$vehicle->marque} {$vehicle->modele}",
                'tax'        => $result,
                'disclaimer' => 'Estimation indicative. Consultez le service cantonal officiel.',
            ]);

        } catch (\InvalidArgumentException $e) {
            $supported = $this->taxService->getSupportedCantons();
            return response()->json([
                'success'    => false,
                'message'    => $e->getMessage(),
                'supported'  => $supported,
            ], 422);
        }
    }

    /**
     * GET /api/v1/cantonal-tax/compare
     *
     * Compare l'impôt pour tous les cantons sans passer par un véhicule.
     * Paramètres : poids_vide, puissance_kw, co2, cylindree, energie
     */
    public function cantonalTaxCompare(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'poids_vide'  => ['nullable', 'integer', 'min:500', 'max:5000'],
            'puissance_kw'=> ['nullable', 'integer', 'min:1', 'max:2000'],
            'co2'         => ['nullable', 'integer', 'min:0', 'max:600'],
            'cylindree'   => ['nullable', 'integer', 'min:0', 'max:10000'],
            'energie'     => ['nullable', 'string', 'max:5'],
        ]);

        $results = $this->taxService->calculateAll($validated);

        return response()->json([
            'success'    => true,
            'results'    => $results,
            'disclaimer' => 'Estimation indicative uniquement.',
        ]);
    }

    // ── Helper privé ──────────────────────────────────────────────────────────

    /**
     * Convertit un modèle Vehicle en tableau de données pour CantonalTaxService.
     */
    private function vehicleDataArray(Vehicle $vehicle): array
    {
        return [
            'poids_vide'    => $vehicle->poids_vide,
            'puissance_kw'  => $vehicle->puissance_kw,
            'cylindree'     => $vehicle->cylindree,
            'co2'           => $vehicle->co2,
            'energie'       => $vehicle->energie,
            'code_emissions'=> $vehicle->code_emissions,
            // Type de véhicule : permet aux barèmes cantonaux de différencier
            // voitures et motos (les motos sont souvent taxées à la cylindrée).
            'vehicle_type'  => $vehicle->vehicle_type,
        ];
    }
}
