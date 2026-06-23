<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Service : CantonalTaxService
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Simulateur d'impôt cantonal sur les véhicules à moteur (Suisse).
 *
 * Chaque canton suisse applique une formule de calcul différente basée sur
 * un ou plusieurs des critères suivants :
 *   - Poids à vide (Leergewicht) en kg
 *   - Puissance moteur en kW ou CV
 *   - Cylindrée en cm³
 *   - Émissions CO₂ en g/km
 *   - Norme d'émissions (Euro)
 *
 * Sources :
 *   - ASTAG (Association suisse des transports routiers)
 *   - Sites officiels des Services des automobiles cantonaux
 *   - Ordonnances cantonales sur les impôts sur les véhicules à moteur
 *
 * ⚠️  DISCLAIMER : Ces formules sont indicatives et peuvent évoluer.
 *     Consulter toujours le service cantonal officiel pour le montant exact.
 * ─────────────────────────────────────────────────────────────────────────────
 */
class CantonalTaxService
{
    /**
     * Base minimale (CHF) pour tous les cantons.
     * Aucun impôt ne peut être inférieur à ce montant.
     */
    private const MINIMUM_TAX_CHF = 20.0;

    /**
     * Données de configuration par canton.
     *
     * Structure par canton :
     * [
     *   'name'     => Nom officiel (langue locale)
     *   'abbr'     => Abréviation officielle OFS
     *   'basis'    => Critère(s) de calcul : 'weight'|'power_kw'|'co2'|'cylinder'|'mixed'
     *   'formula'  => Callable de calcul (voir méthodes privées)
     *   'bands'    => Tranches tarifaires (si applicable)
     *   'bonus'    => Réductions possibles (véhicule électrique, etc.)
     *   'source'   => URL de référence officielle
     * ]
     */
    private array $cantonConfig;

    public function __construct()
    {
        $this->cantonConfig = $this->buildCantonConfig();
    }

    // ── Calcul principal ──────────────────────────────────────────────────────

    /**
     * Calcule l'impôt annuel estimé pour un véhicule dans un canton donné.
     *
     * @param  string   $canton      Code canton (ex: "VD", "ZH", "GE")
     * @param  array{
     *   poids_vide: int|null,
     *   puissance_kw: int|null,
     *   cylindree: int|null,
     *   co2: int|null,
     *   energie: string|null,
     *   code_emissions: string|null,
     * } $vehicleData  Données techniques du véhicule
     * @return array{
     *   canton: string,
     *   canton_name: string,
     *   amount_chf: float,
     *   amount_formatted: string,
     *   basis: string,
     *   breakdown: array,
     *   warnings: array,
     *   is_estimate: bool,
     *   source_url: string,
     * }
     * @throws InvalidArgumentException Si le canton est inconnu
     */
    public function calculate(string $canton, array $vehicleData): array
    {
        $canton = strtoupper(trim($canton));

        if (!isset($this->cantonConfig[$canton])) {
            throw new InvalidArgumentException(
                "Canton inconnu : \"{$canton}\". " .
                "Cantons supportés : " . implode(', ', array_keys($this->cantonConfig))
            );
        }

        $config   = $this->cantonConfig[$canton];
        $warnings = [];

        // Calcul via la méthode spécifique au canton
        $result = ($config['formula'])($vehicleData, $warnings);

        // Application du bonus électrique (si applicable)
        if ($this->isElectric($vehicleData['energie'] ?? null)) {
            $result = $this->applyElectricBonus($canton, $result, $config);
            $warnings[] = 'Véhicule électrique — bonus cantonal appliqué si disponible';
        }

        // Application du minimum légal
        $result['amount'] = max(self::MINIMUM_TAX_CHF, $result['amount']);

        // Arrondi à 5 CHF le plus proche (pratique courante des cantons)
        $rounded = round($result['amount'] / 5) * 5;

        return [
            'canton'           => $canton,
            'canton_name'      => $config['name'],
            'amount_chf'       => $rounded,
            'amount_formatted' => number_format($rounded, 0, '.', '\'') . ' CHF',
            'basis'            => $config['basis'],
            'breakdown'        => $result['breakdown'] ?? [],
            'warnings'         => array_merge($warnings, $result['warnings'] ?? []),
            'is_estimate'      => true, // Toujours une estimation
            'source_url'       => $config['source'],
        ];
    }

    /**
     * Calcule pour tous les cantons en une seule passe.
     * Utile pour l'outil de comparaison inter-cantonale.
     *
     * @param  array $vehicleData
     * @return array<string, array> Résultats indexés par code canton, triés par montant
     */
    public function calculateAll(array $vehicleData): array
    {
        $results = [];

        foreach (array_keys($this->cantonConfig) as $canton) {
            try {
                $results[$canton] = $this->calculate($canton, $vehicleData);
            } catch (\Throwable $e) {
                $results[$canton] = [
                    'canton'           => $canton,
                    'canton_name'      => $this->cantonConfig[$canton]['name'] ?? $canton,
                    'amount_chf'       => null,
                    'amount_formatted' => 'N/D',
                    'error'            => $e->getMessage(),
                ];
            }
        }

        // Tri par montant croissant
        uasort($results, fn($a, $b) => ($a['amount_chf'] ?? PHP_INT_MAX) <=> ($b['amount_chf'] ?? PHP_INT_MAX));

        return $results;
    }

    /**
     * Retourne la liste des cantons supportés avec leur nom.
     *
     * @return array<string, string>  ['ZH' => 'Zürich', 'VD' => 'Vaud', ...]
     */
    public function getSupportedCantons(): array
    {
        return array_map(fn($c) => $c['name'], $this->cantonConfig);
    }

    // ── Formules cantonales ───────────────────────────────────────────────────

    /**
     * Construit la configuration de tous les cantons.
     * Chaque canton a sa propre lambda de calcul.
     */
    private function buildCantonConfig(): array
    {
        return [

            // ── ZH : Zürich — Basé sur le poids (Leergewicht) ────────────────
            'ZH' => [
                'name'   => 'Zürich',
                'abbr'   => 'ZH',
                'basis'  => 'weight',
                'source' => 'https://www.zh.ch/de/steuern-finanzen/steuern/motorfahrzeugsteuer.html',
                'formula'=> function (array $v, array &$warnings): array {
                    $weight = $v['poids_vide'] ?? null;
                    if (!$weight) {
                        $warnings[] = 'Poids à vide manquant — estimation impossible';
                        return ['amount' => 0, 'breakdown' => [], 'warnings' => []];
                    }

                    // ZH : 0.48 CHF/kg pour les 1000 premiers kg,
                    //      0.36 CHF/kg au-delà
                    $tier1 = min($weight, 1000) * 0.48;
                    $tier2 = max(0, $weight - 1000) * 0.36;
                    $base  = $tier1 + $tier2;

                    // Bonus CO₂ ZH : -20% si CO₂ ≤ 90 g/km
                    $co2Bonus = 0;
                    if (isset($v['co2']) && $v['co2'] !== null && $v['co2'] <= 90) {
                        $co2Bonus = $base * 0.20;
                        $warnings[] = 'Bonus CO₂ ≤ 90 g/km appliqué (−20%)';
                    }

                    return [
                        'amount'    => $base - $co2Bonus,
                        'breakdown' => [
                            ['label' => "Tranche 1 (≤1000 kg × 0.48)", 'value' => round($tier1, 2)],
                            ['label' => "Tranche 2 (>1000 kg × 0.36)", 'value' => round($tier2, 2)],
                            ['label' => 'Bonus CO₂', 'value' => -round($co2Bonus, 2)],
                        ],
                        'warnings'  => [],
                    ];
                },
            ],

            // ── VD : Vaud — Basé sur la puissance (CV) ───────────────────────
            'VD' => [
                'name'   => 'Vaud',
                'abbr'   => 'VD',
                'basis'  => 'power_cv',
                'source' => 'https://www.vd.ch/themes/etat-droit-finances/impots/impot-sur-les-vehicules-a-moteur',
                'formula'=> function (array $v, array &$warnings): array {
                    $kw = $v['puissance_kw'] ?? null;
                    if (!$kw) {
                        $warnings[] = 'Puissance moteur manquante — estimation impossible';
                        return ['amount' => 0, 'breakdown' => [], 'warnings' => []];
                    }

                    // Conversion kW → CV fiscal (Steuerps) : 1 kW ≈ 1.35962 CV
                    $cv = (int) round($kw * 1.35962);

                    // VD : 14.80 CHF/CV — tranche progressive
                    $rate = 14.80;
                    if ($cv > 100) {
                        $rate = 15.80; // Majoration au-delà de 100 CV
                    }
                    if ($cv > 150) {
                        $rate = 17.20; // Majoration luxe
                    }

                    $base = $cv * $rate;

                    return [
                        'amount'    => $base,
                        'breakdown' => [
                            ['label' => "Puissance fiscale", 'value' => "{$cv} CV"],
                            ['label' => "Taux", 'value' => "{$rate} CHF/CV"],
                            ['label' => "Montant calculé", 'value' => round($base, 2)],
                        ],
                        'warnings'  => [],
                    ];
                },
            ],

            // ── GE : Genève — Basé sur la puissance kW + bonus CO₂ ───────────
            'GE' => [
                'name'   => 'Genève',
                'abbr'   => 'GE',
                'basis'  => 'power_kw+co2',
                'source' => 'https://www.ge.ch/imposition-vehicules',
                'formula'=> function (array $v, array &$warnings): array {
                    $kw  = $v['puissance_kw'] ?? null;
                    $co2 = $v['co2'] ?? null;

                    if (!$kw) {
                        $warnings[] = 'Puissance kW manquante';
                        return ['amount' => 0, 'breakdown' => [], 'warnings' => []];
                    }

                    // GE : 5.00 CHF/kW (base) + surtaxe CO₂
                    $base    = $kw * 5.00;
                    $co2Tax  = 0;

                    if ($co2 !== null) {
                        // Surtaxe progressive CO₂ à Genève
                        if ($co2 > 200) {
                            $co2Tax = ($co2 - 200) * 8.00;
                        } elseif ($co2 > 150) {
                            $co2Tax = ($co2 - 150) * 5.00;
                        } elseif ($co2 > 130) {
                            $co2Tax = ($co2 - 130) * 2.50;
                        }
                    }

                    // Bonus véhicule très propre (< 95 g/km)
                    $cleanBonus = 0;
                    if ($co2 !== null && $co2 <= 95) {
                        $cleanBonus = $base * 0.25;
                        $warnings[] = 'Bonus véhicule propre GE (CO₂ ≤ 95 g/km, −25%)';
                    }

                    return [
                        'amount'    => $base + $co2Tax - $cleanBonus,
                        'breakdown' => [
                            ['label' => "Base ({$kw} kW × 5.00)", 'value' => round($base, 2)],
                            ['label' => "Surtaxe CO₂ ({$co2} g/km)", 'value' => round($co2Tax, 2)],
                            ['label' => "Bonus propre", 'value' => -round($cleanBonus, 2)],
                        ],
                        'warnings'  => [],
                    ];
                },
            ],

            // ── BE : Berne — Basé sur le poids + CO₂ ────────────────────────
            'BE' => [
                'name'   => 'Berne',
                'abbr'   => 'BE',
                'basis'  => 'weight+co2',
                'source' => 'https://www.stv.bve.be.ch/de/start/motorfahrzeugsteuer.html',
                'formula'=> function (array $v, array &$warnings): array {
                    $weight = $v['poids_vide'] ?? null;
                    $co2    = $v['co2'] ?? null;

                    if (!$weight) {
                        $warnings[] = 'Poids à vide manquant';
                        return ['amount' => 0, 'breakdown' => [], 'warnings' => []];
                    }

                    // BE : 0.30 CHF/kg de base
                    $base    = $weight * 0.30;

                    // Malus CO₂ (> 130 g/km : +10%, > 175 : +25%)
                    $co2Mult = 1.0;
                    if ($co2 !== null) {
                        if ($co2 > 175) $co2Mult = 1.25;
                        elseif ($co2 > 130) $co2Mult = 1.10;
                        elseif ($co2 <= 95) $co2Mult = 0.85; // Bonus propre
                    }

                    $amount = $base * $co2Mult;

                    return [
                        'amount'    => $amount,
                        'breakdown' => [
                            ['label' => "Base ({$weight} kg × 0.30)", 'value' => round($base, 2)],
                            ['label' => "Multiplicateur CO₂", 'value' => "×{$co2Mult}"],
                            ['label' => "Montant calculé", 'value' => round($amount, 2)],
                        ],
                        'warnings'  => [],
                    ];
                },
            ],

            // ── VS : Valais — Basé sur la cylindrée ──────────────────────────
            'VS' => [
                'name'   => 'Valais',
                'abbr'   => 'VS',
                'basis'  => 'cylinder',
                'source' => 'https://www.vs.ch/web/sca/taxe-sur-les-vehicules',
                'formula'=> function (array $v, array &$warnings): array {
                    $cc = $v['cylindree'] ?? null;

                    // Véhicule électrique : calcul par poids
                    if ($this->isElectric($v['energie'] ?? null)) {
                        $weight = $v['poids_vide'] ?? 1500;
                        return [
                            'amount'    => $weight * 0.22,
                            'breakdown' => [['label' => "Électrique ({$weight} kg × 0.22)", 'value' => round($weight * 0.22, 2)]],
                            'warnings'  => ['Véhicule électrique : calcul par poids'],
                        ];
                    }

                    if (!$cc) {
                        $warnings[] = 'Cylindrée manquante';
                        return ['amount' => 0, 'breakdown' => [], 'warnings' => []];
                    }

                    // VS : 0.085 CHF/cm³ (taux 2024)
                    $rate   = 0.085;
                    $base   = $cc * $rate;

                    // Minimum VS : 120 CHF
                    $amount = max(120, $base);

                    return [
                        'amount'    => $amount,
                        'breakdown' => [
                            ['label' => "Cylindrée ({$cc} cm³ × 0.085)", 'value' => round($base, 2)],
                            ['label' => 'Minimum 120 CHF', 'value' => ''],
                        ],
                        'warnings'  => [],
                    ];
                },
            ],

            // ── FR : Fribourg — Basé sur la cylindrée + correctif poids ──────
            'FR' => [
                'name'   => 'Fribourg',
                'abbr'   => 'FR',
                'basis'  => 'cylinder+weight',
                'source' => 'https://www.fr.ch/dsc/sca/imposition-des-vehicules',
                'formula'=> function (array $v, array &$warnings): array {
                    $cc     = $v['cylindree'] ?? null;
                    $weight = $v['poids_vide'] ?? null;

                    if (!$cc && !$weight) {
                        $warnings[] = 'Données insuffisantes (cylindrée ou poids requis)';
                        return ['amount' => 0, 'breakdown' => [], 'warnings' => []];
                    }

                    if ($cc) {
                        // FR : tranche sur cylindrée
                        $base = match(true) {
                            $cc <= 600  => 90,
                            $cc <= 900  => 120,
                            $cc <= 1200 => 160,
                            $cc <= 1600 => 220,
                            $cc <= 2000 => 290,
                            $cc <= 2500 => 380,
                            $cc <= 3000 => 480,
                            $cc <= 4000 => 620,
                            default     => 800,
                        };
                    } else {
                        // Fallback poids
                        $base = ($weight ?? 1400) * 0.25;
                    }

                    return [
                        'amount'    => $base,
                        'breakdown' => [
                            ['label' => "Cylindrée : {$cc} cm³", 'value' => 'tranche forfaitaire'],
                            ['label' => "Montant de base", 'value' => round($base, 2)],
                        ],
                        'warnings'  => [],
                    ];
                },
            ],

            // ── TI : Tessin — Basé sur le poids ──────────────────────────────
            'TI' => [
                'name'   => 'Ticino',
                'abbr'   => 'TI',
                'basis'  => 'weight',
                'source' => 'https://www.ti.ch/tassa-circolazione',
                'formula'=> function (array $v, array &$warnings): array {
                    $weight = $v['poids_vide'] ?? null;
                    if (!$weight) {
                        $warnings[] = 'Poids à vide manquant';
                        return ['amount' => 0, 'breakdown' => [], 'warnings' => []];
                    }

                    // TI : 0.41 CHF/kg
                    $base = $weight * 0.41;

                    return [
                        'amount'    => $base,
                        'breakdown' => [['label' => "Poids ({$weight} kg × 0.41)", 'value' => round($base, 2)]],
                        'warnings'  => [],
                    ];
                },
            ],

            // ── SG : St-Gall — Basé sur la puissance kW ──────────────────────
            'SG' => [
                'name'   => 'Sankt Gallen',
                'abbr'   => 'SG',
                'basis'  => 'power_kw',
                'source' => 'https://www.sg.ch/strassenverkehr/motorfahrzeugsteuer.html',
                'formula'=> function (array $v, array &$warnings): array {
                    $kw = $v['puissance_kw'] ?? null;
                    if (!$kw) {
                        $warnings[] = 'Puissance kW manquante';
                        return ['amount' => 0, 'breakdown' => [], 'warnings' => []];
                    }

                    // SG : 6.20 CHF/kW
                    $base = $kw * 6.20;

                    return [
                        'amount'    => $base,
                        'breakdown' => [['label' => "Puissance ({$kw} kW × 6.20)", 'value' => round($base, 2)]],
                        'warnings'  => [],
                    ];
                },
            ],

            // ── LU : Lucerne — Basé sur le poids + CO₂ ───────────────────────
            'LU' => [
                'name'   => 'Luzern',
                'abbr'   => 'LU',
                'basis'  => 'weight+co2',
                'source' => 'https://www.lu.ch/strassenverkehrsamt/motorfahrzeugsteuern',
                'formula'=> function (array $v, array &$warnings): array {
                    $weight = $v['poids_vide'] ?? null;
                    $co2    = $v['co2'] ?? null;

                    if (!$weight) {
                        $warnings[] = 'Poids à vide manquant';
                        return ['amount' => 0, 'breakdown' => [], 'warnings' => []];
                    }

                    $base = $weight * 0.35;

                    // Bonus CO₂ LU
                    $modifier = 1.0;
                    if ($co2 !== null) {
                        if ($co2 <= 95)       $modifier = 0.75;
                        elseif ($co2 <= 130)  $modifier = 0.90;
                        elseif ($co2 > 160)   $modifier = 1.20;
                    }

                    return [
                        'amount'    => $base * $modifier,
                        'breakdown' => [
                            ['label' => "Base ({$weight} kg × 0.35)", 'value' => round($base, 2)],
                            ['label' => "Facteur CO₂", 'value' => "×{$modifier}"],
                        ],
                        'warnings'  => [],
                    ];
                },
            ],

            // ── AG : Argovie — Basé sur le poids ─────────────────────────────
            'AG' => [
                'name'   => 'Aargau',
                'abbr'   => 'AG',
                'basis'  => 'weight',
                'source' => 'https://www.ag.ch/motorfahrzeugkontrolle',
                'formula'=> function (array $v, array &$warnings): array {
                    $weight = $v['poids_vide'] ?? null;
                    if (!$weight) {
                        $warnings[] = 'Poids à vide manquant';
                        return ['amount' => 0, 'breakdown' => [], 'warnings' => []];
                    }

                    // AG : tranche progressive
                    $base = match(true) {
                        $weight <= 800  => $weight * 0.42,
                        $weight <= 1200 => 336 + ($weight - 800) * 0.38,
                        $weight <= 1800 => 488 + ($weight - 1200) * 0.34,
                        default         => 692 + ($weight - 1800) * 0.28,
                    };

                    return [
                        'amount'    => $base,
                        'breakdown' => [['label' => "Poids progressif ({$weight} kg)", 'value' => round($base, 2)]],
                        'warnings'  => [],
                    ];
                },
            ],

            // ── BS : Bâle-Ville — Basé sur CO₂ principalement ────────────────
            'BS' => [
                'name'   => 'Basel-Stadt',
                'abbr'   => 'BS',
                'basis'  => 'co2+weight',
                'source' => 'https://www.bs.ch/motorfahrzeugsteuer',
                'formula'=> function (array $v, array &$warnings): array {
                    $weight = $v['poids_vide'] ?? null;
                    $co2    = $v['co2'] ?? null;

                    if (!$weight) {
                        $warnings[] = 'Poids à vide manquant';
                        return ['amount' => 0, 'breakdown' => [], 'warnings' => []];
                    }

                    $base = $weight * 0.32;

                    // BS : forte pénalité CO₂ (politique environnementale)
                    $co2Penalty = 0;
                    if ($co2 !== null && $co2 > 95) {
                        $co2Penalty = ($co2 - 95) * 4.50;
                    }

                    // Bonus électrique BS : -100% (gratuit jusqu'en 2025, puis révision)
                    if ($this->isElectric($v['energie'] ?? null)) {
                        $warnings[] = 'Véhicule électrique — exonération BS (sous réserve)';
                        return [
                            'amount'    => 0,
                            'breakdown' => [['label' => 'Exonération électrique BS', 'value' => '0 CHF']],
                            'warnings'  => [],
                        ];
                    }

                    return [
                        'amount'    => $base + $co2Penalty,
                        'breakdown' => [
                            ['label' => "Base ({$weight} kg × 0.32)", 'value' => round($base, 2)],
                            ['label' => "Pénalité CO₂ ({$co2} g/km)", 'value' => round($co2Penalty, 2)],
                        ],
                        'warnings'  => [],
                    ];
                },
            ],

            // ── NE : Neuchâtel — Basé sur la puissance CV ────────────────────
            'NE' => [
                'name'   => 'Neuchâtel',
                'abbr'   => 'NE',
                'basis'  => 'power_cv',
                'source' => 'https://www.ne.ch/impot-vehicules',
                'formula'=> function (array $v, array &$warnings): array {
                    $kw = $v['puissance_kw'] ?? null;
                    if (!$kw) {
                        $warnings[] = 'Puissance kW manquante';
                        return ['amount' => 0, 'breakdown' => [], 'warnings' => []];
                    }

                    $cv   = (int) round($kw * 1.35962);
                    $base = $cv * 13.50; // NE : 13.50 CHF/CV

                    return [
                        'amount'    => $base,
                        'breakdown' => [
                            ['label' => "Puissance : {$cv} CV", 'value' => ''],
                            ['label' => "Taux NE (13.50/CV)", 'value' => round($base, 2)],
                        ],
                        'warnings'  => [],
                    ];
                },
            ],

            // ── JU : Jura — Basé sur le poids ────────────────────────────────
            'JU' => [
                'name'   => 'Jura',
                'abbr'   => 'JU',
                'basis'  => 'weight',
                'source' => 'https://www.jura.ch/DFC/SFI/Taxe-sur-les-vehicules.html',
                'formula'=> function (array $v, array &$warnings): array {
                    $weight = $v['poids_vide'] ?? null;
                    if (!$weight) {
                        $warnings[] = 'Poids à vide manquant';
                        return ['amount' => 0, 'breakdown' => [], 'warnings' => []];
                    }

                    $base = $weight * 0.38;
                    return [
                        'amount'    => $base,
                        'breakdown' => [['label' => "Poids ({$weight} kg × 0.38)", 'value' => round($base, 2)]],
                        'warnings'  => [],
                    ];
                },
            ],

        ]; // fin cantonConfig
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    /**
     * Détermine si le véhicule est électrique selon le code énergie ASTRA.
     * Code ASTRA "14" = électrique pur.
     */
    private function isElectric(?string $energieCode): bool
    {
        return $energieCode === '14';
    }

    /**
     * Applique le bonus électrique cantonal si disponible.
     */
    private function applyElectricBonus(string $canton, array $result, array $config): array
    {
        // Cantons offrant une réduction pour les véhicules électriques
        $electricBonuses = [
            'ZH' => 0.50,  // −50%
            'VD' => 0.50,  // −50%
            'GE' => 1.00,  // −100% (exonération)
            'BE' => 0.50,  // −50%
            'TI' => 0.30,  // −30%
            'LU' => 0.40,  // −40%
            'AG' => 0.25,  // −25%
            'NE' => 0.50,  // −50%
            'JU' => 0.30,  // −30%
        ];

        if (isset($electricBonuses[$canton]) && isset($result['amount'])) {
            $discount        = $result['amount'] * $electricBonuses[$canton];
            $result['amount'] -= $discount;
            $result['breakdown'][] = [
                'label' => "Bonus électrique ({$canton})",
                'value' => -round($discount, 2),
            ];
        }

        return $result;
    }
}
