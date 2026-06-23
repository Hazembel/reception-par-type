<?php
/**
 * Übersetzungen DE — Modul 4 : Kontexthilfe, FAQ, Anleitung
 * resources/lang/de/help.php
 */
return [

    'tooltip' => [
        'numero_tg'         => 'Typengenehmigungsnummer (TG): Eindeutiger Bezeichner des ASTRA bei der Homologation eines Fahrzeugmodells in der Schweiz. Steht in <strong>Feld 24</strong> des Fahrzeugausweises.',
        'entraxe'           => 'Lochkreis (PCD): Abstand in mm zwischen den Mittelpunkten zweier gegenüberliegender Befestigungslöcher der Felge. Bsp: 5×112 = 5 Löcher auf einem Kreis von 112 mm.',
        'alesage'           => 'Nabenbohrung: Innendurchmesser der Zentralbohrung der Felge in mm. Muss exakt mit dem Nabendurchmesser des Fahrzeugs übereinstimmen.',
        'deport_et'         => 'Einpresstiefe (ET): Abstand in mm zwischen der Befestigungsebene der Felge und ihrer Mittelebene. Positives ET (z.B. +45) rückt die Felge näher zur Fahrzeugmitte.',
        'poids_remorquable' => 'Maximale Anhängelast (gebremster Anhänger): Das maximal erlaubte Gewicht eines Anhängers, den das Fahrzeug ziehen darf.',
        'poids_vide'        => 'Leergewicht des Fahrzeugs gemäss ASTRA-Homologation, einschliesslich aller Betriebsflüssigkeiten, ohne Insassen und Gepäck.',
        'poids_total'       => 'Gesamtgewicht (GVW): Maximal zulässiges Gesamtgewicht des beladenen Fahrzeugs (inkl. Insassen und Gepäck). Nicht überschreiten.',
        'co2'               => 'CO₂-Emissionen in g/km, gemessen nach WLTP (seit 2018) oder NEDC. Massgebend für die kantonale Fahrzeugsteuer.',
        'code_emissions'    => 'Europäische Abgasnorm (Euro 1–6). Bestimmt kantonale Steuern und Zugang zu Umweltzonen.',
        'puissance_kw'      => 'Motorleistung in Kilowatt (kW). 1 kW ≈ 1.36 PS. Korrekte Einheit gemäss Schweizer Zulassungsrecht.',
        'cylindree'         => 'Hubraum in cm³. Massgebend für die Fahrzeugbesteuerung in einigen Kantonen.',
        'boite_vitesse'     => 'Getriebetyp: M = Manuell, A = Automatik, CVT = stufenlos, DSG/PDK = Doppelkupplungsgetriebe.',
        'pneus_origine'     => 'Serienreifengrösse des Herstellers. Format: Breite/Höhe R Felgendurchmesser Tragfähigkeit Geschwindigkeitsindex (z.B. 205/55 R16 91H).',
        'nb_trous'          => 'Anzahl der Befestigungslöcher der Felge (meist 4 oder 5). Muss exakt mit der Anzahl der Radbolzen übereinstimmen.',
        'slug'              => 'Automatisch generierte eindeutige URL-Kennung für SEO. Abgeleitet von Marke, Modell und TG-Nummer.',
    ],

    'faq' => [
        'page_title'        => 'Häufig gestellte Fragen',
        'page_description'  => 'Alles Wissenswerte zu ASTRA-Daten, TG-Nummern, Felgen und unserem Service.',
        'search_placeholder'=> 'Frage suchen…',
        'no_results'        => 'Keine Frage entspricht Ihrer Suche.',
        'categories' => [
            'general' => 'Allgemein & TG-Nummer',
            'wheels'  => 'Reifen & Felgen',
            'pricing' => 'Preise & Token',
            'account' => 'Konto & API',
        ],
        'questions' => [
            ['cat' => 'general', 'question' => 'Was ist die Typengenehmigungsnummer (TG)?', 'answer' => 'Die Typengenehmigungsnummer (TG) ist der offizielle Bezeichner des ASTRA bei der Zulassung eines Fahrzeugtyps in der Schweiz. Sie steht in <strong>Feld 24</strong> des Fahrzeugausweises. Beispiel: <code>27.012.000.08.00004</code>.'],
            ['cat' => 'general', 'question' => 'Sind die Daten offiziell und aktuell?', 'answer' => 'Ja. Unsere Daten stammen direkt aus den <strong>TARGA-Dateien des ASTRA</strong>. Die Hauptdatei wird monatlich (am 10.) aktualisiert, neue Homologationen werden wöchentlich über den ASTRA-Newsletter integriert.'],
            ['cat' => 'general', 'question' => 'Kann ich mit einer vollständigen VIN suchen?', 'answer' => 'Ja. Sie können die vollständige 17-stellige VIN aus Ihrem Fahrzeugausweis eingeben – das System kürzt sie automatisch auf die ersten 9 Zeichen, um den Index effizient abzufragen.'],
            ['cat' => 'general', 'question' => 'Welche Fahrzeugtypen sind abgedeckt?', 'answer' => 'Personenwagen, Motorräder und Anhänger, die in der Schweiz typengenehmigt sind. Die Daten werden aus den separaten ASTRA-Dateien (TG-Automobil, TG-Moto, TG-Anhänger) sowie der Emissionsdatei importiert.'],
            ['cat' => 'wheels', 'question' => 'Wie lese ich die Felgendaten (Lochkreis, Nabenbohrung, ET)?', 'answer' => '<ul><li><strong>Lochkreis (PCD)</strong>: Anzahl Löcher × Durchmesser in mm (z.B. 5×112).</li><li><strong>Nabenbohrung</strong>: Zentralbohrung in mm (z.B. 57.1).</li><li><strong>Einpresstiefe ET</strong>: In mm, positiv oder negativ. Toleranz ca. ±10 mm, kantonale MFK-Vorschriften prüfen.</li></ul>'],
            ['cat' => 'wheels', 'question' => 'Sind die angezeigten Reifenmasse die einzigen zugelassenen?', 'answer' => 'Die ASTRA-Daten geben die <strong>Erstausrüstungsreifen</strong> (ab Werk montiert) an. Andere Grössen können über ein kantonales Gutachten separat zugelassen werden. Konsultieren Sie immer einen Fachmann oder das Strassenverkehrsamt Ihres Kantons, bevor Sie Reifen ausserhalb der Originalspezifikation montieren.'],
            ['cat' => 'wheels', 'question' => 'Mein Fahrzeug hat 4 Löcher, meine Felge 5 – ist das kompatibel?', 'answer' => 'Nein, die Anzahl der Befestigungslöcher muss zwischen Felge und Nabe <strong>strikt identisch</strong> sein. Es gibt keinen legalen Adapter, um in der Schweiz von 4 auf 5 Löcher zu wechseln. Eine solche Kombination führt zum Nichtbestehen der MFK und stellt eine ernste Gefahr für die Verkehrssicherheit dar.'],
            ['cat' => 'pricing', 'question' => 'Was ist ein "Web-Token" und wie funktioniert er?', 'answer' => 'Ein <strong>Web-Token</strong> ist eine Pay-as-you-go-Einheit für den Zugriff auf Premium-Daten ohne Monatsabo. 1 Token = 1 vollständige Fahrzeugabfrage oder 1 PDF-Export. Tokens sind in Paketen über Ihren persönlichen Bereich erhältlich und verfallen nie. Ideal für Gelegenheitsnutzer.'],
            ['cat' => 'pricing', 'question' => 'Sind die Basisdaten wirklich kostenlos?', 'answer' => 'Ja. Ohne Anmeldung kann jeder Nutzer <strong>10 Datenblätter pro Monat</strong> mit Motorisierungs- und Emissionsdaten abrufen. Massen- (Gewicht) und Felgen-/Reifendaten erfordern einen Token oder ein Abo ab dem Starter-Level (9 CHF/Monat). Die Erstellung eines kostenlosen Kontos erfolgt ohne Kreditkarte.'],
            ['cat' => 'pricing', 'question' => 'Gibt es ein Angebot für Garagisten und Profis?', 'answer' => 'Ja. Unsere Angebote <strong>Business (199 CHF/Monat)</strong> und <strong>Business+ (399 CHF/Monat)</strong> sind für Profis mit hohem Volumen (5 000 bis 15 000 Datenblätter/Monat), CSV-Massenexport und Mehrbenutzerverwaltung konzipiert. Für Bedarf >15 000 Datenblätter/Monat oder White-Label-Integration kontaktieren Sie uns für ein Enterprise-Angebot.'],
            ['cat' => 'account', 'question' => 'Wie funktioniert die REST-API?', 'answer' => 'Die REST-API ist ab dem <strong>Pro-Abo (49 CHF/Monat)</strong> verfügbar. Sie ermöglicht die Abfrage unserer Daten direkt aus Ihrer Anwendung über HTTP-Aufrufe. Authentifizierung über <strong>Bearer-Token (Laravel Sanctum)</strong>. Interaktive Dokumentation auf <code>/api/docs</code>. Jeder Aufruf verbraucht 1 API-Credit aus Ihrem Monatskontingent.'],
            ['cat' => 'account', 'question' => 'Sind meine persönlichen Daten geschützt?', 'answer' => 'Ja. reception-par-type.ch wird <strong>in der Schweiz gehostet</strong> und entspricht dem <strong>DSG (Bundesgesetz über den Datenschutz)</strong> sowie der europäischen DSGVO. Ihre Daten werden niemals an Dritte verkauft. Sie können die vollständige Löschung Ihres Kontos und Ihrer Daten jederzeit über Ihren persönlichen Bereich beantragen.'],
        ],
    ],

    'guide' => [
        'page_title'       => 'Benutzerhandbuch',
        'page_description' => 'Schritt-für-Schritt-Anleitung zur TG-Nummer, zum Verständnis der ASTRA-Daten und zur optimalen Nutzung von reception-par-type.ch.',
        'nav_title'        => 'In dieser Anleitung',
        'reading_time'     => 'Lesezeit: 4 Min.',
        'steps' => [
            ['id' => 'tg-finden',     'number' => '01', 'title' => 'TG-Nummer im Fahrzeugausweis finden', 'subtitle' => 'Feld 24 des schweizerischen Fahrzeugausweises'],
            ['id' => 'ergebnisse',    'number' => '02', 'title' => 'Ergebnisse verstehen und freischalten', 'subtitle' => 'Kostenlose vs. erweiterte Daten'],
            ['id' => 'export-api',    'number' => '03', 'title' => 'PDF-Bericht exportieren und API nutzen', 'subtitle' => 'Für Profis und Entwickler'],
        ],
        'step1' => [
            'body'          => 'Die Typengenehmigungsnummer (<strong>TG</strong>) steht auf dem schweizerischen Fahrzeugausweis in <strong>Feld 24</strong> ("Typengenehmigungsnummer"). Sie ist in 5 Gruppen gegliedert.',
            'format_title'  => 'Format der TG-Nummer',
            'format_example'=> '27.012.000.08.00004',
            'format_parts'  => [
                ['label' => '27', 'desc' => 'Ländercode (27 = Schweiz)'],
                ['label' => '012', 'desc' => 'Fahrzeugkategorie'],
                ['label' => '000', 'desc' => 'Herstellernummer'],
                ['label' => '08', 'desc' => 'Homologationsjahr'],
                ['label' => '00004', 'desc' => 'Variantennummer'],
            ],
            'tip'  => 'Tipp: Bei französischsprachigem Ausweis suchen Sie nach "N° de réception par type" oder "TG". Auf Italienisch: "N. di omologazione per tipo".',
            'note' => 'Ältere Fahrzeuge (vor 1995) können ein anderes Format haben. Unsere Suchmaschine akzeptiert normierte und ältere Formate.',
        ],
        'step2' => [
            'body'          => 'Die Suchergebnisse sind in thematischen Karten organisiert. Motorisierungs- und Emissionsdaten sind kostenlos zugänglich. Gewichts- und Felgendaten erfordern ein Abo oder einen Token.',
            'free_title'    => 'Kostenlose Daten',
            'premium_title' => 'Erweiterte Daten (Abo oder Token)',
            'free_items'    => ['Marke, Modell, Variante', 'Kraftstoffart & Motorisierung', 'Leistung (kW / PS)', 'Hubraum', 'Getriebe', 'CO₂ & Abgasnorm'],
            'premium_items' => ['Leergewicht & GVW', 'Anhängelast', 'Lochkreis, Nabenbohrung, ET', 'Serienbereifung', 'Offizieller PDF-Export', 'REST-API-Zugang'],
            'tip'           => 'Einzelfreischaltung (2 CHF / Fahrzeug) ohne Abo möglich. Kostenloses Konto erstellen und Token-Guthaben aufladen.',
        ],
        'step3' => [
            'body'        => 'Pro-Abonnenten und höher können jedes Datenblatt als offizielles PDF exportieren oder unsere REST-API in eigene Tools integrieren.',
            'pdf_title'   => 'PDF-Export',
            'pdf_steps'   => ['Fahrzeugdatenblatt öffnen', '"Als PDF exportieren" klicken (oben rechts)', 'PDF enthält alle homologierten Daten + ASTRA-Stempel', 'Verfügbar auf DE, FR, IT und EN'],
            'api_title'   => 'REST-API',
            'api_endpoint'=> 'GET /api/v1/vehicles/{tg_nummer}',
            'api_example' => "{\n  \"tg_nummer\": \"27.012.000.08.00004\",\n  \"marke\": \"Volkswagen\",\n  \"leistung_kw\": 110,\n  ...\n}",
            'api_tip'     => 'Ihr Bearer-API-Token finden Sie unter Konto → Einstellungen → API.',
        ],
    ],
];
