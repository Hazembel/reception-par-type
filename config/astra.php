<?php

/**
 * Configuration : Chemins et paramètres des fichiers ASTRA
 *
 * Ces valeurs peuvent être surchargées via les variables d'environnement .env.
 * Priorité : .env → config/astra.php → valeur par défaut
 *
 * Variables .env associées :
 *   ASTRA_PATH_2000=/storage/app/astra/2000/
 *   ASTRA_PATH_5000=/storage/app/astra/5000/
 *   ASTRA_MAIN_FILE=/storage/app/astra/2000/TG-Automobil.txt
 *   ASTRA_ENCODING=ISO-8859-1
 *   ASTRA_CHUNK_SIZE=1000
 */
return [

    // ── Chemins des fichiers ASTRA ────────────────────────────────────────────
    'paths' => [
        /**
         * Dossier 2000 : Fichier principal mensuel (~300 Mo)
         * Contient l'ensemble des véhicules homologués en Suisse.
         */
        '2000' => env('ASTRA_PATH_2000', storage_path('app/astra/2000/')),

        /**
         * Dossier 5000 : Newsletter hebdomadaire (1-5 Mo)
         * Contient uniquement les nouvelles homologations de la semaine.
         */
        '5000' => env('ASTRA_PATH_5000', storage_path('app/astra/5000/')),

        /**
         * Fichier principal (chemin complet avec nom de fichier).
         * Par convention ASTRA, le fichier s'appelle toujours TG-Automobil.txt.
         */
        'main_file' => env(
            'ASTRA_MAIN_FILE',
            storage_path('app/astra/2000/TG-Automobil.txt')
        ),

        /**
         * Fichier des motos (même format que TG-Automobil.txt).
         * Traité par ImportAstraMainJob avec vehicle_type = motorcycle.
         */
        'moto_file' => env(
            'ASTRA_MOTO_FILE',
            storage_path('app/astra/2000/TG-Moto.txt')
        ),

        /**
         * Fichier des émissions, couplé aux fiches TG par numéro de réception.
         * Traité par ImportAstraEmissionsJob (UPDATE des véhicules existants).
         */
        'emissions_file' => env(
            'ASTRA_EMISSIONS_FILE',
            storage_path('app/astra/2000/emissionen.txt')
        ),
    ],

    // ── Paramètres d'import ───────────────────────────────────────────────────
    'import' => [
        /**
         * Encodage des fichiers ASTRA (ISO-8859-1 = Latin-1, parfois Windows-1252).
         * Tous les fichiers sont convertis en UTF-8 avant traitement.
         */
        'encoding' => env('ASTRA_ENCODING', 'ISO-8859-1'),

        /**
         * Taille des chunks pour l'import principal (ImportAstraMainJob).
         * Augmenter si la RAM du serveur le permet (> 8 Go → 2000 lignes OK).
         */
        'chunk_size' => (int) env('ASTRA_CHUNK_SIZE', 1_000),

        /**
         * Seuil d'erreur pour passer de 'partial' à 'failed' (en pourcentage).
         * 5 = si > 5% de lignes en erreur → import marqué 'failed'
         */
        'error_threshold_percent' => 5,

        /**
         * Nombre maximum de détails d'erreur stockés dans imports_log.error_details.
         */
        'max_error_details' => 100,
    ],

    // ── Queues ────────────────────────────────────────────────────────────────
    'queues' => [
        /**
         * Queue pour les imports légers (newsletter).
         * Traitement rapide, priorité normale.
         */
        'newsletter' => env('ASTRA_QUEUE_NEWSLETTER', 'imports'),

        /**
         * Queue pour les imports lourds (fichier principal 300 Mo).
         * Priorité basse, timeout long, worker dédié recommandé.
         */
        'main' => env('ASTRA_QUEUE_MAIN', 'imports-heavy'),
    ],

    // ── Logging ───────────────────────────────────────────────────────────────
    'logging' => [
        /**
         * Canal de log dédié aux imports (config/logging.php).
         * Recommandé : canal 'daily' avec retention 30 jours.
         */
        'channel' => env('ASTRA_LOG_CHANNEL', 'imports'),
    ],

];
