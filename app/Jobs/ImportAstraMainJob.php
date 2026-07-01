<?php

namespace App\Jobs;

use App\Models\ImportLog;
use App\Models\Vehicle;
use App\Services\AstraFileParser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Job : ImportAstraMainJob
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Traitement du gros fichier mensuel ASTRA (Dossier 2000).
 * Fichier cible : TG-Automobil.txt (~300 Mo, ~400 000+ lignes)
 *
 * Stratégie anti-crash pour gros volumes :
 *  1. Lecture ligne par ligne via fopen/fgets (jamais file() ni file_get_contents)
 *  2. Traitement par chunks de 1 000 lignes en transaction BDD
 *  3. upsert() massif (INSERT ... ON DUPLICATE KEY UPDATE) : 10x plus rapide
 *  4. Libération mémoire explicite entre chaque chunk (gc_collect_cycles)
 *  5. Timeout Redis/Queue configuré généreusement (voir $timeout)
 *  6. Idempotence : vérification du hash SHA-256 avant de traiter
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Déclenchement :
 *   ImportAstraMainJob::dispatch('/path/to/TG-Automobil.txt')
 *       ->onQueue('imports');
 */
class ImportAstraMainJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    // ── Configuration du Job ──────────────────────────────────────────────────

    /**
     * Timeout en secondes (5h max pour le fichier complet de 300 Mo).
     * À aligner avec QUEUE_TIMEOUT dans .env et la config du worker Supervisor.
     */
    public int $timeout = 18_000; // 5 heures

    /**
     * Nombre de tentatives en cas d'échec.
     * On ne rejoue qu'une seule fois pour éviter les doublons massifs.
     */
    public int $tries = 2;

    /**
     * Délai entre les tentatives (en secondes).
     */
    public int $backoff = 300; // 5 minutes

    /**
     * Taille des chunks (lignes traitées par lot + transaction BDD).
     * 1 000 = bon équilibre mémoire/performance pour MySQL/PostgreSQL.
     */
    private const CHUNK_SIZE = 1_000;

    /**
     * Nombre max d'erreurs par ligne stockées dans imports_log.error_details.
     */
    private const MAX_ERROR_DETAILS = 100;

    // ── Constructeur ──────────────────────────────────────────────────────────

    public function __construct(
        private readonly string $filePath,
        private readonly string $triggeredBy = 'scheduler',
        /**
         * Supprime le fichier source après traitement (succès OU échec).
         * Indispensable sur une infra à espace disque limité (30 Go) : un
         * fichier TARGA de 300 Mo ne doit jamais rester sur le disque après
         * import. Mettre à false uniquement pour du débogage manuel.
         */
        private readonly bool $deleteAfterImport = false,
        /**
         * Type de véhicule porté par le fichier traité :
         *   - Vehicle::TYPE_CAR        pour TG-Automobil.txt
         *   - Vehicle::TYPE_MOTORCYCLE pour TG-Moto.txt
         * Toutes les lignes du fichier seront marquées avec ce type, ce qui
         * permet d'utiliser le MÊME job pour les deux fichiers sans duplication.
         */
        private readonly string $vehicleType = Vehicle::TYPE_CAR
    ) {
        // Garde-fou : on n'accepte que les types connus (sinon 'car' par défaut).
        if (!in_array($this->vehicleType, [Vehicle::TYPE_CAR, Vehicle::TYPE_MOTORCYCLE], true)) {
            throw new \InvalidArgumentException(
                "vehicleType invalide : {$this->vehicleType} (attendu : car | motorcycle)"
            );
        }
    }

    // ── Exécution principale ──────────────────────────────────────────────────

    public function handle(): void
    {
        Log::channel('imports')->info('[2000] Démarrage import principal', [
            'file' => $this->filePath,
        ]);

        // ── Étape 1 : Vérifications préalables ────────────────────────────────
        if (!file_exists($this->filePath) || !is_readable($this->filePath)) {
            Log::channel('imports')->error('[2000] Fichier inaccessible', ['path' => $this->filePath]);
            throw new \RuntimeException("Fichier ASTRA inaccessible : {$this->filePath}");
        }

        $fileHash = AstraFileParser::fileHash($this->filePath);
        $fileSize = filesize($this->filePath);
        $filename = basename($this->filePath);

        // ── Étape 2 : Idempotence — vérifier si ce fichier a déjà été importé ─
        $alreadyImported = ImportLog::where('file_hash', $fileHash)
            ->whereIn('status', ['completed', 'partial'])
            ->exists();

        if ($alreadyImported) {
            Log::channel('imports')->info('[2000] Fichier déjà importé (hash identique), skip.', [
                'hash' => $fileHash,
            ]);
            return;
        }

        // ── Étape 3 : Création de l'entrée de log ────────────────────────────
        $importLog = ImportLog::create([
            'import_type'    => $this->vehicleType === Vehicle::TYPE_MOTORCYCLE ? '2000-moto' : '2000',
            'filename'       => $filename,
            'file_path'      => $this->filePath,
            'file_size_bytes'=> $fileSize,
            'file_hash'      => $fileHash,
            'status'         => 'pending',
            'triggered_by'   => $this->triggeredBy,
        ]);

        $importLog->markAsRunning();

        // ── Étape 4 : Ouverture du fichier en streaming ───────────────────────
        // Tout le traitement est encapsulé dans try/finally : quoi qu'il arrive
        // (succès, exception, parsing d'en-tête qui échoue), le handle est fermé
        // ET le fichier source est supprimé du disque. Crucial sur 30 Go d'espace.
        $handle = fopen($this->filePath, 'r');
        if ($handle === false) {
            $importLog->markAsFailed("Impossible d'ouvrir le fichier en lecture.");
            // Le fichier est inutilisable : on tente quand même de le supprimer.
            $this->cleanupSourceFile();
            throw new \RuntimeException("fopen() a échoué pour : {$this->filePath}");
        }

        $stats = [
            'total_lines'    => 0,
            'lines_inserted' => 0,
            'lines_updated'  => 0,
            'lines_skipped'  => 0,
            'lines_failed'   => 0,
        ];
        $errorDetails = [];

        try {
            // ── Étape 5 : Lecture des en-têtes (première ligne) ───────────────
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                $importLog->markAsFailed('Fichier vide ou illisible.');
                return; // finally fermera le handle et supprimera le fichier
            }

            $firstLine = AstraFileParser::toUtf8($firstLine);
            $separator = AstraFileParser::detectSeparator($firstLine);
            $headers   = AstraFileParser::normalizeHeaders(
                str_getcsv(rtrim($firstLine, "\r\n"), $separator)
            );

            Log::channel('imports')->info('[2000] En-têtes détectés', [
                'separator' => $separator === "\t" ? 'TAB' : ';',
                'columns'   => count($headers),
                'sample'    => array_slice($headers, 0, 5),
            ]);

            // ── Étape 6 : Traitement par chunks ───────────────────────────────
            $chunk      = [];
            $lineNumber = 1; // La ligne 0 = en-têtes

            while (!feof($handle)) {
                $rawLine = fgets($handle);
                if ($rawLine === false) {
                    break;
                }

                $lineNumber++;
                $line = AstraFileParser::toUtf8(rtrim($rawLine, "\r\n"));

                // Ignorer les lignes vides et les commentaires
                if (empty(trim($line)) || str_starts_with($line, '#')) {
                    $stats['lines_skipped']++;
                    continue;
                }

                $values  = str_getcsv($line, $separator);
                $parsed  = AstraFileParser::parseLine($headers, $values);

                if ($parsed === null) {
                    $stats['lines_failed']++;
                    if (count($errorDetails) < self::MAX_ERROR_DETAILS) {
                        $errorDetails[] = [
                            'line'  => $lineNumber,
                            'raw'   => mb_substr($line, 0, 100),
                            'error' => 'Parsing échoué ou numéro TG manquant',
                        ];
                    }
                    continue;
                }

                $stats['total_lines']++;
                // Marquage du type de véhicule porté par ce fichier (car/moto).
                $parsed['vehicle_type'] = $this->vehicleType;
                $chunk[] = $parsed;

                // ── Traitement du chunk quand il atteint CHUNK_SIZE ────────────
                if (count($chunk) >= self::CHUNK_SIZE) {
                    $chunkStats = $this->processChunk($chunk);
                    $stats['lines_inserted'] += $chunkStats['inserted'];
                    $stats['lines_updated']  += $chunkStats['updated'];
                    $chunk = [];

                    // Libération mémoire explicite entre les chunks
                    gc_collect_cycles();

                    // Log de progression toutes les 50 000 lignes
                    if ($stats['total_lines'] % 50_000 === 0) {
                        Log::channel('imports')->info('[2000] Progression', [
                            'lines'    => $stats['total_lines'],
                            'inserted' => $stats['lines_inserted'],
                            'updated'  => $stats['lines_updated'],
                            'memory'   => round(memory_get_usage(true) / 1_048_576) . ' Mo',
                        ]);
                        // Heartbeat pour éviter le timeout du worker (Redis/Beanstalk uniquement)
                        if ($this->job && method_exists($this->job, 'ping')) {
                            $this->job->ping();
                        }
                    }
                }
            }

            // ── Traitement du dernier chunk (résidu) ──────────────────────────
            if (!empty($chunk)) {
                $chunkStats = $this->processChunk($chunk);
                $stats['lines_inserted'] += $chunkStats['inserted'];
                $stats['lines_updated']  += $chunkStats['updated'];
            }

            // ── Étape 7 : Finalisation ────────────────────────────────────────
            $importLog->update([
                'total_lines'    => $stats['total_lines'],
                'lines_inserted' => $stats['lines_inserted'],
                'lines_updated'  => $stats['lines_updated'],
                'lines_skipped'  => $stats['lines_skipped'],
                'lines_failed'   => $stats['lines_failed'],
                'error_details'  => !empty($errorDetails) ? $errorDetails : null,
            ]);
            $importLog->markAsFinished();

            Log::channel('imports')->info('[2000] Import terminé', array_merge(
                $stats,
                ['status' => $importLog->fresh()->status, 'summary' => $importLog->fresh()->summary]
            ));

        } catch (\Throwable $e) {
            $importLog->markAsFailed($e->getMessage(), $errorDetails);
            Log::channel('imports')->error('[2000] Erreur critique durant l\'import', [
                'exception' => $e->getMessage(),
            ]);
            throw $e;
        } finally {
            // GARANTI quoi qu'il arrive : fermeture du handle + suppression du fichier.
            if (is_resource($handle)) {
                fclose($handle);
            }
            $this->cleanupSourceFile();
        }
    }

    /**
     * Supprime le fichier source du disque (best-effort, ne lève jamais).
     * Appelé depuis le finally du handle() : s'exécute même si l'import plante,
     * empêchant l'accumulation de fichiers lourds sur un disque limité.
     */
    private function cleanupSourceFile(): void
    {
        if (!$this->deleteAfterImport) {
            return;
        }

        try {
            if (is_file($this->filePath)) {
                $sizeMb = round(@filesize($this->filePath) / 1_048_576, 1);
                if (@unlink($this->filePath)) {
                    Log::channel('imports')->info('[2000] Fichier source supprimé', [
                        'file'    => basename($this->filePath),
                        'freed'   => $sizeMb . ' Mo',
                    ]);
                } else {
                    Log::channel('imports')->warning('[2000] Échec suppression fichier source', [
                        'file' => $this->filePath,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Le nettoyage ne doit JAMAIS faire échouer le job ni masquer
            // l'exception d'origine remontée par le finally.
            Log::channel('imports')->warning('[2000] Exception durant le nettoyage', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ── Traitement d'un chunk ─────────────────────────────────────────────────

    /**
     * Insère ou met à jour un chunk de véhicules via upsert() massif.
     *
     * upsert() génère un seul INSERT ... ON DUPLICATE KEY UPDATE
     * pour tout le chunk → 10x plus rapide que updateOrCreate() en boucle.
     *
     * @param  array<int, array<string, mixed>> $chunk
     * @return array{inserted: int, updated: int}
     */
    private function processChunk(array $chunk): array
    {
        if (empty($chunk)) {
            return ['inserted' => 0, 'updated' => 0];
        }

        // Récupération des numéros TG déjà présents avec leur slug actuel
        $tgNumbers = array_column($chunk, 'numero_tg');

        // Keyed by numero_tg => slug (null if never set)
        $existingVehicles = Vehicle::whereIn('numero_tg', $tgNumbers)
            ->pluck('slug', 'numero_tg')
            ->toArray();

        // Enrichissement : génération du slug — TOUJOURS inclus pour que
        // upsert() ait les mêmes clés sur toutes les lignes (Laravel dérive
        // les colonnes INSERT depuis la première ligne du tableau).
        $preparedRows = [];
        foreach ($chunk as $data) {
            $isNew       = !array_key_exists($data['numero_tg'], $existingVehicles);
            $existingSlug = $existingVehicles[$data['numero_tg']] ?? null;

            // Nouveau ou existant sans slug → générer. Existant avec slug → conserver.
            $data['slug'] = ($isNew || empty($existingSlug))
                ? Vehicle::slugFromData($data)
                : $existingSlug;

            $data['is_active']    = true;
            $data['imported_at']  = now();
            $data['updated_at']   = now();

            if ($isNew) {
                $data['id']         = strtolower((string) Str::ulid());
                $data['created_at'] = now();
            }

            $preparedRows[] = $data;
        }

        // Colonnes à mettre à jour en cas de doublon (ON DUPLICATE KEY UPDATE)
        $updateColumns = [
            'marque', 'modele', 'variante',
            'vin_prefix', 'eu_type_approval', 'vehicle_type',
            'energie', 'puissance_kw', 'cylindree', 'boite_vitesse',
            'poids_vide', 'poids_total', 'poids_remorquable',
            'co2', 'code_emissions', 'pollution_norm',
            'nb_trous', 'entraxe', 'alesage', 'deport_et', 'pneus_origine',
            'is_active', 'imported_at', 'updated_at',
        ];

        DB::transaction(function () use ($preparedRows, $updateColumns) {
            Vehicle::upsert(
                $preparedRows,
                uniqueBy: ['numero_tg'], // Colonne unique pour détecter les doublons
                update: $updateColumns
            );
        });

        $inserted = count(array_filter($preparedRows, fn($r) => !array_key_exists($r['numero_tg'], $existingVehicles)));
        $updated  = count($preparedRows) - $inserted;

        return ['inserted' => $inserted, 'updated' => $updated];
    }

    // ── Gestion des échecs ────────────────────────────────────────────────────

    public function failed(\Throwable $exception): void
    {
        Log::channel('imports')->critical('[2000] Job échoué définitivement', [
            'file'      => $this->filePath,
            'exception' => $exception->getMessage(),
        ]);

        // Mise à jour du log si une entrée existe déjà (cas retry)
        ImportLog::where('file_path', $this->filePath)
            ->where('status', 'running')
            ->first()
            ?->markAsFailed('Job échoué : ' . $exception->getMessage());
    }
}
