<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Services\CantonalTaxService;
use App\Services\TgNormalizerService;
use App\Services\TyreService;
use App\Services\VehicleFieldFilterService;
use App\Services\WheelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Controller : Api\V1\VehicleApiController
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Endpoints REST JSON pour l'API B2B payante.
 *
 * PRINCIPES DE SÉCURITÉ appliqués à chaque réponse :
 *  1. Filtrage strict des champs via config/api_plans.php field_sets
 *  2. Jamais d'ID interne (ULID), jamais de slug, jamais de timestamps BDD
 *  3. Format ISO 8601 pour les dates (pas de timestamps UNIX)
 *  4. Pas de stack trace en production (géré par le Handler)
 *  5. Enveloppe JSON uniforme : { success, data, meta, error? }
 *  6. Numéro TG validé par regex avant toute requête BDD
 *
 * Tous les endpoints :
 *  - Requièrent auth:sanctum + middleware api.quota
 *  - Retournent du JSON avec Content-Type: application/json
 *  - Supportent le cache optionnel (header X-Cache)
 *  - Incluent les headers X-RateLimit-* et X-Quota-* (injectés par le middleware)
 * ─────────────────────────────────────────────────────────────────────────────
 */
class VehicleApiController extends Controller
{
    /**
     * TTL du cache des fiches véhicules (en secondes).
     * Les données ASTRA changent mensuellement → cache 12h.
     */
    private const CACHE_TTL = 43_200; // 12 heures

    /**
     * Regex de validation du numéro TG (même règle que SearchRequest).
     * Protège contre les injections et les requêtes malformées.
     */
    private const REGEX_TG = '/^[\d\.\-\s]{5,25}$/';

    public function __construct(
        private readonly TyreService        $tyreService,
        private readonly WheelService       $wheelService,
        private readonly CantonalTaxService $taxService,
    ) {}

    // ── GET /api/v1/vehicle/tg/{numero_tg} ───────────────────────────────────

    /**
     * Retourne la fiche technique complète d'un véhicule par son numéro TG.
     *
     * Réponse 200 :
     * {
     *   "success": true,
     *   "data": { "numero_tg": "27.012.000.08.00004", "marque": "Volkswagen", ... },
     *   "meta": { "plan": "Business API", "cached": true, "version": "1.0" }
     * }
     */
    public function getByTg(Request $request, string $numero_tg): JsonResponse
    {
        // ── Validation du numéro TG ───────────────────────────────────────────
        if (!$this->isValidTg($numero_tg)) {
            return $this->errorJson(
                'Invalid type approval number format. Expected: XX.XXX.XXX.XX.XXXXX',
                400
            );
        }

        $plan       = $request->attributes->get('api_plan', []);
        $fieldSet   = $plan['fields'] ?? 'standard';
        $cacheKey   = "api:tg:{$numero_tg}:{$fieldSet}";

        // ── Recherche en cache puis BDD ───────────────────────────────────────
        $cached  = true;
        $vehicle = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($numero_tg, &$cached) {
            $cached = false;
            return Vehicle::active()
                ->where('numero_tg', TgNormalizerService::normalize($numero_tg))
                ->first();
        });

        if (!$vehicle) {
            return $this->errorJson(
                "No vehicle found with type approval number: {$numero_tg}",
                404
            );
        }

        // ── Filtrage strict des champs ─────────────────────────────────────────
        $data = VehicleFieldFilterService::applyApiPlan($vehicle->toArray(), $fieldSet);

        // Enrichissement : puissance en CV si jeu "full"
        if ($fieldSet === 'full' && isset($data['puissance_kw'])) {
            $data['puissance_cv'] = $vehicle->puissance_cv;
        }

        return $this->successJson($data, $cached, $request);
    }

    // ── GET /api/v1/vehicle/tyres/{numero_tg} ─────────────────────────────────

    /**
     * Retourne les données de pneumatiques et les dimensions alternatives légales.
     *
     * Réponse 200 :
     * {
     *   "success": true,
     *   "data": {
     *     "numero_tg": "...",
     *     "pneus_origine": "205/55R16 91V",
     *     "decoded": { "width": 205, "series": 55, "rim_diameter": 16, "diameter_mm": 631.9, ... },
     *     "legal_alternatives": [ { "formatted": "215/50R16", "deviation_pct": -0.8, ... } ]
     *   }
     * }
     */
    public function getTyres(Request $request, string $numero_tg): JsonResponse
    {
        if (!$this->isValidTg($numero_tg)) {
            return $this->errorJson('Invalid type approval number format.', 400);
        }

        $vehicle = Vehicle::active()
            ->where('numero_tg', TgNormalizerService::normalize($numero_tg))
            ->select(['numero_tg', 'marque', 'modele', 'nb_trous', 'entraxe', 'alesage', 'deport_et', 'pneus_origine'])
            ->first();

        if (!$vehicle) {
            return $this->errorJson("No vehicle found: {$numero_tg}", 404);
        }

        if (empty($vehicle->pneus_origine)) {
            return $this->errorJson('Tyre data not available for this vehicle.', 404);
        }

        $decoded      = null;
        $alternatives = [];
        $tyreError    = null;

        try {
            $decoded      = $this->tyreService->decode($vehicle->pneus_origine);
            $alternatives = $this->tyreService->getLegalAlternatives($vehicle->pneus_origine);
        } catch (\InvalidArgumentException $e) {
            $tyreError = $e->getMessage();
        }

        $data = [
            'numero_tg'          => $vehicle->numero_tg,
            'marque'             => $vehicle->marque,
            'modele'             => $vehicle->modele,
            'pneus_origine'      => $vehicle->pneus_origine,
            'wheel_specs' => [
                'nb_trous'  => $vehicle->nb_trous,
                'entraxe_mm'=> $vehicle->entraxe,
                'alesage_mm'=> $vehicle->alesage,
                'offset_et' => $vehicle->deport_et,
                'pcd_label' => $vehicle->nb_trous && $vehicle->entraxe
                    ? "{$vehicle->nb_trous}×{$vehicle->entraxe}"
                    : null,
            ],
        ];

        if ($decoded) {
            // Nettoyage : on n'expose pas le champ 'raw' (redondant) ni 'formatted' (calculé)
            $data['tyre_decoded'] = array_diff_key($decoded, array_flip(['raw', 'formatted']));
            $data['legal_alternatives'] = array_map(function ($alt) {
                // Seulement les champs utiles pour le client API
                return [
                    'dimension'      => $alt['formatted'],
                    'diameter_mm'    => $alt['diameter_mm'],
                    'deviation_pct'  => $alt['deviation_pct'],
                    'sidewall_mm'    => $alt['sidewall_mm'],
                    'is_legal'       => $alt['is_legal'],
                    'legal_note'     => $alt['legal_note'],
                    'speedometer'    => $alt['speedometer'],
                ];
            }, $alternatives);
            $data['alternatives_count']    = count($alternatives);
            $data['tolerance_pct']         = 8;
            $data['legal_basis']           = 'ASA Switzerland ±8% outer diameter';
        } elseif ($tyreError) {
            $data['tyre_parse_error'] = $tyreError;
        }

        return $this->successJson($data, false, $request);
    }

    // ── GET /api/v1/vehicle/wheels/{numero_tg} ────────────────────────────────

    /**
     * Retourne les données de jantes et les véhicules avec montage compatible.
     *
     * Réponse 200 :
     * {
     *   "success": true,
     *   "data": {
     *     "numero_tg": "...",
     *     "wheel_specs": { "nb_trous": 5, "entraxe_mm": 112, ... },
     *     "compatible_vehicles": [ { "make": "Audi", "model": "A3", ... } ]
     *   }
     * }
     */
    public function getWheels(Request $request, string $numero_tg): JsonResponse
    {
        if (!$this->isValidTg($numero_tg)) {
            return $this->errorJson('Invalid type approval number format.', 400);
        }

        $vehicle = Vehicle::active()
            ->where('numero_tg', TgNormalizerService::normalize($numero_tg))
            ->select(['id', 'numero_tg', 'marque', 'modele', 'variante',
                      'nb_trous', 'entraxe', 'alesage', 'deport_et', 'pneus_origine'])
            ->first();

        if (!$vehicle) {
            return $this->errorJson("No vehicle found: {$numero_tg}", 404);
        }

        if (!$vehicle->nb_trous || !$vehicle->entraxe) {
            return $this->errorJson('Wheel data not available for this vehicle.', 404);
        }

        // Compatibilité cross-véhicule
        $compatResult   = $this->wheelService->findFromVehicle($vehicle, 10);
        $compatible     = $compatResult['compatible'];

        $data = [
            'numero_tg'  => $vehicle->numero_tg,
            'marque'     => $vehicle->marque,
            'modele'     => $vehicle->modele,
            'wheel_specs' => [
                'nb_trous'   => $vehicle->nb_trous,
                'entraxe_mm' => $vehicle->entraxe,
                'alesage_mm' => $vehicle->alesage,
                'offset_et'  => $vehicle->deport_et,
                'pcd_label'  => $compatResult['pcd'],
            ],
            'compatible_vehicles' => $compatible->map(fn($v) => [
                // Exposer uniquement les données publiques — jamais l'ID interne
                'numero_tg'  => null,  // Non disponible depuis WheelService (non chargé)
                'make'       => $v['marque'],
                'model'      => $v['modele'],
                'variant'    => $v['variante'],
                'pcd_label'  => $v['pcd_label'],
                'offset_et'  => $v['deport_et'],
                'et_compat'  => $v['et_compat'],
            ])->values()->all(),
            'compatible_count' => $compatible->count(),
        ];

        return $this->successJson($data, false, $request);
    }

    // ── Helpers d'enveloppe JSON ──────────────────────────────────────────────

    /**
     * Enveloppe JSON unifiée pour les réponses 2xx.
     *
     * Format :
     * {
     *   "success": true,
     *   "data": { ... },
     *   "meta": {
     *     "plan": "Business API",
     *     "cached": false,
     *     "version": "1.0",
     *     "timestamp": "2024-03-15T10:30:00+00:00"
     *   }
     * }
     */
    private function successJson(array $data, bool $cached, Request $request): JsonResponse
    {
        $plan = $request->attributes->get('api_plan', []);

        $response = response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'plan'      => $plan['name'] ?? 'API',
                'cached'    => $cached,
                'version'   => '1.0',
                'timestamp' => now()->toIso8601String(),
                'source'    => 'OFROU/ASTRA TARGA',
                'disclaimer'=> 'Indicative data based on official ASTRA files. No liability for errors.',
            ],
        ], 200, ['Content-Type' => 'application/json; charset=utf-8']);

        // Header de cache pour les proxies clients
        if ($cached) {
            $response->headers->set('X-Cache', 'HIT');
        } else {
            $response->headers->set('X-Cache', 'MISS');
            $response->headers->set('Cache-Control', 'private, max-age=' . self::CACHE_TTL);
        }

        return $response;
    }

    /**
     * Enveloppe JSON unifiée pour les erreurs.
     *
     * Format :
     * {
     *   "success": false,
     *   "error": "NOT_FOUND",
     *   "message": "No vehicle found...",
     *   "timestamp": "...",
     *   "docs": "https://reception-par-type.ch/api/docs"
     * }
     */
    private function errorJson(string $message, int $statusCode): JsonResponse
    {
        $errorCodes = [
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            422 => 'VALIDATION_ERROR',
            429 => 'TOO_MANY_REQUESTS',
            500 => 'INTERNAL_ERROR',
        ];

        return response()->json([
            'success'   => false,
            'error'     => $errorCodes[$statusCode] ?? 'ERROR',
            'message'   => $message,
            'timestamp' => now()->toIso8601String(),
            'docs'      => 'https://reception-par-type.ch/api/docs',
        ], $statusCode, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    // ── Validation du numéro TG ───────────────────────────────────────────────

    private function isValidTg(string $tg): bool
    {
        return (bool) preg_match(self::REGEX_TG, trim($tg));
    }

    // ── Fiche d'homologation PDF ──────────────────────────────────────────────

    /**
     * GET /api/v1/vehicle/homologation/{numero_tg}
     *
     * Génère et retourne la fiche d'homologation PDF du véhicule.
     * Réservé aux clients de niveau 5 et supérieur.
     *
     * FACTURATION : la requête n'est comptée dans le quota mensuel QUE si le
     * PDF est bien généré (réponse 200). Les 403/404/500 ne sont pas facturés —
     * c'est géré par EnsureApiKeyHasQuota via $isBilled = (status >= 200 < 300).
     */
    public function getHomologationPdf(Request $request, string $numero_tg): \Symfony\Component\HttpFoundation\Response
    {
        if (!$this->isValidTg($numero_tg)) {
            return $this->errorJson('Numéro TG invalide.', 422);
        }

        // Niveau 5+ requis pour la génération PDF
        $planLevel = (int) ($request->user()?->currentAccessToken()?->plan_id ?? 1);
        if ($planLevel < 5) {
            return $this->errorJson(
                'La génération de fiche PDF est réservée aux plans niveau 5 et supérieur.',
                403
            );
        }

        $vehicle = \App\Models\Vehicle::where('numero_tg', TgNormalizerService::normalize($numero_tg))
            ->where('is_active', true)
            ->first();

        if (!$vehicle) {
            return $this->errorJson('Véhicule introuvable pour ce numéro TG.', 404);
        }

        try {
            $html = view('invoices.homologation', ['vehicle' => $vehicle])->render();

            $dompdf = new \Dompdf\Dompdf([
                'enable_remote'      => false,
                'default_paper_size' => 'A4',
                'is_html5_parser'    => true,
                'is_php_enabled'     => false,
            ]);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($numero_tg)));
            $filename = "homologation-{$slug}.pdf";

            return response($dompdf->output(), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control'       => 'no-store, no-cache',
            ]);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[API] Échec génération fiche PDF', [
                'numero_tg' => $numero_tg,
                'error'     => $e->getMessage(),
            ]);
            return $this->errorJson('Erreur lors de la génération du PDF.', 500);
        }
    }
}
