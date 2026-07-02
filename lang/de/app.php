<?php

/**
 * Übersetzungsdatei : Deutsch (DE)
 * reception-par-type.ch
 */
return [

    // ── Navigation ─────────────────────────────────────────────────────────
    'nav' => [
        'home'        => 'Startseite',
        'search'      => 'Suche',
        'compare'     => 'Vergleichen',
        'pricing'     => 'Preise',
        'login'       => 'Anmelden',
        'login_desc'  => 'Bitte melden Sie sich an, um auf Ihr Konto zuzugreifen.',
        'register'    => 'Registrieren',
        'no_account'  => 'Noch kein Konto?',
        'remember_me' => 'Angemeldet bleiben',
        'logout'      => 'Abmelden',
        'profile'     => 'Mein Profil',
    ],

    // ── Authentifizierung ──────────────────────────────────────────────────
    'auth' => [
        'email'           => 'E-Mail-Adresse',
        'password'        => 'Passwort',
        'forgot_password' => 'Passwort vergessen?',
    ],

    // ── Technische Fahrzeugfelder ──────────────────────────────────────────
    'vehicle' => [
        'numero_tg'         => 'Typengenehmigungsnummer',
        'marque'            => 'Marke',
        'modele'            => 'Modell',
        'variante'          => 'Variante',
        'energie'           => 'Antrieb',
        'puissance_kw'      => 'Leistung (kW)',
        'puissance_cv'      => 'Leistung (PS)',
        'cylindree'         => 'Hubraum (cm³)',
        'boite_vitesse'     => 'Getriebe',
        'poids_vide'        => 'Leergewicht (kg)',
        'poids_total'       => 'Gesamtgewicht (kg)',
        'poids_remorquable' => 'Anhängelast (kg)',
        'co2'               => 'CO₂ (g/km)',
        'code_emissions'    => 'Emissionsnorm',
        'nb_trous'          => 'Lochanzahl',
        'entraxe'           => 'Lochkreis (mm)',
        'alesage'           => 'Nabenbohrung (mm)',
        'deport_et'         => 'Einpresstiefe ET (mm)',
        'pneus_origine'     => 'Serienreifen',

        // ── Abschnitte der Fahrzeugdaten ───────────────────────────────────
        'section_engine'     => 'Motorisierung',
        'section_masses'     => 'Gewichte & Lasten',
        'section_emissions'  => 'Emissionen & Verbrauch',
        'section_wheels'     => 'Felgen & Reifen',

        // ── Kopfzeile & Status ────────────────────────────────────────────
        'reception_badge'    => 'Nr. Typengenehmigung',
        'status_active'      => 'Typengenehmigung aktiv',
        'status_archived'    => 'Typengenehmigung archiviert',
        'updated_at'         => 'Aktualisiert',
        'updated_unknown'    => 'unbekannt',

        // ── Verbrauch & Reichweite ────────────────────────────────────────
        'consumption_mixed'  => 'Verbrauch (NEFZ)',
        'consumption_wltp'   => 'Verbrauch (WLTP)',
        'consumption_el'     => 'Stromverbrauch',
        'range_electric'     => 'Elektrische Reichweite',
        'energy_label'       => 'Energieetikette',

        // ── Teilen ────────────────────────────────────────────────────────
        'bolts_unit'         => 'Löcher',
        'share'              => 'Teilen:',
        'copy_link'          => 'Link kopieren',
        'link_copied'        => 'URL kopiert!',
    ],

    // ── Fehlermeldungen ────────────────────────────────────────────────────
    'errors' => [
        '400'        => 'Ungültige Anfrage',
        '400_detail' => 'Die angeforderte Sprache wird nicht unterstützt. Bitte verwenden Sie /fr/, /de/, /it/ oder /en/.',
        '404'        => 'Seite nicht gefunden',
        '403'        => 'Zugriff verweigert',
        '500'        => 'Serverfehler',
    ],

    // ── Freemium ───────────────────────────────────────────────────────────
    'freemium' => [
        'upgrade_required'    => 'Upgraden Sie Ihr Abonnement, um auf diese Funktion zuzugreifen.',
        'quota_exceeded'      => 'Sie haben Ihr monatliches Konsultationslimit erreicht.',
        'tokens_insufficient' => 'Unzureichendes Token-Guthaben. Bitte laden Sie Ihr Konto auf.',
        'verify_email'        => 'Bitte bestätigen Sie Ihre E-Mail-Adresse, um fortzufahren.',
    ],


    'search' => [
        'title'           => 'Fahrzeug suchen',
        'placeholder'     => 'Typengenehmigungsnummer oder Marke/Modell…',
        'button'          => 'Suchen',
        'results_count'   => 'Ergebnis(se)',
        'no_results'      => 'Kein Fahrzeug gefunden.',
        'no_results_hint' => 'Prüfen Sie die TG-Nummer (Feld 24 des Fahrzeugausweises).',
    ],
    'seo' => [
        'home_title'       => 'Offizielle technische Fahrzeugdaten ASTRA — reception-par-type.ch',
        'home_tagline'     => 'Offizielle ASTRA-Fahrzeugdaten für alle Schweizer Fahrzeuge',
        'home_description' => 'Technische Daten jedes in der Schweiz zugelassenen Fahrzeugs anhand der Typengenehmigungsnummer (TG).',
    ],

    // ── Fusszeile ──────────────────────────────────────────────────────────
    'footer' => [
        'desc_line1' => 'Offizielle ASTRA-Fahrzeugdaten — Schweizer Typengenehmigungen.',
        'desc_line2' => 'Nicht mit OFROU/ASTRA verbunden.',
        'legal'      => 'Impressum',
        'privacy'    => 'Datenschutz',
        'terms'      => 'Nutzungsbedingungen',
    ],

];
