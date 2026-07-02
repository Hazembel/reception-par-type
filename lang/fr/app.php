<?php

/**
 * Fichier de traduction : Français (FR)
 * reception-par-type.ch
 */
return [

    // ── Navigation ─────────────────────────────────────────────────────────
    'nav' => [
        'home'        => 'Accueil',
        'search'      => 'Recherche',
        'compare'     => 'Comparer',
        'pricing'     => 'Tarifs',
        'login'       => 'Connexion',
        'login_desc'  => 'Veuillez vous connecter pour accéder à votre espace.',
        'register'    => 'Inscription',
        'no_account'  => 'Pas encore de compte ?',
        'remember_me' => 'Se souvenir de moi',
        'logout'      => 'Déconnexion',
        'profile'     => 'Mon profil',
    ],

    // ── Authentification ───────────────────────────────────────────────────
    'auth' => [
        'email'            => 'Adresse email',
        'password'         => 'Mot de passe',
        'forgot_password'  => 'Mot de passe oublié ?',
    ],

    // ── Champs techniques véhicule ─────────────────────────────────────────
    'vehicle' => [
        'numero_tg'         => 'N° de réception par type',
        'marque'            => 'Marque',
        'modele'            => 'Modèle',
        'variante'          => 'Variante',
        'energie'           => 'Énergie',
        'puissance_kw'      => 'Puissance (kW)',
        'puissance_cv'      => 'Puissance (CV)',
        'cylindree'         => 'Cylindrée (cm³)',
        'boite_vitesse'     => 'Boîte de vitesses',
        'poids_vide'        => 'Poids à vide (kg)',
        'poids_total'       => 'PMA (kg)',
        'poids_remorquable' => 'Charge remorquable (kg)',
        'co2'               => 'CO₂ (g/km)',
        'code_emissions'    => 'Norme d\'émissions',
        'nb_trous'          => 'Nombre de trous',
        'entraxe'           => 'Entraxe (mm)',
        'alesage'           => 'Alésage (mm)',
        'deport_et'         => 'Déport ET (mm)',
        'pneus_origine'     => 'Pneumatiques d\'origine',

        // ── Sections de la fiche technique ────────────────────────────────
        'section_engine'     => 'Motorisation',
        'section_masses'     => 'Masses & Charges',
        'section_emissions'  => 'Émissions & Consommation',
        'section_wheels'     => 'Jantes & Pneumatiques',

        // ── En-tête & statut ──────────────────────────────────────────────
        'reception_badge'    => 'N° Réception par type',
        'status_active'      => 'Homologation active',
        'status_archived'    => 'Homologation archivée',
        'updated_at'         => 'Mise à jour',
        'updated_unknown'    => 'inconnue',

        // ── Consommation & autonomie ───────────────────────────────────────
        'consumption_mixed'  => 'Consommation NE',
        'consumption_wltp'   => 'Consommation WLTP',
        'consumption_el'     => 'Consommation élec.',
        'range_electric'     => 'Autonomie électrique',
        'energy_label'       => 'Label énergétique',

        // ── Partage ───────────────────────────────────────────────────────
        'bolts_unit'         => 'trous',
        'share'              => 'Partager :',
        'copy_link'          => 'Copier le lien',
        'link_copied'        => 'URL copiée !',
    ],

    // ── Messages d'erreur ──────────────────────────────────────────────────
    'errors' => [
        '400'        => 'Requête invalide',
        '400_detail' => 'La langue demandée n\'est pas supportée. Veuillez utiliser /fr/, /de/, /it/ ou /en/.',
        '404'        => 'Page introuvable',
        '403'        => 'Accès refusé',
        '500'        => 'Erreur serveur',
    ],

    // ── Freemium ───────────────────────────────────────────────────────────
    'freemium' => [
        'upgrade_required'   => 'Passez à un abonnement supérieur pour accéder à cette fonctionnalité.',
        'quota_exceeded'     => 'Vous avez atteint votre limite mensuelle de consultations.',
        'tokens_insufficient'=> 'Solde de tokens insuffisant. Rechargez votre compte.',
        'verify_email'       => 'Veuillez vérifier votre adresse email pour continuer.',
    ],


    // ── Recherche ──────────────────────────────────────────────────────────
    'search' => [
        'title'           => 'Rechercher un véhicule',
        'placeholder'     => 'N° de réception par type ou marque/modèle…',
        'button'          => 'Rechercher',
        'results_count'   => 'résultat(s)',
        'no_results'      => 'Aucun véhicule trouvé.',
        'no_results_hint' => 'Vérifiez le numéro TG (case 24 de la carte grise) ou essayez une autre marque.',
    ],

    // ── SEO ────────────────────────────────────────────────────────────────
    'seo' => [
        'home_title'       => 'Données techniques automobiles officielles ASTRA — reception-par-type.ch',
        'home_tagline'     => 'Données techniques ASTRA officielles pour tous les véhicules suisses',
        'home_description' => 'Consultez les données techniques homologuées de tout véhicule immatriculé en Suisse par son numéro de réception par type (TG).',
    ],

    // ── Footer ─────────────────────────────────────────────────────────────
    'footer' => [
        'desc_line1' => 'Données techniques ASTRA — Homologations suisses.',
        'desc_line2' => 'Non affilié à l\'OFROU/ASTRA.',
        'legal' => 'Mentions légales',
        'privacy' => 'Confidentialité',
        'terms' => 'CGU',
    ],

];
