<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request : SearchRequest
 *
 * Validation stricte de la requête de recherche.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * SÉCURITÉ — Prévention des injections SQL :
 *
 * Laravel utilise des "prepared statements" PDO par défaut (Eloquent/Query Builder),
 * ce qui protège structurellement contre les injections SQL. Cette Request ajoute
 * une couche de défense en profondeur via :
 *
 *   1. Regex stricte sur le numéro TG : seuls les chiffres, points et tirets autorisés
 *   2. Longueur maximale sur tous les champs (empêche les payloads oversized)
 *   3. Sanitisation des champs texte libres (marque, modèle) : caractères alphanumériques
 *      + espaces + tirets uniquement
 *   4. Type-casting explicite sur les champs numériques (puissance, cylindrée)
 * ─────────────────────────────────────────────────────────────────────────────
 */
class SearchRequest extends FormRequest
{
    /**
     * Regex du numéro TG officiel ASTRA.
     *
     * Formats acceptés :
     *   27.012.000.08.00004    (format normalisé avec points)
     *   27-012-000-08-00004    (tirets)
     *   27 012 000 08 00004    (espaces)
     *   27012000080004         (sans séparateur — anciens formats)
     *   27.012.*               (recherche partielle avec *)
     *
     * Longueur : 5 à 25 caractères après nettoyage.
     * Caractères autorisés : chiffres, points, tirets, espaces, astérisque (wildcard).
     *
     * Ce que la regex BLOQUE :
     *   - Guillemets simples/doubles ('  ")  → tentatives d'injection SQL
     *   - Points-virgules (;)               → fin de requête SQL
     *   - Commentaires (--)                 → injection SQL commentaire
     *   - Balises HTML (<>)                 → XSS
     *   - Backticks (`)                     → injection MySQL
     *   - Barres obliques (/)               → traversée de chemin
     */
    private const REGEX_NUMERO_TG = '/^[\d\.\-\s\*]{5,25}$/';

    /**
     * Regex pour les champs texte libres (marque, modèle, variante).
     * Autorise : lettres Unicode (accents, umlauts...), chiffres, espaces, tirets, apostrophes.
     * Bloque : tous les caractères de contrôle SQL/HTML.
     */
    private const REGEX_TEXT_FIELD = '/^[\p{L}\p{N}\s\-\'\.]{1,100}$/u';

    public function authorize(): bool
    {
        // Recherche publique — accessible à tous
        return true;
    }

    public function rules(): array
    {
        return [
            // ── Champ de recherche principal ──────────────────────────────────
            /**
             * 'query' : Recherche globale (TG, marque, modèle).
             * Validation souple car l'utilisateur peut chercher par marque ou TG.
             * La détection du type se fait dans SearchController::detectQueryType().
             */
            'query' => [
                'required',
                'string',
                'min:2',
                'max:150',
                // Caractères autorisés : lettres, chiffres, espaces, points, tirets, apostrophes
                'regex:/^[\p{L}\p{N}\s\.\-\*\']{2,150}$/u',
            ],

            // ── Filtres avancés (formulaire de recherche filtrée) ──────────────
            'numero_tg' => [
                'sometimes',
                'nullable',
                'string',
                'max:25',
                'regex:' . self::REGEX_NUMERO_TG,
            ],

            'marque' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                'regex:' . self::REGEX_TEXT_FIELD,
            ],

            'modele' => [
                'sometimes',
                'nullable',
                'string',
                'max:150',
                'regex:' . self::REGEX_TEXT_FIELD,
            ],

            'energie' => [
                'sometimes',
                'nullable',
                'string',
                // Seulement les codes ASTRA connus (liste blanche stricte)
                'in:01,02,03,04,05,06,07,08,09,10,11,12,13,14,15,16',
            ],

            // Type de véhicule : liste blanche stricte (voiture / moto)
            'vehicle_type' => [
                'sometimes',
                'nullable',
                'string',
                'in:car,motorcycle',
            ],

            'puissance_min' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
                'max:2000', // Limite raisonnable en kW (Bugatti = ~736 kW)
            ],

            'puissance_max' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
                'max:2000',
                'gte:puissance_min',
            ],

            'co2_max' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
                'max:600', // Limite haute réaliste en g/km
            ],

            'page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:1000',
            ],

            'per_page' => [
                'sometimes',
                'integer',
                'in:10,25,50', // Valeurs autorisées seulement (évite les DoS)
            ],
        ];
    }

    /**
     * Messages d'erreur traduits.
     * Les clés correspondent aux règles échouées.
     */
    public function messages(): array
    {
        return [
            'query.required'     => __('search.validation.query_required'),
            'query.min'          => __('search.validation.query_min'),
            'query.max'          => __('search.validation.query_max'),
            'query.regex'        => __('search.validation.query_invalid_chars'),
            'numero_tg.regex'    => __('search.validation.tg_invalid_format'),
            'energie.in'         => __('search.validation.energie_invalid'),
            'puissance_min.max'  => __('search.validation.puissance_out_of_range'),
            'per_page.in'        => __('search.validation.per_page_invalid'),
        ];
    }

    /**
     * Préparation des données avant validation.
     * Nettoie les espaces excessifs et normalise la casse.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyage de la requête principale
        if ($this->has('query')) {
            $this->merge([
                'query' => trim(preg_replace('/\s+/', ' ', $this->input('query', ''))),
            ]);
        }

        // Nettoyage du numéro TG : normalisation des séparateurs
        if ($this->has('numero_tg') && $this->input('numero_tg')) {
            $tg = trim($this->input('numero_tg'));
            // Normalisation : espaces → points
            $tg = preg_replace('/[\s_]+/', '.', $tg);
            // Tirets multiples → tiret simple
            $tg = preg_replace('/\.{2,}/', '.', $tg);
            $this->merge(['numero_tg' => $tg]);
        }

        // Marque en Title Case
        if ($this->has('marque') && $this->input('marque')) {
            $this->merge([
                'marque' => mb_convert_case(trim($this->input('marque')), MB_CASE_TITLE),
            ]);
        }
    }
}
