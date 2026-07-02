<?php

/**
 * Translation file : English (EN)
 * reception-par-type.ch
 */
return [

    'nav' => [
        'home'        => 'Home',
        'search'      => 'Search',
        'compare'     => 'Compare',
        'pricing'     => 'Pricing',
        'login'       => 'Login',
        'login_desc'  => 'Please sign in to access your account.',
        'register'    => 'Register',
        'no_account'  => "Don't have an account?",
        'remember_me' => 'Remember me',
        'logout'      => 'Logout',
        'profile'     => 'My Profile',
    ],

    // ── Authentication ─────────────────────────────────────────────────────
    'auth' => [
        'email'           => 'Email address',
        'password'        => 'Password',
        'forgot_password' => 'Forgot password?',
    ],

    'vehicle' => [
        'numero_tg'         => 'Type Approval Number',
        'marque'            => 'Make',
        'modele'            => 'Model',
        'variante'          => 'Variant',
        'energie'           => 'Fuel Type',
        'puissance_kw'      => 'Power (kW)',
        'puissance_cv'      => 'Power (HP)',
        'cylindree'         => 'Engine Displacement (cm³)',
        'boite_vitesse'     => 'Transmission',
        'poids_vide'        => 'Kerb Weight (kg)',
        'poids_total'       => 'Gross Vehicle Weight (kg)',
        'poids_remorquable' => 'Towing Capacity (kg)',
        'co2'               => 'CO₂ (g/km)',
        'code_emissions'    => 'Emission Standard',
        'nb_trous'          => 'Number of Bolts',
        'entraxe'           => 'Bolt Circle (mm)',
        'alesage'           => 'Hub Bore (mm)',
        'deport_et'         => 'Offset ET (mm)',
        'pneus_origine'     => 'OEM Tyres',

        // ── Vehicle sheet sections ─────────────────────────────────────────
        'section_engine'     => 'Engine',
        'section_masses'     => 'Weights & Loads',
        'section_emissions'  => 'Emissions & Consumption',
        'section_wheels'     => 'Wheels & Tyres',

        // ── Header & status ───────────────────────────────────────────────
        'reception_badge'    => 'Type Approval No.',
        'status_active'      => 'Active type approval',
        'status_archived'    => 'Archived type approval',
        'updated_at'         => 'Updated',
        'updated_unknown'    => 'unknown',

        // ── Consumption & range ───────────────────────────────────────────
        'consumption_mixed'  => 'Fuel consumption (NEDC)',
        'consumption_wltp'   => 'Fuel consumption (WLTP)',
        'consumption_el'     => 'Electric consumption',
        'range_electric'     => 'Electric range',
        'energy_label'       => 'Energy label',

        // ── Share ─────────────────────────────────────────────────────────
        'bolts_unit'         => 'bolts',
        'share'              => 'Share:',
        'copy_link'          => 'Copy link',
        'link_copied'        => 'URL copied!',
    ],

    'errors' => [
        '400'        => 'Bad Request',
        '400_detail' => 'The requested language is not supported. Please use /fr/, /de/, /it/ or /en/.',
        '404'        => 'Page Not Found',
        '403'        => 'Access Denied',
        '500'        => 'Server Error',
    ],

    'freemium' => [
        'upgrade_required'    => 'Upgrade your subscription to access this feature.',
        'quota_exceeded'      => 'You have reached your monthly consultation limit.',
        'tokens_insufficient' => 'Insufficient token balance. Please top up your account.',
        'verify_email'        => 'Please verify your email address to continue.',
    ],


    'search' => [
        'title'           => 'Search for a vehicle',
        'placeholder'     => 'Type approval number or make/model…',
        'button'          => 'Search',
        'results_count'   => 'result(s)',
        'no_results'      => 'No vehicle found.',
        'no_results_hint' => 'Check the TG number (box 24 of the registration document).',
    ],
    'seo' => [
        'home_title'       => 'Official ASTRA vehicle technical data — reception-par-type.ch',
        'home_tagline'     => 'Official ASTRA technical data for all Swiss vehicles',
        'home_description' => 'Look up the homologated technical data of any vehicle registered in Switzerland by its type approval number (TG).',
    ],

    // ── Footer ─────────────────────────────────────────────────────────────
    'footer' => [
        'desc_line1' => 'Official ASTRA technical data — Swiss homologations.',
        'desc_line2' => 'Not affiliated with OFROU/ASTRA.',
        'legal'      => 'Legal Notice',
        'privacy'    => 'Privacy Policy',
        'terms'      => 'Terms of Service',
        'copyright'  => '© :year reception-par-type.ch — ASTRA data (OFROU) — All rights reserved',
    ],

    // ── Legal pages ────────────────────────────────────────────────────────
    'legal' => [
        'mentions_title' => 'Legal Notice',
        'privacy_title'  => 'Privacy Policy',
        'terms_title'    => 'Terms of Service',
        'last_updated'   => 'Last updated:',
    ],

    // ── Admin / Profile ────────────────────────────────────────────────────
    'admin' => [
        'dashboard'    => 'Dashboard',
        'edit_profile' => 'Edit my profile',
    ],

    // ── FAQ (CTA) ──────────────────────────────────────────────────────────
    'faq' => [
        'all'            => 'All',
        'contact_prompt' => 'Can\'t find your answer?',
        'contact_btn'    => 'Contact support',
    ],

];
