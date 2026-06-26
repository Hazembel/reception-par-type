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
        'tg_nummer'                  => 'numero_tg',
        'tg-nummer'                  => 'numero_tg',
        'typengenehmigungsnummer'    => 'numero_tg',  // col 1 in ASTRA files
        'n_reception'                => 'numero_tg',

        // ── VIN (préfixe) ─────────────────────────────────────────────────
        'vin'                        => 'vin_prefix',
        'fahrgestellnummer'          => 'vin_prefix',
        '12 fahrgestellnummer'       => 'vin_prefix',  // col 22 in ASTRA files
        'vin_prefix'                 => 'vin_prefix',
        'wmi'                        => 'vin_prefix',

        // ── Homologation européenne ───────────────────────────────────────
        'eg_typengenehmigung'        => 'eu_type_approval',
        'eu_typengenehmigung'        => 'eu_type_approval',
        'ce_reception'               => 'eu_type_approval',
        'eu_type_approval'           => 'eu_type_approval',
        'homologation_eu'            => 'eu_type_approval',
        '09 eu-gesamtgenehmigung'    => 'eu_type_approval',  // col 18 in ASTRA files

        // ── Constructeur ──────────────────────────────────────────────────
        'marke'                      => 'marque',
        '04 marke'                   => 'marque',  // col 11 in ASTRA files
        'marque'                     => 'marque',
        'fabrikat'                   => 'marque',

        'typ'                        => 'modele',
        '04 typ'                     => 'modele',  // col 12 in ASTRA files
        'type'                       => 'modele',
        'modell'                     => 'modele',
        'modele'                     => 'modele',

        'variante'                   => 'variante',
        '05 typ; variante/version'   => 'variante',  // col 13 in ASTRA files
        'ausführung'                 => 'variante',
        'version'                    => 'variante',

        // ── Motorisation ──────────────────────────────────────────────────
        'antrieb'                    => 'energie',
        'energie'                    => 'energie',
        'kraftstoff'                 => 'energie',
        '22 bauart treibstoff'       => 'energie',  // col 63 in ASTRA files

        'leistung_kw'                => 'puissance_kw',
        'puissance_kw'               => 'puissance_kw',
        'nennleistung'               => 'puissance_kw',
        '24 leistung kw'             => 'puissance_kw',  // col 65 in ASTRA files

        'hubraum'                    => 'cylindree',
        '23 hubraum'                 => 'cylindree',  // col 64 in ASTRA files
        'cylindree'                  => 'cylindree',

        'getriebe'                   => 'boite_vitesse',
        '15 getriebe 1'              => 'boite_vitesse',  // col 27 in ASTRA files
        'boite'                      => 'boite_vitesse',
        'transmission'               => 'boite_vitesse',

        // ── Masses ────────────────────────────────────────────────────────
        'leergewicht'                => 'poids_vide',
        '44 leergewicht von'         => 'poids_vide',  // col 145 in ASTRA files
        'poids_vide'                 => 'poids_vide',

        'gesamtgewicht'              => 'poids_total',
        '45 garantiegewicht von'     => 'poids_total',  // col 147 in ASTRA files
        'pma'                        => 'poids_total',
        'poids_total'                => 'poids_total',

        'anhaengelast'               => 'poids_remorquable',
        '48 gebremst mech'           => 'poids_remorquable',  // col 166 in ASTRA files
        'charge_remorquable'         => 'poids_remorquable',

        // ── Émissions (présentes aussi dans certains fichiers TG) ─────────
        'co2'                        => 'co2',
        'co2_emission'               => 'co2',

        'abgasnorm'                  => 'code_emissions',
        'norme_emission'             => 'code_emissions',
        'euro_norm'                  => 'code_emissions',

        // ── Roues ─────────────────────────────────────────────────────────
        'lochzahl'                   => 'nb_trous',
        'nb_trous'                   => 'nb_trous',

        'lochkreis'                  => 'entraxe',
        'entraxe'                    => 'entraxe',

        'nabenbohrung'               => 'alesage',
        'alesage'                    => 'alesage',

        'einpresstiefe'              => 'deport_et',
        'et'                         => 'deport_et',
        'offset'                     => 'deport_et',

        'reifen'                     => 'pneus_origine',
        '52 reifen felgen'           => 'pneus_origine',  // col 191 in ASTRA files
        'pneus'                      => 'pneus_origine',
        'bereifung'                  => 'pneus_origine',
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
        // Clé de liaison (obligatoire)
        'tg_nummer'               => 'numero_tg',
        'tg-nummer'               => 'numero_tg',
        'typengenehmigungsnummer' => 'numero_tg',
        'n_reception'             => 'numero_tg',

        // CO2 (g/km)
        'co2'                     => 'co2',
        'co2_emission'            => 'co2',
        'co2_kombiniert'          => 'co2',

        // Norme antipollution détaillée (ex: "Euro 6d-ISC-FCM")
        'abgasnorm'               => 'pollution_norm',
        'emissionsnorm'           => 'pollution_norm',
        'norme_antipollution'     => 'pollution_norm',
        'pollution_norm'          => 'pollution_norm',

        // Code émission synthétique (ex: "EURO6d")
        'emissionscode'           => 'code_emissions',
        'code_emission'           => 'code_emissions',
        'code_emissions'          => 'code_emissions',
    ];

    /** Champs entiers communs (nettoyage numérique). */
    private const INTEGER_FIELDS = [
        'puissance_kw', 'cylindree', 'poids_vide', 'poids_total',
        'poids_remorquable', 'co2', 'nb_trous', 'entraxe', 'alesage', 'deport_et',
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

        // On ne garde la ligne que si elle apporte au moins une donnée d'émission.
        $hasPayload = isset($mapped['co2'])
            || isset($mapped['pollution_norm'])
            || isset($mapped['code_emissions']);

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
