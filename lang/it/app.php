<?php

/**
 * File di traduzione : Italiano (IT)
 * reception-par-type.ch
 */
return [

    'nav' => [
        'home'        => 'Home',
        'search'      => 'Ricerca',
        'compare'     => 'Confronta',
        'pricing'     => 'Prezzi',
        'login'       => 'Accesso',
        'login_desc'  => 'Accedi per entrare nel tuo spazio personale.',
        'register'    => 'Registrazione',
        'no_account'  => 'Non hai ancora un account?',
        'remember_me' => 'Ricordami',
        'logout'      => 'Esci',
        'profile'     => 'Il mio profilo',
    ],

    // ── Autenticazione ─────────────────────────────────────────────────────
    'auth' => [
        'email'           => 'Indirizzo email',
        'password'        => 'Password',
        'forgot_password' => 'Password dimenticata?',
    ],

    'vehicle' => [
        'numero_tg'         => 'N° di omologazione per tipo',
        'marque'            => 'Marca',
        'modele'            => 'Modello',
        'variante'          => 'Variante',
        'energie'           => 'Energia',
        'puissance_kw'      => 'Potenza (kW)',
        'puissance_cv'      => 'Potenza (CV)',
        'cylindree'         => 'Cilindrata (cm³)',
        'boite_vitesse'     => 'Cambio',
        'poids_vide'        => 'Tara (kg)',
        'poids_total'       => 'Massa totale (kg)',
        'poids_remorquable' => 'Carico rimorchiabile (kg)',
        'co2'               => 'CO₂ (g/km)',
        'code_emissions'    => 'Norma emissioni',
        'nb_trous'          => 'Numero fori',
        'entraxe'           => 'Interasse fori (mm)',
        'alesage'           => 'Centraggio mozzo (mm)',
        'deport_et'         => 'Offset ET (mm)',
        'pneus_origine'     => 'Pneumatici originali',

        // ── Sezioni della scheda tecnica ───────────────────────────────────
        'section_engine'     => 'Motorizzazione',
        'section_masses'     => 'Masse & Carichi',
        'section_emissions'  => 'Emissioni & Consumo',
        'section_wheels'     => 'Cerchi & Pneumatici',

        // ── Intestazione & stato ──────────────────────────────────────────
        'reception_badge'    => 'N° Omologazione tipo',
        'status_active'      => 'Omologazione attiva',
        'status_archived'    => 'Omologazione archiviata',
        'updated_at'         => 'Aggiornato',
        'updated_unknown'    => 'sconosciuta',

        // ── Consumo & autonomia ───────────────────────────────────────────
        'consumption_mixed'  => 'Consumo NE',
        'consumption_wltp'   => 'Consumo WLTP',
        'consumption_el'     => 'Consumo elettrico',
        'range_electric'     => 'Autonomia elettrica',
        'energy_label'       => 'Etichetta energetica',

        // ── Condivisione ──────────────────────────────────────────────────
        'bolts_unit'         => 'fori',
        'share'              => 'Condividi:',
        'copy_link'          => 'Copia link',
        'link_copied'        => 'URL copiato!',
    ],

    'errors' => [
        '400'        => 'Richiesta non valida',
        '400_detail' => 'La lingua richiesta non è supportata. Si prega di utilizzare /fr/, /de/, /it/ o /en/.',
        '404'        => 'Pagina non trovata',
        '403'        => 'Accesso negato',
        '500'        => 'Errore del server',
    ],

    'freemium' => [
        'upgrade_required'    => 'Effettua l\'upgrade dell\'abbonamento per accedere a questa funzione.',
        'quota_exceeded'      => 'Hai raggiunto il limite mensile di consultazioni.',
        'tokens_insufficient' => 'Saldo token insufficiente. Ricarica il tuo account.',
        'verify_email'        => 'Verifica il tuo indirizzo email per continuare.',
    ],


    'search' => [
        'title'           => 'Cerca un veicolo',
        'placeholder'     => 'Numero di approvazione del tipo o marca/modello…',
        'button'          => 'Cerca',
        'results_count'   => 'risultato/i',
        'no_results'      => 'Nessun veicolo trovato.',
        'no_results_hint' => 'Verifica il numero TG (casella 24 della licenza di circolazione).',
    ],
    'seo' => [
        'home_title'       => 'Dati tecnici automobilistici ufficiali ASTRA — reception-par-type.ch',
        'home_tagline'     => 'Dati tecnici ufficiali ASTRA per tutti i veicoli svizzeri',
        'home_description' => 'Consulta i dati tecnici omologati di qualsiasi veicolo immatricolato in Svizzera tramite il numero di approvazione del tipo (TG).',
    ],

    // ── Piè di pagina ──────────────────────────────────────────────────────
    'footer' => [
        'desc_line1' => 'Dati tecnici ufficiali ASTRA — Omologazioni svizzere.',
        'desc_line2' => 'Non affiliato a OFROU/ASTRA.',
        'legal'      => 'Note legali',
        'privacy'    => 'Privacy',
        'terms'      => 'Termini di utilizzo',
    ],

];
