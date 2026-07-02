<?php

return [
    'hero' => [
        'tagline' => 'Offizielle technische ASTRA-Daten',
        'badge' => 'Offizielle Quelle ASTRA / OFROU · Monatlich synchronisiert',
        'title' => 'Die TG-Nummer Ihres',
        'title_highlight' => 'Fahrzeugausweises',
        'title_suffix' => ', entschlüsselt.',
        'subtitle' => 'Greifen Sie sofort auf die offiziellen technischen Daten jedes in der Schweiz zugelassenen Fahrzeugs zu — Gewicht, Motor, Felgen, Emissionen, Kantonssteuern, offizielles PDF.',
        'search_placeholder' => 'TG-Nummer, VIN, Marke/Modell…',
        'search_button' => 'Suchen',
        'stats' => [
            'vehicles' => 'Indexierte Fahrzeuge',
            'brands' => 'Abgedeckte Marken',
            'uptime' => 'SLA-Verfügbarkeit',
        ],
    ],
    'photo_band' => [
        'subtitle' => 'Offizielle ASTRA-Daten',
        'title' => 'Für jedes Fahrzeug auf Schweizer Strassen',
    ],
    'features' => [
        'badge' => 'Unsere Stärken',
        'title' => 'Warum reception-par-type.ch wählen?',
        'subtitle' => 'Die Referenzplattform für Automobilfachleute, Importeure und kantonale Dienste.',
        'items' => [
            [
                'title' => 'Sofortabfrage',
                'badge' => '< 200 ms',
                'desc' => 'Ergebnisse in weniger als 200 ms aus unserer Datenbank, die monatlich mit den ASTRA TARGA-Dateien synchronisiert wird.',
            ],
            [
                'title' => 'Offizielle Quelle',
                'badge' => 'ASTRA / OFROU',
                'desc' => 'Daten direkt aus den TG-Automobil-, TG-Moto- und emissionen.txt-Dateien des Bundesamts für Strassen (ASTRA/OFROU).',
            ],
            [
                'title' => 'DSGVO & DSG',
                'badge' => 'Hosting CH',
                'desc' => 'Hosting in der Schweiz. Keine Weitergabe persönlicher Daten. Konform mit dem revidierten Bundesgesetz über den Datenschutz (DSG 2023).',
            ],
            [
                'title' => 'Offizielles PDF-Datenblatt',
                'badge' => 'Stufen 5+',
                'desc' => 'Erstellen Sie per Klick ein vollständiges Homologations-PDF mit europäischen Entsprechungen (Richtlinie 1999/37/EG), bereit für Ihre Behördengänge.',
            ],
            [
                'title' => 'B2B REST-API',
                'badge' => 'Stufen 6-8',
                'desc' => 'Integrieren Sie unsere Daten in Ihre Systeme: ERP, Konfigurator, Werkstattsoftware. API-Schlüssel pro Kunde, monatliches Kontingent, automatischer 429.',
            ],
            [
                'title' => 'Kantonssteuern',
                'badge' => '12 Kantone',
                'desc' => 'Simulieren Sie die jährliche Fahrzeugsteuer in 12 Schweizer Kantonen. Berechnung auf Basis offizieller Tarife (Gewicht, Leistung, CO₂).',
            ],
        ],
    ],
    'how_it_works' => [
        'image_annotation' => 'Feld 24 des Fahrzeugausweises',
        'badge' => 'So funktioniert\'s',
        'title' => 'Von der TG-Nummer zum vollständigen Datenblatt in 3 Sekunden',
        'steps' => [
            [
                'title' => 'TG-Nummer finden',
                'desc' => 'Sie befindet sich in Feld 24 Ihres Schweizer Fahrzeugausweises (z. B. 27.012.000.08.00004). Sie können auch eine vollständige VIN eingeben — diese wird automatisch auf 9 Zeichen für die Suche gekürzt.',
            ],
            [
                'title' => 'Technisches Datenblatt erhalten',
                'desc' => 'Leergewicht, Gesamtgewicht, Leistung kW/PS, Hubraum, Kraftstoff, zugelassene Reifengrößen, CO₂-Emissionen, Euro-Code, Abgasnormen.',
            ],
            [
                'title' => 'Exportieren oder integrieren',
                'desc' => 'Laden Sie das offizielle PDF-Datenblatt herunter oder nutzen Sie unsere Daten über die REST-API. Jeder Datenpunkt trägt seine ASTRA-Feldreferenz und sein EU-Äquivalent (Richtlinie 1999/37/EG).',
            ],
        ],
    ],
    'api' => [
        'badge' => 'B2B REST-API',
        'title' => 'Unsere Daten in Ihre Werkzeuge integrieren',
        'desc' => 'API-Schlüssel pro Kunde, monatliches Kontingent passend zu Ihrem Volumen, strukturierte JSON-Antworten. Anfragen ohne Ergebnis werden nicht berechnet.',
        'endpoints' => [
            [
                'desc' => 'Vollständiges technisches Datenblatt',
            ],
            [
                'desc' => 'Reifengrößen + Äquivalente ±8 %',
            ],
            [
                'desc' => 'Offizielles PDF-Datenblatt (Stufe 5+)',
            ],
            [
                'desc' => 'Simulierte Kantonssteuer',
            ],
        ],
        'docs_btn' => 'Dokumentation ansehen',
        'keys_btn' => 'Meine API-Schlüssel →',
    ],
    'professionals' => [
        'badge' => 'Fachleute',
        'title' => 'ASTRA-Daten in Ihre Werkstattsoftware integrieren',
    ],
    'official' => [
        'title' => 'Die Referenz für Schweizer Automobilfachleute',
    ],
    'cta' => [
        'title' => 'Bereit loszulegen?',
        'desc' => 'Kostenloser Zugang für Grunddaten. Professionelle Pläne ab wenigen Franken pro Monat.',
        'search_btn' => 'Fahrzeug suchen →',
        'pricing_btn' => 'Preise ansehen',
    ],
];
