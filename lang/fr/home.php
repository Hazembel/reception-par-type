<?php

return [
    'hero' => [
        'tagline' => 'Données techniques ASTRA officielles',
        'badge' => 'Source officielle OFROU / ASTRA · Synchronisé mensuellement',
        'title' => 'Le numéro TG de votre',
        'title_highlight' => 'carte grise',
        'title_suffix' => ', décodé.',
        'subtitle' => 'Accédez instantanément aux données techniques officielles de tout véhicule homologué en Suisse — poids, moteur, jantes, émissions, impôts cantonaux, fiche PDF.',
        'search_placeholder' => 'Numéro TG, VIN, marque/modèle…',
        'search_button' => 'Rechercher',
        'stats' => [
            'vehicles' => 'Véhicules indexés',
            'brands' => 'Marques couvertes',
            'uptime' => 'Disponibilité SLA',
        ],
    ],
    'photo_band' => [
        'subtitle' => 'Données officielles OFROU',
        'title' => 'Pour chaque véhicule circulant en Suisse',
    ],
    'features' => [
        'badge' => 'Nos atouts',
        'title' => 'Pourquoi choisir reception-par-type.ch ?',
        'subtitle' => 'La plateforme de référence pour les professionnels de l\'automobile, les importateurs et les services cantonaux.',
        'items' => [
            [
                'title' => 'Instantané',
                'badge' => '< 200 ms',
                'desc' => 'Résultats en moins de 200 ms depuis notre base de données synchronisée mensuellement avec les fichiers TARGA de l\'ASTRA.',
            ],
            [
                'title' => 'Source officielle',
                'badge' => 'ASTRA / OFROU',
                'desc' => 'Données directement issues des fichiers TG-Automobil, TG-Moto et emissionen.txt publiés par l\'Office fédéral des routes (OFROU).',
            ],
            [
                'title' => 'RGPD & LPD',
                'badge' => 'Hébergé CH',
                'desc' => 'Hébergé en Suisse. Aucune donnée personnelle revendue. Conforme à la Loi fédérale sur la protection des données (LPD révisée 2023).',
            ],
            [
                'title' => 'Fiche PDF officielle',
                'badge' => 'Niveaux 5+',
                'desc' => 'Générez en un clic une fiche d\'homologation PDF complète avec correspondances européennes (directive 1999/37/CE), prête pour vos démarches.',
            ],
            [
                'title' => 'API REST B2B',
                'badge' => 'Niveaux 6-8',
                'desc' => 'Intégrez nos données dans vos systèmes : ERP, configurateur, logiciel d\'atelier. Clé API par client, quota mensuel, 429 automatique.',
            ],
            [
                'title' => 'Taxes cantonales',
                'badge' => '12 cantons',
                'desc' => 'Simulez l\'impôt annuel sur les véhicules dans 12 cantons suisses. Calcul basé sur les barèmes officiels (poids, puissance, CO₂).',
            ],
        ],
    ],
    'how_it_works' => [
        'image_annotation' => 'Case 24 du permis de circulation',
        'badge' => 'Comment ça marche',
        'title' => 'Du numéro TG à la fiche complète en 3 secondes',
        'steps' => [
            [
                'title' => 'Trouvez le numéro TG',
                'desc' => 'Il se trouve en case 24 de votre permis de circulation suisse (ex : 27.012.000.08.00004). Vous pouvez aussi saisir un VIN complet — il sera automatiquement tronqué à 9 caractères pour la recherche.',
            ],
            [
                'title' => 'Obtenez la fiche technique',
                'desc' => 'Masse à vide, PMA, puissance kW/CV, cylindrée, carburant, montes pneumatiques autorisées, émissions CO₂, code Euro, normes antipollution.',
            ],
            [
                'title' => 'Exportez ou intégrez',
                'desc' => 'Téléchargez la fiche PDF officielle ou consommez nos données via l\'API REST. Chaque donnée porte sa référence de champ ASTRA et son équivalent EU (directive 1999/37/CE).',
            ],
        ],
    ],
    'api' => [
        'badge' => 'API REST B2B',
        'title' => 'Intégrez nos données dans vos outils',
        'desc' => 'Clé API par client, quota mensuel adapté à votre volume, réponses JSON structurées. Les requêtes sans résultat ne sont pas facturées.',
        'endpoints' => [
            [
                'desc' => 'Fiche technique complète',
            ],
            [
                'desc' => 'Montes pneumatiques + équivalences ±8%',
            ],
            [
                'desc' => 'Fiche PDF officielle (niveau 5+)',
            ],
            [
                'desc' => 'Impôt cantonal simulé',
            ],
        ],
        'docs_btn' => 'Voir la documentation',
        'keys_btn' => 'Mes clés API →',
    ],
    'professionals' => [
        'badge' => 'Professionnels',
        'title' => 'Intégrez les données ASTRA dans votre logiciel d\'atelier',
    ],
    'official' => [
        'title' => 'La référence pour les professionnels de l\'automobile suisse',
    ],
    'cta' => [
        'title' => 'Prêt à démarrer ?',
        'desc' => 'Accès gratuit pour les données de base. Plans professionnels à partir de quelques francs par mois.',
        'search_btn' => 'Rechercher un véhicule →',
        'pricing_btn' => 'Voir les tarifs',
    ],
];
