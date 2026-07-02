<?php

return [
    'hero' => [
        'tagline' => 'Dati tecnici ufficiali ASTRA',
        'badge' => 'Fonte ufficiale ASTRA / USTRA · Sincronizzato mensilmente',
        'title' => 'Il numero TG della tua',
        'title_highlight' => 'licenza di circolazione',
        'title_suffix' => ', decodificato.',
        'subtitle' => 'Accedi istantaneamente ai dati tecnici ufficiali di qualsiasi veicolo omologato in Svizzera — peso, motore, cerchi, emissioni, imposte cantonali, PDF ufficiale.',
        'search_placeholder' => 'Numero TG, VIN, marca/modello…',
        'search_button' => 'Cerca',
        'stats' => [
            'vehicles' => 'Veicoli indicizzati',
            'brands' => 'Marchi coperti',
            'uptime' => 'Disponibilità SLA',
        ],
    ],
    'photo_band' => [
        'subtitle' => 'Dati ufficiali ASTRA',
        'title' => 'Per ogni veicolo che circola in Svizzera',
    ],
    'features' => [
        'badge' => 'I nostri punti di forza',
        'title' => 'Perché scegliere reception-par-type.ch?',
        'subtitle' => 'La piattaforma di riferimento per i professionisti del settore automobilistico, gli importatori e i servizi cantonali.',
        'items' => [
            [
                'title' => 'Istantaneo',
                'badge' => '< 200 ms',
                'desc' => 'Risultati in meno di 200 ms dal nostro database sincronizzato mensilmente con i file TARGA dell\'ASTRA.',
            ],
            [
                'title' => 'Fonte ufficiale',
                'badge' => 'ASTRA / USTRA',
                'desc' => 'Dati direttamente tratti dai file TG-Automobil, TG-Moto e emissionen.txt pubblicati dall\'Ufficio federale delle strade (USTRA).',
            ],
            [
                'title' => 'GDPR & LPD',
                'badge' => 'Hosting CH',
                'desc' => 'Hosted in Svizzera. Nessun dato personale rivenduto. Conforme alla Legge federale sulla protezione dei dati (LPD rivista 2023).',
            ],
            [
                'title' => 'Scheda PDF ufficiale',
                'badge' => 'Livelli 5+',
                'desc' => 'Genera con un clic una scheda di omologazione PDF completa con equivalenti europei (direttiva 1999/37/CE), pronta per le tue pratiche.',
            ],
            [
                'title' => 'API REST B2B',
                'badge' => 'Livelli 6-8',
                'desc' => 'Integra i nostri dati nei tuoi sistemi: ERP, configuratore, software di officina. Chiave API per cliente, quota mensile, 429 automatico.',
            ],
            [
                'title' => 'Imposte cantonali',
                'badge' => '12 cantoni',
                'desc' => 'Simula l\'imposta annuale sui veicoli in 12 cantoni svizzeri. Calcolo basato sui tariffari ufficiali (peso, potenza, CO₂).',
            ],
        ],
    ],
    'how_it_works' => [
        'image_annotation' => 'Casella 24 della licenza di circolazione',
        'badge' => 'Come funziona',
        'title' => 'Dal numero TG alla scheda completa in 3 secondi',
        'steps' => [
            [
                'title' => 'Trova il numero TG',
                'desc' => 'Si trova nella casella 24 della tua licenza di circolazione svizzera (es.: 27.012.000.08.00004). Puoi anche inserire un VIN completo — verrà automaticamente troncato a 9 caratteri per la ricerca.',
            ],
            [
                'title' => 'Ottieni la scheda tecnica',
                'desc' => 'Tara, massa totale, potenza kW/CV, cilindrata, carburante, misure pneumatici autorizzate, emissioni CO₂, codice Euro, norme antinquinamento.',
            ],
            [
                'title' => 'Esporta o integra',
                'desc' => 'Scarica la scheda PDF ufficiale o consuma i nostri dati tramite l\'API REST. Ogni dato riporta il suo riferimento di campo ASTRA e l\'equivalente EU (direttiva 1999/37/CE).',
            ],
        ],
    ],
    'api' => [
        'badge' => 'API REST B2B',
        'title' => 'Integra i nostri dati nei tuoi strumenti',
        'desc' => 'Chiave API per cliente, quota mensile adattata al tuo volume, risposte JSON strutturate. Le richieste senza risultato non vengono fatturate.',
        'endpoints' => [
            [
                'desc' => 'Scheda tecnica completa',
            ],
            [
                'desc' => 'Misure pneumatici + equivalenti ±8%',
            ],
            [
                'desc' => 'Scheda PDF ufficiale (livello 5+)',
            ],
            [
                'desc' => 'Imposta cantonale simulata',
            ],
        ],
        'docs_btn' => 'Vedi la documentazione',
        'keys_btn' => 'Le mie chiavi API →',
    ],
    'professionals' => [
        'badge' => 'Professionisti',
        'title' => 'Integra i dati ASTRA nel tuo software di officina',
    ],
    'official' => [
        'title' => 'Il riferimento per i professionisti dell\'automobile svizzera',
    ],
    'cta' => [
        'title' => 'Pronto per iniziare?',
        'desc' => 'Accesso gratuito per i dati di base. Piani professionali a partire da pochi franchi al mese.',
        'search_btn' => 'Cerca un veicolo →',
        'pricing_btn' => 'Vedi i prezzi',
    ],
];
