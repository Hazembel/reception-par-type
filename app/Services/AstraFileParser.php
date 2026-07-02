<?php

namespace App\Services;

use App\Models\Vehicle;

/**
 * Service : AstraFileParser
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Centralise toute la logique de parsing des fichiers TARGA de l'ASTRA.
 * Utilisé par les Jobs d'import :
 *   - ImportAstraMainJob        (TG-Automobil.txt, TG-Moto.txt)
 *   - ImportAstraNewsletterJob  (mises à jour hebdomadaires)
 *   - ImportAstraEmissionsJob   (emissionen.txt — couplage par numéro TG)
 *
 * Format des fichiers ASTRA :
 *   - Encodage : ISO-8859-1 (Windows-1252 parfois) → converti en UTF-8
 *   - Séparateur : tabulation (\t) ou point-virgule (;) selon le dossier
 *   - Première ligne : en-têtes de colonnes
 *   - Taille typique : 2000 → ~300 Mo | 5000 → ~1-5 Mo | émissions → variable
 * ─────────────────────────────────────────────────────────────────────────────
 */
class AstraFileParser
{
    /**
     * Mapping des colonnes des fichiers TG (voitures/motos) vers `vehicles`.
     *
     * Clé    = nom de colonne dans le fichier ASTRA (après mb_strtolower + trim)
     * Valeur = nom du champ dans la table `vehicles`
     *
     * ⚠️ À ajuster selon le fichier réel fourni par l'ASTRA : les libellés de
     *    colonnes peuvent varier selon la version et la langue du fichier.
     *
     * @var array<string, string>
     */
    public const COLUMN_MAP = [
        // ── Identification ────────────────────────────────────────────────
        // FR file (RT-automobil.txt) col 0
        'réception par type'         => 'numero_tg',
        // DE file (TG-Automobil.txt) col 0
        'typengenehmigungsnummer'    => 'numero_tg',
        // generic fallbacks
        'tg_nummer'                  => 'numero_tg',
        'tg-nummer'                  => 'numero_tg',
        'n_reception'                => 'numero_tg',

        // ── VIN (préfixe) ─────────────────────────────────────────────────
        // FR col 21, DE col 22
        '12 no de châssis'           => 'vin_prefix',
        '12 fahrgestellnummer'       => 'vin_prefix',
        'vin'                        => 'vin_prefix',
        'fahrgestellnummer'          => 'vin_prefix',
        'vin_prefix'                 => 'vin_prefix',
        'wmi'                        => 'vin_prefix',

        // ── Homologation européenne ───────────────────────────────────────
        // FR col 17, DE col 18
        '09 no de réception générale -ce' => 'eu_type_approval',
        '09 eu-gesamtgenehmigung'         => 'eu_type_approval',
        'eg_typengenehmigung'             => 'eu_type_approval',
        'eu_type_approval'                => 'eu_type_approval',

        // ── Constructeur ──────────────────────────────────────────────────
        // FR col 10, DE col 11
        '04 marque'                  => 'marque',
        '04 marke'                   => 'marque',
        'marke'                      => 'marque',
        'marque'                     => 'marque',

        // FR col 11, DE col 12
        '04 type'                    => 'modele',
        '04 typ'                     => 'modele',
        'typ'                        => 'modele',

        // FR col 12, DE col 13
        '05 type; variante/version'  => 'variante',
        '05 typ; variante/version'   => 'variante',
        'variante'                   => 'variante',
        'version'                    => 'variante',

        // ── Motorisation ──────────────────────────────────────────────────
        // FR col 26 "18 Boîte de vitesses 1", DE col 27 "18 Getriebe 1"
        '18 boîte de vitesses 1'     => 'boite_vitesse',
        '18 getriebe 1'              => 'boite_vitesse',
        'getriebe'                   => 'boite_vitesse',

        // FR col 62 "26 Construction carburant", DE col 63 "26 Bauart Treibstoff"
        '26 construction carburant'  => 'energie',
        '26 bauart treibstoff'       => 'energie',
        'antrieb'                    => 'energie',
        'kraftstoff'                 => 'energie',

        // FR col 63 "27 Cylindrée", DE col 64 "27 Hubraum"
        '27 cylindrée'               => 'cylindree',
        '27 hubraum'                 => 'cylindree',
        'hubraum'                    => 'cylindree',

        // FR col 64 "28 Puissance kw", DE col 65 "28 Leistung kW"
        '28 puissance kw'            => 'puissance_kw',
        '28 leistung kw'             => 'puissance_kw',
        'nennleistung'               => 'puissance_kw',

        // ── Masses ────────────────────────────────────────────────────────
        // FR col 144 "52 Poids à vide de", DE col 145 "52 Leergewicht von"
        '52 poids à vide de'         => 'poids_vide',
        '52 leergewicht von'         => 'poids_vide',
        'leergewicht'                => 'poids_vide',

        // FR col 146 "53 Poids garanti de", DE col 147 "53 Garantiegewicht von"
        '53 poids garanti de'        => 'poids_total',
        '53 garantiegewicht von'     => 'poids_total',
        'gesamtgewicht'              => 'poids_total',

        // FR col 165 "57 freinés méc.", DE col 166 "57 gebremst mech"
        '57 freinés méc.'            => 'poids_remorquable',
        '57 gebremst mech'           => 'poids_remorquable',
        'anhaengelast'               => 'poids_remorquable',

        // ── Roues / Pneus ─────────────────────────────────────────────────
        // FR col 190-192 "69/70/71 Pneus et jantes"
        '69 pneus et jantes'         => 'pneus_origine',
        '70 pneus et jantes'         => 'pneus_origine',
        '71 pneus et jantes'         => 'pneus_origine',
        // DE col 191-193 "69/70/71 Reifen Felgen"
        '69 reifen felgen'           => 'pneus_origine',
        '70 reifen felgen'           => 'pneus_origine',
        '71 reifen felgen'           => 'pneus_origine',
        'reifen'                     => 'pneus_origine',
        'bereifung'                  => 'pneus_origine',

        // generic wheel fields (used if present)
        'lochzahl'                   => 'nb_trous',
        'lochkreis'                  => 'entraxe',
        'nabenbohrung'               => 'alesage',
        'einpresstiefe'              => 'deport_et',

        // ── RT-moto.txt specific (different section numbers than RT-automobil) ──
        '15 boîte de vitesses 1'     => 'boite_vitesse',
        '22 construction carburant'  => 'energie',
        '23 cylindrée'               => 'cylindree',
        '24 puissance kw'            => 'puissance_kw',
        '44 poids à vide de'         => 'poids_vide',
        '45 poids garanti de'        => 'poids_total',
        '52 pneus et jantes'         => 'pneus_origine',
        '53 pneus et jantes'         => 'pneus_origine',
        '54 pneus et jantes'         => 'pneus_origine',
    ];

    /**
     * Mapping des colonnes du fichier des émissions (emissionen.txt).
     *
     * Ce fichier ne crée jamais de véhicule : il enrichit les fiches existantes
     * via le numéro TG. On n'y lit donc que la clé de liaison + les champs
     * d'émissions.
     *
     * @var array<string, string>
     */
    public const EMISSIONS_COLUMN_MAP = [
        // ── Clé de liaison (obligatoire) ─────────────────────────────────
        // emissionen.txt col 0 actual header
        'tg-code'                 => 'numero_tg',
        // fallbacks for other file versions
        'tg_nummer'               => 'numero_tg',
        'tg-nummer'               => 'numero_tg',
        'typengenehmigungsnummer' => 'numero_tg',
        'n_reception'             => 'numero_tg',

        // ── Motorisation (enrichit les véhicules du fichier principal) ────
        // emissionen.txt col 16
        'treibstoff'              => 'energie',
        // emissionen.txt col 9
        'getriebe'                => 'boite_vitesse',
        // emissionen.txt col 19
        'hubraum'                 => 'cylindree',
        // emissionen.txt col 20
        'leistung'                => 'puissance_kw',

        // ── Masses ───────────────────────────────────────────────────────
        // emissionen.txt col 3
        'leergewicht_von'         => 'poids_vide',
        // emissionen.txt col 5
        'garantiegewicht_von'     => 'poids_total',

        // ── CO2 (g/km) ───────────────────────────────────────────────────
        // emissionen.txt col 42 "ScCo2"
        'scco2'                   => 'co2',
        'co2'                     => 'co2',
        'co2_emission'            => 'co2',
        'co2_kombiniert'          => 'co2',

        // ── Code émissions ───────────────────────────────────────────────
        // emissionen.txt col 27 "AbgasCode" (ex: "AABB")
        'abgascode'               => 'code_emissions',
        'abgasnorm'               => 'code_emissions',
        // emissionen.txt col 28 "Emissionscode" (ex: "B02")
        'emissionscode'           => 'pollution_norm',
        'emissionsnorm'           => 'pollution_norm',
        'pollution_norm'          => 'pollution_norm',
    ];

    /**
     * Mapping des colonnes verbrauch.txt vers `vehicles`.
     *
     * @var array<string, string>
     */
    public const VERBRAUCH_COLUMN_MAP = [
        'tg-code'                    => 'numero_tg',
        'energieeffizienzkategorie'  => 'energie_label',
        // ET = older NEDC single-test (fallback when ZT is absent)
        'et_verbrauch'               => '_et_conso',
        // ZT = NEDC combined (preferred source)
        'zt_verbrauch'               => 'consommation_mixte',
        'zt_verbrauch_wltp'          => 'consommation_wltp',    // WLTP combined (l/100km)
        'zt_co2_wltp'                => 'co2_wltp',             // WLTP CO2 (g/km)
        'el_verbrauch_wltp'          => 'consommation_el',      // electric (kWh/100km)
        'el_reichweite_von_wltp'     => 'autonomie_min',        // EV range min (km)
        'el_reichweite_bis_wltp'     => 'autonomie_max',        // EV range max (km)
    ];

    /** Champs entiers communs (nettoyage numérique). */
    private const INTEGER_FIELDS = [
        'puissance_kw', 'cylindree', 'poids_vide', 'poids_total',
        'poids_remorquable', 'co2', 'co2_wltp', 'autonomie_min', 'autonomie_max',
        'nb_trous', 'entraxe', 'alesage', 'deport_et',
    ];

    /** Champs décimaux (consommation en l/100km ou kWh/100km). */
    private const DECIMAL_FIELDS = [
        'consommation_mixte', 'consommation_wltp', 'consommation_el',
    ];

    /**
     * Nettoie et normalise un numéro TG brut issu du fichier ASTRA.
     *
     * Convention retenue : format officiel avec points "xx.xxx.xxx.xx.xxxxx",
     * tel qu'il figure sur les documents ASTRA.
     */
    public static function cleanNumeroTg(string $raw): string
    {
        $clean = trim($raw);
        $clean = preg_replace('/[\s\-_]+/', '.', $clean); // séparateurs → points
        $clean = preg_replace('/\.{2,}/', '.', $clean);   // points doubles → simple
        $clean = trim($clean, '.');                        // points en bord

        return $clean;
    }

    /**
     * Tronque et normalise un VIN vers son préfixe stockable (9 caractères).
     * Délègue au modèle pour garder UNE seule source de vérité.
     */
    public static function truncateVin(?string $vin): ?string
    {
        return Vehicle::normalizeVinPrefix($vin);
    }

    /**
     * Parse une ligne d'un fichier TG (voiture ou moto).
     *
     * @param  array<string>        $headers   En-têtes normalisés (lowercase + trim)
     * @param  array<string>        $values    Valeurs de la ligne courante
     * @return array<string, mixed>|null       Null si ligne invalide/vide
     */
    public static function parseLine(array $headers, array $values): ?array
    {
        // ASTRA files omit trailing empty fields on short rows — pad to header count
        if (count($values) > count($headers)) {
            return null; // more values than headers = genuinely malformed
        }
        if (count($values) < count($headers)) {
            $values = array_pad($values, count($headers), '');
        }
        $raw = array_combine($headers, $values);

        $mapped = [];
        foreach (self::COLUMN_MAP as $astraCol => $vehicleField) {
            if (isset($raw[$astraCol])) {
                $val = trim($raw[$astraCol]);
                // On ne réécrit pas un champ déjà rempli avec une valeur vide
                // (plusieurs alias peuvent pointer vers le même champ).
                if ($val === '' || $val === '-') {
                    $mapped[$vehicleField] ??= null;
                } else {
                    $mapped[$vehicleField] = $val;
                }
            }
        }

        // Le numéro TG est obligatoire
        if (empty($mapped['numero_tg'])) {
            return null;
        }

        $mapped['numero_tg'] = self::cleanNumeroTg($mapped['numero_tg']);
        if (strlen($mapped['numero_tg']) < 5) {
            return null;
        }

        // Troncature/normalisation du préfixe VIN (jamais le VIN complet en base)
        if (array_key_exists('vin_prefix', $mapped)) {
            $mapped['vin_prefix'] = self::truncateVin($mapped['vin_prefix']);
        }

        self::castIntegers($mapped);

        return $mapped;
    }

    /**
     * Parse une ligne du fichier des émissions (emissionen.txt).
     *
     * Renvoie uniquement la clé de liaison `numero_tg` + les champs d'émissions
     * présents, prêts pour un UPDATE ciblé des véhicules existants.
     *
     * @param  array<string>        $headers
     * @param  array<string>        $values
     * @return array<string, mixed>|null  Null si pas de TG exploitable
     */
    public static function parseEmissionLine(array $headers, array $values): ?array
    {
        if (count($values) > count($headers)) {
            return null;
        }
        if (count($values) < count($headers)) {
            $values = array_pad($values, count($headers), '');
        }
        $raw = array_combine($headers, $values);

        $mapped = [];
        foreach (self::EMISSIONS_COLUMN_MAP as $col => $field) {
            if (isset($raw[$col])) {
                $val = trim($raw[$col]);
                if ($val === '' || $val === '-') {
                    $mapped[$field] ??= null;
                } else {
                    $mapped[$field] = $val;
                }
            }
        }

        if (empty($mapped['numero_tg'])) {
            return null;
        }

        $mapped['numero_tg'] = self::cleanNumeroTg($mapped['numero_tg']);
        if (strlen($mapped['numero_tg']) < 5) {
            return null;
        }

        self::castIntegers($mapped);

        // Keep the row if it brings any useful data (emissions OR engine/weight fields).
        $hasPayload = isset($mapped['co2'])
            || isset($mapped['pollution_norm'])
            || isset($mapped['code_emissions'])
            || isset($mapped['energie'])
            || isset($mapped['boite_vitesse'])
            || isset($mapped['cylindree'])
            || isset($mapped['puissance_kw'])
            || isset($mapped['poids_vide'])
            || isset($mapped['poids_total']);

        return $hasPayload ? $mapped : null;
    }

    /**
     * Parse une ligne du fichier de consommation (verbrauch.txt).
     *
     * @param  array<string>        $headers
     * @param  array<string>        $values
     * @return array<string, mixed>|null  Null si pas de TG ou aucune donnée utile
     */
    public static function parseVerbrauchLine(array $headers, array $values): ?array
    {
        if (count($values) > count($headers)) {
            return null;
        }
        if (count($values) < count($headers)) {
            $values = array_pad($values, count($headers), '');
        }
        $raw = array_combine($headers, $values);

        $mapped = [];
        foreach (self::VERBRAUCH_COLUMN_MAP as $col => $field) {
            if (isset($raw[$col])) {
                $val = trim($raw[$col]);
                if ($val === '' || $val === '-') {
                    $mapped[$field] ??= null;
                } else {
                    $mapped[$field] = $val;
                }
            }
        }

        if (empty($mapped['numero_tg'])) {
            return null;
        }

        $mapped['numero_tg'] = self::cleanNumeroTg($mapped['numero_tg']);
        if (strlen($mapped['numero_tg']) < 5) {
            return null;
        }

        self::castIntegers($mapped);
        self::castDecimals($mapped);

        // ASTRA stores EL_Verbrauch_WLTP in Wh/km; convert to kWh/100km (÷10).
        if (isset($mapped['consommation_el']) && $mapped['consommation_el'] > 0) {
            $mapped['consommation_el'] = round((float) $mapped['consommation_el'] / 10, 2);
        }

        // Zero is meaningless for range and WLTP CO2 — treat as absent.
        foreach (['autonomie_min', 'autonomie_max', 'co2_wltp'] as $f) {
            if (isset($mapped[$f]) && $mapped[$f] === 0) {
                $mapped[$f] = null;
            }
        }

        // ET_Verbrauch fallback: if ZT combined is null/zero, use ET single-test value.
        if (empty($mapped['consommation_mixte']) && isset($mapped['_et_conso'])) {
            $et = (float) str_replace(',', '.', (string) $mapped['_et_conso']);
            $mapped['consommation_mixte'] = $et > 0 ? $et : null;
        }
        unset($mapped['_et_conso']);

        // Only keep row if it has at least one consumption or label value.
        $hasPayload = isset($mapped['consommation_mixte'])
            || isset($mapped['consommation_wltp'])
            || isset($mapped['co2_wltp'])
            || isset($mapped['consommation_el'])
            || isset($mapped['autonomie_min'])
            || isset($mapped['autonomie_max'])
            || isset($mapped['energie_label']);

        return $hasPayload ? $mapped : null;
    }

    /**
     * Caste en entier les champs numériques d'un tableau mappé (en place).
     * Extrait la partie numérique d'une valeur du type "150 kW" → 150.
     *
     * @param array<string, mixed> $mapped
     */
    private static function castIntegers(array &$mapped): void
    {
        foreach (self::INTEGER_FIELDS as $field) {
            if (isset($mapped[$field]) && $mapped[$field] !== null) {
                preg_match('/^-?\d+/', (string) $mapped[$field], $m);
                $mapped[$field] = !empty($m) ? (int) $m[0] : null;
            }
        }
    }

    /**
     * Caste en float les champs décimaux (consommation l/100km, kWh/100km).
     *
     * @param array<string, mixed> $mapped
     */
    private static function castDecimals(array &$mapped): void
    {
        foreach (self::DECIMAL_FIELDS as $field) {
            if (isset($mapped[$field]) && $mapped[$field] !== null) {
                $val = (float) str_replace(',', '.', (string) $mapped[$field]);
                $mapped[$field] = $val > 0 ? $val : null;
            }
        }
    }

    /**
     * Détecte le séparateur de colonnes du fichier (TAB vs point-virgule).
     */
    public static function detectSeparator(string $firstLine): string
    {
        $tabCount       = substr_count($firstLine, "\t");
        $semicolonCount = substr_count($firstLine, ';');

        return $tabCount >= $semicolonCount ? "\t" : ';';
    }

    /**
     * Normalise les en-têtes de colonnes ASTRA (minuscules + trim).
     *
     * @param  array<string> $rawHeaders
     * @return array<string>
     */
    public static function normalizeHeaders(array $rawHeaders): array
    {
        return array_map(
            fn ($h) => mb_strtolower(trim($h)),
            $rawHeaders
        );
    }

    /**
     * Convertit une ligne brute (ISO-8859-1) en UTF-8 si nécessaire.
     */
    public static function toUtf8(string $line): string
    {
        if (mb_detect_encoding($line, 'UTF-8', true) === 'UTF-8') {
            return $line;
        }

        return mb_convert_encoding($line, 'UTF-8', 'ISO-8859-1');
    }

    /**
     * Calcule le hash SHA-256 d'un fichier (idempotence), sans le charger en RAM.
     */
    public static function fileHash(string $path): string
    {
        return hash_file('sha256', $path);
    }
}
