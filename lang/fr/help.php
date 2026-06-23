<?php
/**
 * Traductions FR — Module 4 : Aide contextuelle, FAQ, Guide
 * resources/lang/fr/help.php
 */
return [

    // ── Tooltips — termes techniques ────────────────────────────────────────
    'tooltip' => [
        'numero_tg'          => 'Numéro de Réception par Type (TG/Typengenehmigung). Identifiant unique attribué par l\'ASTRA lors de l\'homologation d\'un modèle en Suisse. Il figure à la case 24 de votre carte grise.',
        'entraxe'            => 'Distance en millimètres entre les centres de deux trous de fixation opposés de la jante. Aussi appelé "PCD" (Pitch Circle Diameter). Ex: 5×112 = 5 trous sur un cercle de 112 mm.',
        'alesage'            => 'Diamètre intérieur du moyeu central de la jante (en mm). Doit correspondre précisément au diamètre du moyeu du véhicule pour une fixation sûre sans bague de centrage.',
        'deport_et'          => 'Déport (Einpresstiefe) : distance en mm entre le plan de fixation de la jante et son plan médian. Un ET positif (ex: +45) rapproche la jante du centre. Un ET négatif écarte la jante vers l\'extérieur.',
        'poids_remorquable'  => 'Masse maximale autorisée de la remorque que le véhicule peut tracter en charge (remorque freinée). Ne pas confondre avec la charge utile du véhicule lui-même.',
        'poids_vide'         => 'Masse du véhicule à vide selon les données d\'homologation ASTRA, incluant les fluides (carburant, huile, liquide de refroidissement) mais sans passagers ni chargement.',
        'poids_total'        => 'Poids Maximum Autorisé (PMA) : masse maximale du véhicule en charge (véhicule + passagers + bagages). Ne pas dépasser sous peine de contravention.',
        'co2'                => 'Émissions de CO₂ en grammes par kilomètre, mesurées selon le cycle WLTP (depuis 2018) ou NEDC (avant 2018). Donnée utilisée pour calculer l\'impôt cantonal sur les véhicules.',
        'code_emissions'     => 'Norme européenne d\'émissions antipollution (Euro 1 à Euro 6). Détermine les taxes cantonales, les accès aux zones à faibles émissions et l\'éligibilité à certaines aides.',
        'puissance_kw'       => 'Puissance maximale du moteur exprimée en kilowatts (kW), unité légale en Suisse. 1 kW ≈ 1.36 CV (chevaux-vapeur). La puissance CV est donnée à titre indicatif.',
        'cylindree'          => 'Volume total balayé par tous les pistons du moteur en cm³. Utilisé pour le calcul de l\'imposition dans certains cantons suisses (ex: Valais, Fribourg).',
        'boite_vitesse'      => 'Type de transmission : M = Manuelle, A = Automatique classique, CVT = Transmission continue, DSG/PDK = Double embrayage automatisé.',
        'pneus_origine'      => 'Dimensions des pneumatiques montés en première monte par le constructeur. Format : Largeur/Hauteur R Diamètre Indice_de_charge Indice_de_vitesse (ex: 205/55 R16 91H).',
        'slug'               => 'Identifiant URL unique généré automatiquement pour le SEO. Dérivé de la marque, du modèle et du numéro TG.',
        'nb_trous'           => 'Nombre de trous de fixation de la jante (généralement 4 ou 5 pour les voitures de tourisme). Doit correspondre exactement au nombre de goujons sur le moyeu.',
    ],

    // ── FAQ ─────────────────────────────────────────────────────────────────
    'faq' => [
        'page_title'       => 'Questions fréquentes',
        'page_description' => 'Tout ce que vous devez savoir sur les données ASTRA, les numéros TG, les jantes, et notre service.',
        'search_placeholder'=> 'Rechercher une question…',
        'no_results'       => 'Aucune question ne correspond à votre recherche.',

        'categories' => [
            'general'  => 'Général & Numéro TG',
            'wheels'   => 'Pneus & Jantes',
            'pricing'  => 'Tarifs & Jetons',
            'account'  => 'Compte & API',
        ],

        'questions' => [
            // ── Général & TG ─────────────────────────────────────────────
            [
                'cat'      => 'general',
                'question' => "Qu'est-ce que le numéro de réception par type (TG) ?",
                'answer'   => "Le numéro de réception par type (TG, pour <em>Typengenehmigung</em> en allemand) est l'identifiant officiel attribué par l'ASTRA (Office fédéral des routes) lors de l'homologation d'un modèle de véhicule en Suisse. Il garantit que le véhicule répond aux normes techniques et légales helvétiques. Ce numéro figure à la <strong>case 24 de votre carte grise suisse</strong> (permis de circulation). Format exemple : <code>27.012.000.08.00004</code>.",
            ],
            [
                'cat'      => 'general',
                'question' => "Quelle est la différence entre le numéro TG et le numéro de châssis (VIN) ?",
                'answer'   => "Le <strong>numéro de châssis (VIN)</strong> est unique à chaque véhicule individuel (17 caractères alphanumériques). Le <strong>numéro TG</strong> s'applique à toute une famille de modèles partageant les mêmes caractéristiques techniques homologuées. Plusieurs milliers de véhicules identiques peuvent partager le même numéro TG. Notre service travaille exclusivement avec le numéro TG pour vous fournir les données d'homologation officielles.",
            ],
            [
                'cat'      => 'general',
                'question' => "Les données sont-elles officielles et à jour ?",
                'answer'   => "Oui. Nos données proviennent directement des fichiers TARGA publiés par l'<strong>ASTRA (OFROU)</strong>. Le fichier principal est synchronisé <strong>mensuellement</strong> (le 10 de chaque mois) et les nouvelles homologations sont intégrées <strong>chaque semaine</strong> via la newsletter ASTRA. Chaque fiche indique la date de la dernière synchronisation.",
            ],
            [
                'cat'      => 'general',
                'question' => "Puis-je chercher par marque ou par modèle sans connaître le numéro TG ?",
                'answer'   => "Absolument. Notre moteur de recherche accepte le numéro TG (entier ou partiel), la marque, le modèle, la variante ou une combinaison de ces critères. Les utilisateurs avec un abonnement Pro+ et supérieur bénéficient de filtres avancés (énergie, puissance, cylindrée, norme d'émissions).",
            ],
            // ── Pneus & Jantes ────────────────────────────────────────────
            [
                'cat'      => 'wheels',
                'question' => "Comment lire les données de jantes (entraxe, alésage, déport ET) ?",
                'answer'   => "<p>Les trois paramètres clés pour la compatibilité des jantes sont :</p><ul><li><strong>Entraxe (PCD)</strong> : nombre de trous × diamètre du cercle en mm (ex: 5×112). Doit correspondre exactement.</li><li><strong>Alésage</strong> : diamètre du trou central en mm (ex: 57.1). Une bague de centrage est utilisable si l'alésage de la jante est supérieur.</li><li><strong>Déport ET</strong> : en mm, peut être positif ou négatif. Une tolérance de ±10 mm est généralement acceptée, mais vérifiez impérativement la réglementation du contrôle technique cantonal.</li></ul>",
            ],
            [
                'cat'      => 'wheels',
                'question' => "Les dimensions de pneus affichées sont-elles les seules homologuées ?",
                'answer'   => "Les données ASTRA indiquent les pneumatiques de <strong>première monte</strong> (montés en usine). D'autres tailles peuvent être homologuées séparément via une expertise cantonale. Consultez toujours un professionnel ou le service des automobiles de votre canton avant de monter des pneumatiques hors spécifications d'origine.",
            ],
            [
                'cat'      => 'wheels',
                'question' => "Mon véhicule a 4 trous mais ma jante en a 5, est-ce compatible ?",
                'answer'   => "Non, le nombre de trous de fixation doit être <strong>strictement identique</strong> entre la jante et le moyeu. Il n'existe aucun adaptateur légal pour passer de 4 à 5 trous en Suisse. Le montage d'une telle combinaison entraînerait l'échec du contrôle technique et constitue un danger grave pour la sécurité routière.",
            ],
            // ── Tarifs & Jetons ───────────────────────────────────────────
            [
                'cat'      => 'pricing',
                'question' => "Qu'est-ce qu'un 'jeton web' et comment fonctionne-t-il ?",
                'answer'   => "Les <strong>jetons web (web tokens)</strong> sont une unité de paiement à l'unité (pay-as-you-go) pour accéder aux données premium sans abonnement mensuel. 1 jeton = 1 consultation complète de fiche, ou 1 export PDF. Les jetons sont achetables par packs depuis votre espace personnel et n'expirent jamais. C'est idéal pour les utilisateurs occasionnels (ex: un garagiste qui vérifie une fiche 2-3 fois par mois).",
            ],
            [
                'cat'      => 'pricing',
                'question' => "Les données de base sont-elles vraiment gratuites ?",
                'answer'   => "Oui. Sans inscription, chaque utilisateur peut consulter <strong>10 fiches par mois</strong> avec les données de motorisation et d'émissions. Les données de masse (poids) et de jantes/pneumatiques nécessitent un jeton ou un abonnement à partir du niveau Starter (9 CHF/mois). La création d'un compte gratuit est sans carte bancaire.",
            ],
            [
                'cat'      => 'pricing',
                'question' => "Y a-t-il une offre pour les garagistes et professionnels ?",
                'answer'   => "Oui. Nos offres <strong>Business (199 CHF/mois)</strong> et <strong>Business+ (399 CHF/mois)</strong> sont conçues pour les professionnels avec des volumes élevés (5 000 à 15 000 fiches/mois), l'accès à l'export CSV en masse, et la gestion multi-utilisateurs. Pour les besoins >15 000 fiches/mois ou une intégration en marque blanche, contactez-nous pour un devis Entreprise.",
            ],
            // ── Compte & API ──────────────────────────────────────────────
            [
                'cat'      => 'account',
                'question' => "Comment fonctionne l'API REST ?",
                'answer'   => "L'API REST est disponible dès le niveau <strong>Pro (49 CHF/mois)</strong>. Elle permet d'interroger nos données directement depuis votre application via des appels HTTP. Authentification via <strong>Bearer Token (Laravel Sanctum)</strong>. Documentation interactive disponible sur <code>/api/docs</code>. Chaque appel consomme 1 crédit API inclus dans votre quota mensuel.",
            ],
            [
                'cat'      => 'account',
                'question' => "Mes données personnelles sont-elles protégées ?",
                'answer'   => "Oui. reception-par-type.ch est <strong>hébergé en Suisse</strong> et conforme à la <strong>LPD (Loi fédérale sur la protection des données)</strong> et au RGPD européen. Vos données ne sont jamais vendues à des tiers. Vous pouvez demander la suppression complète de votre compte et de vos données à tout moment depuis votre espace personnel.",
            ],
        ],
    ],

    // ── Guide ────────────────────────────────────────────────────────────────
    'guide' => [
        'page_title'       => 'Mode d\'emploi',
        'page_description' => 'Guide complet pour trouver votre numéro TG, comprendre les données ASTRA et tirer le meilleur parti de reception-par-type.ch.',
        'nav_title'        => 'Dans ce guide',
        'reading_time'     => 'Lecture : 4 min',

        'steps' => [
            [
                'id'       => 'trouver-tg',
                'number'   => '01',
                'title'    => 'Trouver le numéro TG sur votre carte grise',
                'subtitle' => 'Case 24 du permis de circulation suisse',
            ],
            [
                'id'       => 'interpreter-resultats',
                'number'   => '02',
                'title'    => 'Comprendre et débloquer les résultats',
                'subtitle' => 'Données gratuites vs. données avancées',
            ],
            [
                'id'       => 'exporter-api',
                'number'   => '03',
                'title'    => 'Exporter le rapport PDF et utiliser l\'API',
                'subtitle' => 'Pour les professionnels et développeurs',
            ],
        ],

        'step1' => [
            'body'    => 'Le numéro de réception par type (<strong>TG</strong>) est imprimé directement sur votre permis de circulation (carte grise) suisse. Il se trouve à la <strong>case 24</strong>, intitulée "N° de réception par type". Ce numéro est structuré en 5 groupes séparés par des points.',
            'format_title' => 'Format du numéro TG',
            'format_example' => '27.012.000.08.00004',
            'format_parts' => [
                ['label' => '27',    'desc' => 'Code pays (27 = Suisse)'],
                ['label' => '012',   'desc' => 'Catégorie de véhicule'],
                ['label' => '000',   'desc' => 'Numéro du constructeur'],
                ['label' => '08',    'desc' => 'Année de l\'homologation'],
                ['label' => '00004', 'desc' => 'Numéro de la variante'],
            ],
            'tip' => 'Astuce : si votre carte grise est en allemand, cherchez "Typengenehmigungsnummer" ou l\'abréviation "TG". En italien : "N. di omologazione per tipo".',
            'note' => 'Certains véhicules anciens (avant 1995) peuvent avoir un format différent. Notre moteur de recherche accepte les formats normalisés et anciens.',
        ],

        'step2' => [
            'body'   => 'Une fois votre recherche effectuée, les résultats sont organisés en cartes thématiques. Les données de motorisation et d\'émissions sont accessibles gratuitement. Les données de masse et de jantes/pneumatiques sont réservées aux abonnés ou débloquables à l\'unité.',
            'free_title'    => 'Données gratuites (sans inscription)',
            'premium_title' => 'Données avancées (abonnement ou jeton)',
            'free_items'    => ['Marque, modèle, variante', 'Type d\'énergie et motorisation', 'Puissance (kW / CV)', 'Cylindrée', 'Boîte de vitesses', 'Émissions CO₂ et norme Euro'],
            'premium_items' => ['Poids à vide et PMA', 'Charge remorquable', 'Entraxe, alésage, déport ET', 'Pneumatiques d\'origine', 'Export PDF officiel', 'Accès API REST'],
            'tip' => 'Le déblocage à l\'unité (2 CHF / fiche) ne nécessite pas d\'abonnement. Créez simplement un compte gratuit et rechargez votre solde de jetons.',
        ],

        'step3' => [
            'body'   => 'Les abonnés Pro et supérieurs peuvent exporter chaque fiche au format PDF officiel, ou interroger notre base de données via l\'API REST pour l\'intégrer dans leurs propres outils (logiciels de garage, ERP, applications mobiles).',
            'pdf_title' => 'Export PDF',
            'pdf_steps' => [
                'Ouvrez la fiche du véhicule souhaité',
                'Cliquez sur "Exporter en PDF" (bouton en haut à droite)',
                'Le PDF inclut toutes les données homologuées + le tampon ASTRA',
                'Disponible en FR, DE, IT et EN',
            ],
            'api_title'   => 'API REST',
            'api_endpoint'=> 'GET /api/v1/vehicles/{numero_tg}',
            'api_example' => '{
  "numero_tg": "27.012.000.08.00004",
  "marque": "Volkswagen",
  "modele": "Golf",
  "puissance_kw": 110,
  "co2": 128,
  ...
}',
            'api_tip' => 'Votre clé API Bearer Token est disponible dans Compte → Paramètres → API. La documentation interactive est accessible sur /api/docs.',
        ],
    ],
];
