<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Service : WheelService
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Intelligence de compatibilité des jantes entre véhicules.
 *
 * Responsabilités :
 *  1. Trouver les véhicules partageant le même montage de jante (cross-vehicle)
 *  2. Vérifier la compatibilité complète d'une jante avec un véhicule
 *  3. Calculer les tolérances de déport ET entre deux véhicules
 *
 * Données utilisées (table `vehicles`) :
 *   - nb_trous   : Nombre de trous de fixation
 *   - entraxe    : Pitch Circle Diameter en mm (PCD)
 *   - alesage    : Alésage du moyeu central en mm
 *   - deport_et  : Offset ET en mm
 * ─────────────────────────────────────────────────────────────────────────────
 */
class WheelService
{
    /**
     * Tolérance de déport ET entre véhicules différents (mm).
     * Une jante avec ET hors de cette plage peut nécessiter des entretoises.
     */
    private const ET_TOLERANCE_MM = 10;

    /**
     * TTL du cache pour les résultats de compatibilité (secondes).
     * Les données de jante changent rarement — cache 24h.
     */
    private const CACHE_TTL = 86_400; // 24 heures

    // ── Recherche de véhicules compatibles ────────────────────────────────────

    /**
     * Trouve les véhicules qui partagent EXACTEMENT les mêmes spécifications
     * de jante (nb_trous + entraxe + alésage).
     *
     * Utilisation typique : "Quelles autres voitures peuvent monter mes jantes VW ?"
     *
     * @param  int      $nbTrous    Nombre de trous de fixation
     * @param  int      $entraxe    PCD en mm (ex: 112)
     * @param  int|null $alesage    Alésage du moyeu en mm (ex: 57)
     * @param  int|null $deport     Déport ET de référence (pour calcul de compatibilité)
     * @param  string|null $excludeVehicleId  ULID du véhicule source à exclure
     * @param  int      $limit      Nombre maximum de résultats
     * @return Collection<int, array>
     */
    public function findCompatibleVehicles(
        int     $nbTrous,
        int     $entraxe,
        ?int    $alesage            = null,
        ?int    $deport             = null,
        ?string $excludeVehicleId   = null,
        int     $limit              = 10
    ): Collection {
        $cacheKey = "wheel_compat:{$nbTrous}:{$entraxe}:" . ($alesage ?? 'any') . ":{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use (
            $nbTrous, $entraxe, $alesage, $deport, $excludeVehicleId, $limit
        ) {
            $query = Vehicle::active()
                ->select([
                    'id', 'marque', 'modele', 'variante', 'slug',
                    'nb_trous', 'entraxe', 'alesage', 'deport_et', 'pneus_origine',
                ])
                ->where('nb_trous', $nbTrous)
                ->where('entraxe', $entraxe)
                ->whereNotNull('entraxe')
                ->whereNotNull('nb_trous');

            // Filtre sur l'alésage si fourni (correspondance exacte ou supérieure)
            // Note : un alésage de jante SUPÉRIEUR au moyeu est acceptable
            //        avec une bague de centrage — mais on ne filtre que l'exact ici.
            if ($alesage !== null) {
                $query->where('alesage', $alesage);
            }

            // Exclusion du véhicule source
            if ($excludeVehicleId !== null) {
                $query->where('id', '!=', $excludeVehicleId);
            }

            $vehicles = $query
                ->distinct('marque')
                ->orderBy('marque')
                ->orderBy('modele')
                ->limit($limit * 3) // Sur-fetch pour dédupliquer par marque ensuite
                ->get();

            // Enrichissement avec les données de compatibilité ET
            return $vehicles
                ->map(function (Vehicle $v) use ($deport, $nbTrous, $entraxe) {
                    $etCompat = $deport !== null
                        ? $this->assessEtCompatibility((int) $v->deport_et, $deport)
                        : null;

                    return [
                        'id'           => $v->id,
                        'marque'       => $v->marque,
                        'modele'       => $v->modele,
                        'variante'     => $v->variante,
                        'slug'         => $v->slug,
                        'nb_trous'     => $v->nb_trous,
                        'entraxe'      => $v->entraxe,
                        'alesage'      => $v->alesage,
                        'deport_et'    => $v->deport_et,
                        'pneus_origine'=> $v->pneus_origine,
                        'et_compat'    => $etCompat,
                        'pcd_label'    => "{$nbTrous}×{$entraxe}",
                    ];
                })
                ->unique(fn($v) => $v['marque'] . '|' . $v['modele']) // 1 entrée par modèle
                ->take($limit)
                ->values();
        });
    }

    /**
     * Version simplifiée : compatible à partir d'un modèle Vehicle Eloquent.
     * Méthode principale appelée depuis le VehicleController.
     *
     * @param  Vehicle $vehicle  Le véhicule de référence
     * @param  int     $limit    Nombre de résultats
     * @return array{
     *   compatible: Collection,
     *   pcd: string,
     *   has_results: bool,
     *   source_specs: array,
     * }
     */
    public function findFromVehicle(Vehicle $vehicle, int $limit = 10): array
    {
        // Vérification que les données de jante sont disponibles
        if (empty($vehicle->nb_trous) || empty($vehicle->entraxe)) {
            return [
                'compatible'  => collect(),
                'pcd'         => '—',
                'has_results' => false,
                'source_specs'=> [],
                'error'       => 'missing_wheel_data',
            ];
        }

        $compatible = $this->findCompatibleVehicles(
            nbTrous:          (int) $vehicle->nb_trous,
            entraxe:          (int) $vehicle->entraxe,
            alesage:          $vehicle->alesage ? (int) $vehicle->alesage : null,
            deport:           $vehicle->deport_et !== null ? (int) $vehicle->deport_et : null,
            excludeVehicleId: $vehicle->id,
            limit:            $limit
        );

        return [
            'compatible'   => $compatible,
            'pcd'          => "{$vehicle->nb_trous}×{$vehicle->entraxe}",
            'has_results'  => $compatible->isNotEmpty(),
            'source_specs' => [
                'nb_trous'  => $vehicle->nb_trous,
                'entraxe'   => $vehicle->entraxe,
                'alesage'   => $vehicle->alesage,
                'deport_et' => $vehicle->deport_et,
            ],
        ];
    }

    // ── Évaluation de la compatibilité ET ─────────────────────────────────────

    /**
     * Évalue la compatibilité du déport ET entre un véhicule cible et un de référence.
     *
     * Règles :
     *  - Écart ≤ 5 mm  → Parfait (aucune adaptation nécessaire)
     *  - Écart 5–10 mm → Acceptable (vérifier l'homologation cantonale)
     *  - Écart > 10 mm → Entretoise requise ou non compatible
     *
     * @param  int $targetEt     ET du véhicule cible
     * @param  int $referenceEt  ET du véhicule de référence
     * @return array{
     *   compatible: bool,
     *   level: string,
     *   diff_mm: int,
     *   note: string,
     * }
     */
    public function assessEtCompatibility(int $targetEt, int $referenceEt): array
    {
        $diff = abs($targetEt - $referenceEt);

        if ($diff <= 5) {
            return [
                'compatible' => true,
                'level'      => 'perfect',
                'diff_mm'    => $diff,
                'note'       => "ET identique ou quasi-identique (±{$diff} mm) — compatible direct",
                'color'      => 'green',
            ];
        }

        if ($diff <= self::ET_TOLERANCE_MM) {
            return [
                'compatible' => true,
                'level'      => 'acceptable',
                'diff_mm'    => $diff,
                'note'       => "Écart ET de {$diff} mm — vérifier l'homologation MFK cantonale",
                'color'      => 'amber',
            ];
        }

        return [
            'compatible' => false,
            'level'      => 'incompatible',
            'diff_mm'    => $diff,
            'note'       => "Écart ET de {$diff} mm — entretoise ou jante adaptée requise",
            'color'      => 'red',
        ];
    }

    /**
     * Vérifie si une jante custom est compatible avec un véhicule.
     *
     * @param  Vehicle $vehicle    Véhicule cible
     * @param  int     $nbTrous    Trous de la jante custom
     * @param  int     $entraxe    PCD de la jante custom
     * @param  int     $alesage    Alésage de la jante custom
     * @param  int     $deport     ET de la jante custom
     * @return array{
     *   is_compatible: bool,
     *   checks: array,
     *   warnings: array,
     *   summary: string,
     * }
     */
    public function checkWheelCompatibility(
        Vehicle $vehicle,
        int     $nbTrous,
        int     $entraxe,
        int     $alesage,
        int     $deport
    ): array {
        $checks   = [];
        $warnings = [];
        $errors   = [];

        // ── Check 1 : Nombre de trous ─────────────────────────────────────────
        $checkTrous = $nbTrous === (int) $vehicle->nb_trous;
        $checks['nb_trous'] = [
            'label'   => 'Nombre de trous',
            'vehicle' => $vehicle->nb_trous,
            'wheel'   => $nbTrous,
            'pass'    => $checkTrous,
            'critical'=> true,
            'note'    => $checkTrous
                ? 'Identique ✓'
                : "Incompatible — {$vehicle->nb_trous} trous requis, {$nbTrous} sur la jante",
        ];
        if (!$checkTrous) {
            $errors[] = 'Nombre de trous incompatible (bloquant)';
        }

        // ── Check 2 : Entraxe PCD ─────────────────────────────────────────────
        $checkEntraxe = $entraxe === (int) $vehicle->entraxe;
        $checks['entraxe'] = [
            'label'   => 'Entraxe PCD',
            'vehicle' => $vehicle->entraxe . ' mm',
            'wheel'   => $entraxe . ' mm',
            'pass'    => $checkEntraxe,
            'critical'=> true,
            'note'    => $checkEntraxe
                ? 'Identique ✓'
                : "Incompatible — PCD {$vehicle->entraxe} mm requis",
        ];
        if (!$checkEntraxe) {
            $errors[] = 'Entraxe incompatible (bloquant)';
        }

        // ── Check 3 : Alésage du moyeu ────────────────────────────────────────
        if ($vehicle->alesage) {
            $vehicleAlesage = (int) $vehicle->alesage;
            // L'alésage de la jante doit être >= alésage du moyeu
            // (bague de centrage possible si jante > moyeu, pas l'inverse)
            $checkAlesage = $alesage >= $vehicleAlesage;
            $checks['alesage'] = [
                'label'   => 'Alésage central',
                'vehicle' => $vehicleAlesage . ' mm',
                'wheel'   => $alesage . ' mm',
                'pass'    => $checkAlesage,
                'critical'=> false,
                'note'    => $alesage === $vehicleAlesage
                    ? 'Identique ✓'
                    : ($checkAlesage
                        ? "Bague de centrage requise ({$alesage} → {$vehicleAlesage} mm)"
                        : "Trop petit — alésage {$vehicleAlesage} mm minimum requis"),
            ];
            if (!$checkAlesage) {
                $errors[] = 'Alésage trop petit (bloquant)';
            } elseif ($alesage > $vehicleAlesage) {
                $warnings[] = 'Bague de centrage recommandée';
            }
        }

        // ── Check 4 : Déport ET ───────────────────────────────────────────────
        if ($vehicle->deport_et !== null) {
            $etAssess = $this->assessEtCompatibility($deport, (int) $vehicle->deport_et);
            $checks['deport_et'] = [
                'label'    => 'Déport ET',
                'vehicle'  => $vehicle->deport_et . ' mm',
                'wheel'    => $deport . ' mm',
                'pass'     => $etAssess['compatible'],
                'critical' => false,
                'note'     => $etAssess['note'],
            ];
            if (!$etAssess['compatible']) {
                $warnings[] = $etAssess['note'];
            } elseif ($etAssess['level'] === 'acceptable') {
                $warnings[] = 'Vérifier homologation MFK cantonale pour le déport ET';
            }
        }

        $isCompatible = empty($errors);

        // Résumé lisible
        $summary = $isCompatible
            ? ($warnings
                ? 'Compatible avec réserves — ' . implode(', ', $warnings)
                : 'Totalement compatible ✓')
            : 'Incompatible — ' . implode(', ', $errors);

        return [
            'is_compatible' => $isCompatible,
            'checks'        => $checks,
            'warnings'      => $warnings,
            'errors'        => $errors,
            'summary'       => $summary,
        ];
    }
}
