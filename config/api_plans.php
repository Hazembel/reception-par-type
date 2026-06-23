<?php

/**
 * Configuration : Plans API B2B
 *
 * Définit les quotas, taux de requête et fonctionnalités par niveau
 * d'abonnement pour l'accès à l'API payante (niveaux 6, 7, 8).
 *
 * Référencé par :
 *  - EnsureApiKeyHasQuota (middleware rate limiting)
 *  - VehicleApiController (filtrage des champs exposés)
 *  - ApiKeyController (création des clés)
 */
return [

    // ── Niveau 6 : Business ───────────────────────────────────────────────────
    6 => [
        'name'              => 'Business API',
        'monthly_calls'     => 5_000,       // 5 000 appels API/mois
        'rate_per_minute'   => 30,           // 30 req/min (burst)
        'rate_per_second'   => 5,            // 5 req/s (strict)
        'concurrent'        => 2,            // 2 requêtes simultanées max
        'endpoints'         => [             // Endpoints accessibles
            'vehicle.tg',
            'vehicle.tyres',
            'vehicle.wheels',
        ],
        'fields'            => 'standard',   // Jeu de champs (standard | full)
        'export_csv'        => false,
        'webhooks'          => false,
        'sla_uptime'        => '99.5%',
    ],

    // ── Niveau 7 : Business+ ──────────────────────────────────────────────────
    7 => [
        'name'              => 'Business+ API',
        'monthly_calls'     => 15_000,
        'rate_per_minute'   => 60,
        'rate_per_second'   => 10,
        'concurrent'        => 5,
        'endpoints'         => [
            'vehicle.tg',
            'vehicle.tyres',
            'vehicle.wheels',
            'vehicle.tax',          // Simulateur fiscal (Module 6)
            'search.query',         // Recherche par marque/modèle
        ],
        'fields'            => 'full',
        'export_csv'        => true,
        'webhooks'          => false,
        'sla_uptime'        => '99.9%',
    ],

    // ── Niveau 8 : Enterprise ─────────────────────────────────────────────────
    8 => [
        'name'              => 'Enterprise API',
        'monthly_calls'     => PHP_INT_MAX,  // Illimité contractuellement
        'rate_per_minute'   => 300,
        'rate_per_second'   => 50,
        'concurrent'        => 20,
        'endpoints'         => [
            'vehicle.tg',
            'vehicle.tyres',
            'vehicle.wheels',
            'vehicle.tax',
            'search.query',
            'bulk.export',          // Export bulk (futur)
            'webhook.register',     // Webhooks sur nouvelles homologations
        ],
        'fields'            => 'full',
        'export_csv'        => true,
        'webhooks'          => true,
        'sla_uptime'        => '99.95%',
        'dedicated_support' => true,
    ],

    // ── Champs exposés par jeu ────────────────────────────────────────────────
    'field_sets' => [

        /**
         * Jeu standard : Niveau 6 — données techniques homologuées sans
         * informations internes ou dérivées (pas de slug, pas d'ID, pas de dates).
         */
        'standard' => [
            'numero_tg', 'marque', 'modele', 'variante',
            'energie', 'puissance_kw', 'cylindree', 'boite_vitesse',
            'poids_vide', 'poids_total', 'poids_remorquable',
            'co2', 'code_emissions',
            'nb_trous', 'entraxe', 'alesage', 'deport_et', 'pneus_origine',
        ],

        /**
         * Jeu complet : Niveaux 7/8 — ajoute les métadonnées d'import
         * et les données calculées (puissance en CV, diamètre pneu).
         */
        'full' => [
            'numero_tg', 'marque', 'modele', 'variante',
            'energie', 'puissance_kw', 'puissance_cv', 'cylindree', 'boite_vitesse',
            'poids_vide', 'poids_total', 'poids_remorquable',
            'co2', 'code_emissions',
            'nb_trous', 'entraxe', 'alesage', 'deport_et', 'pneus_origine',
            'is_active', 'imported_at',
        ],
    ],

    // ── Champs JAMAIS exposés (liste noire absolue) ───────────────────────────
    // Ces champs ne sont retournés dans AUCUNE réponse API, quel que soit le niveau.
    'hidden_fields' => [
        'id',           // ULID interne — jamais exposé (anti-énumération)
        'slug',         // Identifiant SEO interne
        'deleted_at',   // Soft delete — information système interne
        'updated_at',   // Timestamp interne
        'created_at',   // Timestamp interne (utiliser imported_at à la place)
    ],
];
