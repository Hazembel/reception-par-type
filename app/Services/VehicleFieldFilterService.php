<?php

namespace App\Services;

use App\Models\Vehicle;

class VehicleFieldFilterService
{
    /**
     * Champs "avancés" (masses + roues) qui nécessitent un abonnement payant
     * pour être consultés sur l'interface web.
     */
    private const ADVANCED_FIELDS = [
        'poids_vide',
        'poids_total',
        'poids_remorquable',
        'nb_trous',
        'entraxe',
        'alesage',
        'deport_et',
        'pneus_origine',
    ];

    /**
     * Nullifie les champs avancés sur un modèle Vehicle pour les utilisateurs
     * sans accès, garantissant qu'aucune donnée sensible ne transite vers la vue.
     */
    public static function applyWebAccess(Vehicle $vehicle, bool $canViewAdvanced): void
    {
        if ($canViewAdvanced) {
            return;
        }

        foreach (self::ADVANCED_FIELDS as $field) {
            $vehicle->{$field} = null;
        }
    }

    /**
     * Filtre un tableau brut de données Vehicle selon le jeu de champs autorisé
     * par un plan API. Garantit qu'aucun champ interne (ID, slug, timestamps)
     * n'est jamais exposé, même s'il figure dans la liste blanche du plan.
     *
     * @param  array  $data      Tableau brut issu de Vehicle::toArray()
     * @param  string $fieldSet  Clé du jeu de champs dans config/api_plans.php
     * @return array  Tableau filtré prêt pour la réponse JSON
     */
    public static function applyApiPlan(array $data, string $fieldSet): array
    {
        $allowedFields = config("api_plans.field_sets.{$fieldSet}", []);
        $hiddenFields  = config('api_plans.hidden_fields', []);

        $filtered = array_intersect_key($data, array_flip($allowedFields));
        $filtered = array_diff_key($filtered, array_flip($hiddenFields));

        // Conversion des dates en ISO 8601
        foreach (['imported_at'] as $dateField) {
            if (isset($filtered[$dateField]) && $filtered[$dateField]) {
                try {
                    $filtered[$dateField] = \Carbon\Carbon::parse($filtered[$dateField])->toIso8601String();
                } catch (\Throwable) {
                    $filtered[$dateField] = null;
                }
            }
        }

        // Conversion des colonnes numériques (PDO peut retourner des strings)
        $intFields = [
            'puissance_kw', 'cylindree', 'poids_vide', 'poids_total',
            'poids_remorquable', 'co2', 'nb_trous', 'entraxe', 'alesage', 'deport_et',
        ];
        foreach ($intFields as $f) {
            if (isset($filtered[$f]) && $filtered[$f] !== null) {
                $filtered[$f] = (int) $filtered[$f];
            }
        }

        return $filtered;
    }
}
