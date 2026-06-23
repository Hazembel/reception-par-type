<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Service : TyreService
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Intelligence métier pour les pneumatiques.
 *
 * Responsabilités :
 *  1. Décodage d'une chaîne de dimension de pneu (ex: "205/55R16 91V")
 *  2. Calcul du diamètre total de la roue en mm
 *  3. Génération des dimensions alternatives légales selon les normes ASA Suisse
 *     (tolérance de ±8% sur le diamètre extérieur)
 *  4. Validation des indices de charge et de vitesse
 *
 * Formule du diamètre total (norme ETRTO) :
 *   Ø total (mm) = (Largeur × Série/100 × 2) + (Ø jante × 25.4)
 *
 * Références légales :
 *   - OETV Art. 55 (Ordonnance sur les exigences techniques des véhicules routiers)
 *   - Directives ASA (Association des Services de l'Automobile)
 *   - Tolérance ±8% sur Ø extérieur selon réglementation suisse MFK
 * ─────────────────────────────────────────────────────────────────────────────
 */
class TyreService
{
    /**
     * Tolérance légale sur le diamètre extérieur (%) — norme ASA Suisse.
     * +8% = élargissement maximum, -8% = réduction maximum.
     */
    private const DIAMETER_TOLERANCE_PCT = 8.0;

    /**
     * Largeurs de pneus standardisées ETRTO (mm).
     * Seules ces valeurs sont homologuées en Suisse.
     */
    private const STANDARD_WIDTHS = [
        125, 135, 145, 155, 165, 175, 185, 195, 205, 215,
        225, 235, 245, 255, 265, 275, 285, 295, 305, 315,
        325, 335, 345, 355,
    ];

    /**
     * Séries de flanc standardisées ETRTO (%).
     */
    private const STANDARD_SERIES = [
        25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 82,
    ];

    /**
     * Diamètres de jante standardisés (pouces).
     */
    private const STANDARD_RIM_DIAMETERS = [
        13, 14, 15, 16, 17, 18, 19, 20, 21, 22,
    ];

    /**
     * Ordre de priorité des indices de vitesse (du plus bas au plus élevé).
     * Source : norme ETRTO
     */
    private const SPEED_INDEX_ORDER = [
        'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'H', 'V', 'W', 'Y', 'Z',
    ];

    // ── Décodage d'une dimension de pneu ──────────────────────────────────────

    /**
     * Décode une chaîne de dimension de pneu en tableau structuré.
     *
     * Formats acceptés :
     *   "205/55R16 91V"       → standard
     *   "205/55 R16 91V"      → avec espace avant R
     *   "205/55R16"           → sans indice
     *   "P205/55R16 91V"      → avec préfixe P (US)
     *   "205/55R16 91H XL"    → avec mention XL/RF
     *
     * @param  string $dimension  Chaîne brute issue de la BDD ASTRA
     * @return array{
     *   raw: string,
     *   width: int,
     *   series: int,
     *   rim_diameter: int,
     *   load_index: int|null,
     *   speed_index: string|null,
     *   is_xl: bool,
     *   diameter_mm: float,
     *   sidewall_mm: float,
     * }
     * @throws InvalidArgumentException Si le format est invalide
     */
    public function decode(string $dimension): array
    {
        $raw     = trim($dimension);
        $cleaned = strtoupper(preg_replace('/\s+/', ' ', $raw));

        // Suppression du préfixe P (marché US/Canada) s'il existe
        $cleaned = ltrim($cleaned, 'P');

        // Pattern principal : 205/55R16 91V (et variantes)
        // Capture : largeur / série R diamètre [indice_charge] [indice_vitesse] [XL|RF|C]
        $pattern = '/^(\d{3})\/(\d{2,3})\s?R\s?(\d{2})\s?(\d{2,3})?([A-Z])?(\s?(?:XL|RF|C|LT))?$/i';

        if (!preg_match($pattern, $cleaned, $m)) {
            throw new InvalidArgumentException(
                "Format de dimension de pneu non reconnu : \"{$raw}\". " .
                "Formats acceptés : 205/55R16, 205/55R16 91V, 205/55R16 91H XL"
            );
        }

        $width       = (int) $m[1];
        $series      = (int) $m[2];
        $rimDiameter = (int) $m[3];
        $loadIndex   = isset($m[4]) && $m[4] !== '' ? (int) $m[4] : null;
        $speedIndex  = isset($m[5]) && $m[5] !== '' ? strtoupper($m[5]) : null;
        $isXl        = isset($m[6]) && stripos($m[6], 'XL') !== false;

        // Validation des valeurs
        $this->validateDimensions($width, $series, $rimDiameter);

        $sidewallMm  = $this->calculateSidewall($width, $series);
        $diameterMm  = $this->calculateDiameter($width, $series, $rimDiameter);

        return [
            'raw'          => $raw,
            'width'        => $width,
            'series'       => $series,
            'rim_diameter' => $rimDiameter,
            'load_index'   => $loadIndex,
            'speed_index'  => $speedIndex,
            'is_xl'        => $isXl,
            'diameter_mm'  => round($diameterMm, 2),
            'sidewall_mm'  => round($sidewallMm, 2),
            'formatted'    => $this->format($width, $series, $rimDiameter, $loadIndex, $speedIndex, $isXl),
        ];
    }

    // ── Calculs de dimensions ─────────────────────────────────────────────────

    /**
     * Calcule le diamètre extérieur total du pneu monté sur jante.
     *
     * Formule ETRTO :
     *   Ø (mm) = (Largeur × Série/100 × 2) + (Ø_jante_pouces × 25.4)
     *
     * Exemple pour 205/55R16 :
     *   Flanc    = 205 × 0.55 = 112.75 mm
     *   Ø jante  = 16 × 25.4 = 406.4 mm
     *   Ø total  = (112.75 × 2) + 406.4 = 631.9 mm
     *
     * @param  int   $width       Largeur du pneu en mm
     * @param  int   $series      Série (ratio flanc) en %
     * @param  int   $rimDiameter Diamètre de jante en pouces
     * @return float Diamètre extérieur en mm
     */
    public function calculateDiameter(int $width, int $series, int $rimDiameter): float
    {
        $sidewall   = $this->calculateSidewall($width, $series);
        $rimMm      = $rimDiameter * 25.4;

        return ($sidewall * 2) + $rimMm;
    }

    /**
     * Calcule la hauteur du flanc du pneu.
     *
     * Formule : Flanc (mm) = Largeur × (Série / 100)
     */
    public function calculateSidewall(int $width, int $series): float
    {
        return $width * ($series / 100);
    }

    /**
     * Calcule la circonférence de roulement du pneu.
     * Utilisée pour la correction du compteur de vitesse.
     *
     * Formule : Circonférence = π × Ø_total
     */
    public function calculateCircumference(float $diameterMm): float
    {
        return M_PI * $diameterMm;
    }

    /**
     * Calcule l'écart de vitesse au compteur dû à un changement de dimensions.
     *
     * @param  float $originalDiameter  Diamètre d'origine en mm
     * @param  float $newDiameter       Nouveau diamètre en mm
     * @return array{percent: float, at_100: float, at_130: float}
     */
    public function calculateSpeedometerDeviation(float $originalDiameter, float $newDiameter): array
    {
        $ratio = ($newDiameter - $originalDiameter) / $originalDiameter * 100;

        return [
            'percent' => round($ratio, 2),
            'at_100'  => round(100 * ($newDiameter / $originalDiameter), 1),
            'at_130'  => round(130 * ($newDiameter / $originalDiameter), 1),
        ];
    }

    // ── Alternatives légales (norme ASA Suisse ±8%) ───────────────────────────

    /**
     * Génère la liste des dimensions alternatives légales selon la norme ASA.
     *
     * Règles appliquées :
     *  1. Diamètre extérieur dans la plage [original × 0.92, original × 1.08]
     *  2. Largeur normalisée ETRTO uniquement
     *  3. Série et diamètre de jante standardisés ETRTO
     *  4. Indice de charge ≥ à l'original (sécurité)
     *  5. Indice de vitesse ≥ à l'original (sécurité)
     *  6. Taille de jante inchangée (sauf si le diamètre de jante est aussi ajusté)
     *
     * @param  string   $originalDimension  Dimension d'origine (ex: "205/55R16 91V")
     * @param  bool     $sameRimOnly        Si true : ne change pas le diamètre de jante
     * @return array<int, array{
     *   formatted: string,
     *   width: int,
     *   series: int,
     *   rim_diameter: int,
     *   diameter_mm: float,
     *   deviation_pct: float,
     *   speedometer: array,
     *   load_index: int|null,
     *   speed_index: string|null,
     *   is_legal: bool,
     *   legal_note: string,
     * }>
     */
    public function getLegalAlternatives(
        string $originalDimension,
        bool   $sameRimOnly = true
    ): array {
        $original       = $this->decode($originalDimension);
        $originalDiam   = $original['diameter_mm'];
        $minDiam        = $originalDiam * (1 - self::DIAMETER_TOLERANCE_PCT / 100);
        $maxDiam        = $originalDiam * (1 + self::DIAMETER_TOLERANCE_PCT / 100);

        $alternatives = [];

        // Itération sur toutes les combinaisons standards
        $rimsToCheck = $sameRimOnly
            ? [$original['rim_diameter']]
            : self::STANDARD_RIM_DIAMETERS;

        foreach (self::STANDARD_WIDTHS as $width) {
            foreach (self::STANDARD_SERIES as $series) {
                foreach ($rimsToCheck as $rim) {
                    // Ignorer la dimension originale
                    if ($width === $original['width']
                        && $series === $original['series']
                        && $rim === $original['rim_diameter']) {
                        continue;
                    }

                    $diam = $this->calculateDiameter($width, $series, $rim);

                    // Vérification de la tolérance de diamètre
                    if ($diam < $minDiam || $diam > $maxDiam) {
                        continue;
                    }

                    $deviationPct = round(($diam - $originalDiam) / $originalDiam * 100, 2);

                    // Calcul de l'indice de charge minimal requis
                    // (conservateur : on maintient le même indice)
                    $minLoad = $original['load_index'];

                    // Formatage de la dimension
                    $formatted = $this->format(
                        $width, $series, $rim,
                        $minLoad,
                        $original['speed_index'],
                        false
                    );

                    // Note légale selon l'écart
                    $legalNote = $this->buildLegalNote($deviationPct, $width, $original['width']);

                    $alternatives[] = [
                        'formatted'      => $formatted,
                        'width'          => $width,
                        'series'         => $series,
                        'rim_diameter'   => $rim,
                        'diameter_mm'    => round($diam, 2),
                        'deviation_pct'  => $deviationPct,
                        'speedometer'    => $this->calculateSpeedometerDeviation($originalDiam, $diam),
                        'load_index'     => $minLoad,
                        'speed_index'    => $original['speed_index'],
                        'is_legal'       => abs($deviationPct) <= self::DIAMETER_TOLERANCE_PCT,
                        'legal_note'     => $legalNote,
                        'sidewall_mm'    => round($this->calculateSidewall($width, $series), 1),
                    ];
                }
            }
        }

        // Tri : d'abord les dimensions proches du diamètre original, puis par largeur
        usort($alternatives, function ($a, $b) use ($originalDiam) {
            $diffA = abs($a['diameter_mm'] - $originalDiam);
            $diffB = abs($b['diameter_mm'] - $originalDiam);
            return $diffA <=> $diffB;
        });

        // Limiter aux 20 meilleures alternatives
        return array_slice($alternatives, 0, 20);
    }

    // ── Validation des indices ─────────────────────────────────────────────────

    /**
     * Vérifie si un indice de vitesse est supérieur ou égal à l'original requis.
     *
     * @param  string $candidate  Indice proposé (ex: "V")
     * @param  string $required   Indice minimum requis (ex: "H")
     * @return bool
     */
    public function isSpeedIndexSufficient(string $candidate, string $required): bool
    {
        $order     = array_flip(self::SPEED_INDEX_ORDER);
        $candPos   = $order[strtoupper($candidate)] ?? -1;
        $reqPos    = $order[strtoupper($required)]  ?? -1;

        return $candPos >= $reqPos;
    }

    /**
     * Vérifie si un indice de charge est suffisant.
     */
    public function isLoadIndexSufficient(int $candidate, int $required): bool
    {
        return $candidate >= $required;
    }

    /**
     * Retourne la charge maximale en kg pour un indice de charge donné.
     * Source : tableau ETRTO standard
     */
    public function getLoadCapacity(int $loadIndex): ?int
    {
        $table = [
            60 => 250,  61 => 257,  62 => 265,  63 => 272,  64 => 280,
            65 => 290,  66 => 300,  67 => 307,  68 => 315,  69 => 325,
            70 => 335,  71 => 345,  72 => 355,  73 => 365,  74 => 375,
            75 => 387,  76 => 400,  77 => 412,  78 => 425,  79 => 437,
            80 => 450,  81 => 462,  82 => 475,  83 => 487,  84 => 500,
            85 => 515,  86 => 530,  87 => 545,  88 => 560,  89 => 580,
            90 => 600,  91 => 615,  92 => 630,  93 => 650,  94 => 670,
            95 => 690,  96 => 710,  97 => 730,  98 => 750,  99 => 775,
            100=> 800, 101=> 825, 102=> 850, 103=> 875, 104=> 900,
        ];

        return $table[$loadIndex] ?? null;
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    /**
     * Formate une dimension en chaîne standard (ex: "205/55R16 91V").
     */
    public function format(
        int     $width,
        int     $series,
        int     $rim,
        ?int    $loadIndex   = null,
        ?string $speedIndex  = null,
        bool    $isXl        = false
    ): string {
        $str = "{$width}/{$series}R{$rim}";

        if ($loadIndex !== null) {
            $str .= " {$loadIndex}";
        }
        if ($speedIndex !== null) {
            $str .= $speedIndex;
        }
        if ($isXl) {
            $str .= ' XL';
        }

        return $str;
    }

    /**
     * Génère une note légale lisible selon l'écart de diamètre.
     */
    private function buildLegalNote(float $deviationPct, int $newWidth, int $origWidth): string
    {
        $absDeviation = abs($deviationPct);

        if ($absDeviation <= 2.0) {
            return 'Équivalent (Ø identique)';
        }
        if ($absDeviation <= 4.0) {
            return 'Légal — écart minimal, aucune restriction';
        }
        if ($absDeviation <= 8.0) {
            $sign = $deviationPct > 0 ? '+' : '';
            $note = "Légal — Ø {$sign}" . round($deviationPct, 1) . '%';
            if ($newWidth > $origWidth + 20) {
                $note .= ' — vérifier garde-boue';
            }
            return $note;
        }

        return 'Hors tolérance légale (>' . self::DIAMETER_TOLERANCE_PCT . '%)';
    }

    /**
     * Validation des valeurs de dimensions.
     */
    private function validateDimensions(int $width, int $series, int $rim): void
    {
        if ($width < 100 || $width > 400) {
            throw new InvalidArgumentException("Largeur de pneu invalide : {$width} mm (attendu : 100–400)");
        }
        if ($series < 20 || $series > 90) {
            throw new InvalidArgumentException("Série de flanc invalide : {$series}% (attendu : 20–90)");
        }
        if ($rim < 10 || $rim > 24) {
            throw new InvalidArgumentException("Diamètre de jante invalide : {$rim}\" (attendu : 10–24)");
        }
    }
}
