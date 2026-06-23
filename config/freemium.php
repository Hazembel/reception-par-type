<?php

/**
 * Configuration : Modèle Freemium
 *
 * Définit les limites et les fonctionnalités par niveau d'abonnement.
 * Ces valeurs sont référencées par User::remainingMonthlyQuota() et
 * les middlewares d'accès au contenu premium.
 *
 * Niveaux :
 *  1 = Gratuit (Free)
 *  2 = Starter
 *  3 = Basic
 *  4 = Pro
 *  5 = Pro+
 *  6 = Business
 *  7 = Business+
 *  8 = Entreprise
 */
return [

    // ── Limites mensuelles de consultations web par niveau ────────────────────
    'monthly_limits' => [
        1 => 10,      // Gratuit : 10 fiches/mois
        2 => 50,      // Starter : 50 fiches/mois
        3 => 150,     // Basic   : 150 fiches/mois
        4 => 500,     // Pro     : 500 fiches/mois
        5 => 1_000,   // Pro+    : 1 000 fiches/mois
        6 => 5_000,   // Business: 5 000 fiches/mois
        7 => 15_000,  // Business+: 15 000 fiches/mois
        8 => PHP_INT_MAX, // Entreprise : illimité
    ],

    // ── Accès aux exports (CSV, PDF, API) par niveau ──────────────────────────
    'export_access' => [
        'csv' => 3,   // Niveau minimum pour exporter en CSV
        'pdf' => 2,   // Niveau minimum pour exporter en PDF
        'api' => 4,   // Niveau minimum pour accéder à l'API JSON
    ],

    // ── Fonctionnalités par niveau ────────────────────────────────────────────
    'features' => [
        'comparateur'       => 2, // Comparateur de véhicules dès Starter
        'historique'        => 2, // Historique de recherches
        'alertes'           => 4, // Alertes sur les nouvelles fiches
        'multi_utilisateur' => 6, // Comptes multi-utilisateurs
        'api_access'        => 4, // Accès API REST
        'webhook'           => 6, // Webhooks pour intégrations
        'marque_blanche'    => 8, // Marque blanche / White label
    ],

    // ── Prix (CHF) par niveau — référence pour Stripe/Checkout ───────────────
    'pricing' => [
        1 => ['monthly' => 0,     'yearly' => 0],
        2 => ['monthly' => 9,     'yearly' => 90],
        3 => ['monthly' => 19,    'yearly' => 190],
        4 => ['monthly' => 49,    'yearly' => 490],
        5 => ['monthly' => 99,    'yearly' => 990],
        6 => ['monthly' => 199,   'yearly' => 1990],
        7 => ['monthly' => 399,   'yearly' => 3990],
        8 => ['monthly' => null,  'yearly' => null], // Sur devis
    ],

    // ── Coût en tokens des actions premium ───────────────────────────────────
    'token_costs' => [
        'vehicle_export_pdf'  => 1,
        'vehicle_export_csv'  => 1,
        'bulk_search'         => 5,
        'api_call'            => 1,
    ],

];
