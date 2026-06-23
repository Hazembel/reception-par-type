<?php
/**
 * resources/lang/fr/access.php + resources/lang/fr/anomaly.php
 * (Fusionnés ici pour la lisibilité — à séparer en production)
 */

// ── Contrôle d'accès ────────────────────────────────────────────────────────
return [

    // Niveau 1
    'level1_message'  => 'Passez au niveau Starter pour accéder aux données de masse et de jantes.',
    'unlock_for'      => 'Débloquer cette fiche pour :price CHF',
    'subscribe_from'  => 'Ou abonnez-vous dès :price CHF/mois',

    // Abonnement expiré
    'subscription_expired' => 'Votre abonnement a expiré. Renouvelez-le pour continuer.',
    'renew_subscription'   => 'Renouveler mon abonnement',

    // Jetons insuffisants
    'no_tokens'        => 'Votre solde de jetons est insuffisant (:balance jeton(s) restant(s)).',
    'top_up_tokens'    => 'Recharger mes jetons',
    'token_price'      => '2 CHF / fiche',

    // Quota Pro dépassé
    'quota_exceeded'   => 'Vous avez atteint votre limite mensuelle de :limit fiches. Elle se renouvelle le 1er du mois.',
    'upgrade_to'       => 'Passer au plan Pro+',

    // Informations positives
    'already_unlocked' => 'Cette fiche est débloquée pour ce mois-ci.',
    'token_spent'      => '1 jeton utilisé. Solde restant : :balance jeton(s).',
    'quota_ok'         => ':remaining consultation(s) restante(s) ce mois.',

    // Tableau comparatif (affiché aux utilisateurs niveau 1)
    'comparison' => [
        'title'          => 'Choisissez votre formule',
        'one_time'       => 'Accès à l\'unité',
        'starter'        => 'Starter',
        'pro'            => 'Pro',
        'one_time_price' => '2 CHF',
        'starter_price'  => '9 CHF/mois',
        'pro_price'      => '49 CHF/mois',
        'one_time_desc'  => 'Débloque cette fiche pour 30 jours',
        'starter_desc'   => '50 fiches/mois avec données complètes',
        'pro_desc'       => '500 fiches/mois + accès API REST',
        'cta_one_time'   => 'Acheter maintenant',
        'cta_subscribe'  => 'S\'abonner',
    ],
];
