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
 * Job : ImportAstraNewsletterJob
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Traitement des fichiers de newsletter hebdomadaire ASTRA (Dossier 5000).
 *
 * Différences vs ImportAstraMainJob :
 *  - Fichiers légers (1-5 Mo, quelques centaines à quelques milliers de lignes)
 *  - Import INCRÉMENTIEL : uniquement les nouveaux véhicules homologués cette semaine
 *  - Pas besoin de chunking agressif : traitement en une seule passe optimisée
 *  - Timeout court : 10 minutes max
 *  - Peut traiter PLUSIEURS fichiers en une seule exécution (dossier 5000)
 *  - Vérification du répertoire pour trouver tous les fichiers non encore importés
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Déclenchement :
 *   ImportAstraNewsletterJob::dispatch('/storage/astra/5000/')
 *       ->onQueue('imports');
 */
class ImportAstraNewsletterJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    // ── Configuration ─────────────────────────────────────────────────────────

    public int $timeout = 600;   // 10 minutes
    public int $tries   = 3;
    public int $backoff = 60;    // 1 minute entre les tentatives

    /**
     * Pour la newsletter, on traite en un seul batch (fichiers petits).
     * Chunk plus large pour maximiser l'efficacité de upsert().
     */
    private const CHUNK_SIZE = 500;

    // ── Constructeur ──────────────────────────────────────────────────────────

    public function __construct(
        private readonly string $directory,      // Chemin du dossier 5000
        private readonly string $triggeredBy = 'scheduler',
        private readonly ?string $specificFile = null, // Pour forcer un fichier précis
        /**
         * Supprime chaque fichier newsletter après traitement (succès OU échec).
         * Évite l'accumulation sur le disque ; les fichiers traités avec succès
         * sont de toute façon protégés contre le retraitement par leur hash.
         */
        private readonly bool $deleteAfterImport = false
    ) {}

    // ── Exécution principale ──────────────────────────────────────────────────

    public function handle(): void
    {
        Log::channel('imports')->info('[5000] Démarrage import newsletter', [
            'directory' => $this->directory,
        ]);

        // ── Recherche des fichiers à traiter ──────────────────────────────────
        $filesToProcess = $this->findUnprocessedFiles();

        if (empty($filesToProcess)) {
            Log::channel('imports')->info('[5000] Aucun nouveau fichier à traiter.');
            return;
        }

        Log::channel('imports')->info('[5000] Fichiers à traiter', [
            'count' => count($filesToProcess),
            'files' => array_map('basename', $filesToProcess),
        ]);

        // ── Traitement séquentiel de chaque fichier newsletter ─────────────────
        $consecutiveFails = 0;
        $lastException    = null;

        foreach ($filesToProcess as $filePath) {
            try {
                $this->processNewsletterFile($filePath);
                $consecutiveFails = 0;
            } catch (\Throwable $e) {
                $consecutiveFails++;
                $lastException = $e;
            }
        }

        // Si TOUS les fichiers ont échoué, remonter l'exception pour que le job
        // soit marqué "failed" et que les alertes de supervision se déclenchent.
        if ($consecutiveFails === count($filesToProcess) && $lastException !== null) {
            throw $lastException;
        }
    }

    // ── Recherche des fichiers non encore importés ────────────────────────────

    /**
     * Retourne la liste des fichiers du dossier 5000 non encore importés.
     *
     * Critères d'un "fichier newsletter ASTRA" :
     *  - Extension .txt
     *  - Dans le répertoire configuré
     *  - Hash SHA-256 absent de imports_log (jamais importé avec succès)
     *
     * @return array<string> Chemins absolus des fichiers à traiter
     */
    private function findUnprocessedFiles(): array
    {
        // Fichier spécifique forcé (depuis l'interface admin)
        if ($this->specificFile !== null) {
            return file_exists($this->specificFile) ? [$this->specificFile] : [];
        }

        if (!is_dir($this->directory)) {
            Log::channel('imports')->warning('[5000] Répertoire introuvable', [
                'directory' => $this->directory,
            ]);
            return [];
        }

        // Récupération des hashes déjà importés avec succès
        $importedHashes = ImportLog::where('import_type', '5000')
            ->whereIn('status', ['completed', 'partial'])
            ->pluck('file_hash')
            ->filter()
            ->toArray();

        $files = glob($this->directory . '*.txt');
        if ($files === false) {
            return [];
        }

        // Tri par date de modification (les plus anciens en premier)
        usort($files, fn($a, $b) => filemtime($a) <=> filemtime($b));

        // Filtrage : exclure les fichiers déjà importés
        return array_filter($files, function (string $path) use ($importedHashes) {
            if (!is_readable($path)) {
                return false;
            }
            $hash = AstraFileParser::fileHash($path);
            return !in_array($hash, $importedHashes, true);
        });
    }

    // ── Traitement d'un fichier newsletter ────────────────────────────────────

    /**
     * Traite un seul fichier de newsletter en mode optimisé.
     * Les fichiers étant petits, on charge tout en mémoire (sécurisé ici).
     */
    private function processNewsletterFile(string $filePath): void
    {
        $filename = basename($filePath);
        $fileHash = AstraFileParser::fileHash($filePath);
        $fileSize = filesize($filePath);

        Log::channel('imports')->info("[5000] Traitement : {$filename}");

        // Création du log d'import
        $importLog = ImportLog::create([
            'import_type'    => '5000',
            'filename'       => $filename,
            'file_path'      => $filePath,
            'file_size_bytes'=> $fileSize,
            'file_hash'      => $fileHash,
            'status'         => 'pending',
            'triggered_by'   => $this->triggeredBy,
        ]);

        $importLog->markAsRunning();

        try {
            $result = $this->parseAndUpsert($filePath, $importLog);

            $importLog->update([
                'total_lines'    => $result['total'],
                'lines_inserted' => $result['inserted'],
                'lines_updated'  => $result['updated'],
                'lines_skipped'  => $result['skipped'],
                'lines_failed'   => $result['failed'],
            ]);
            $importLog->markAsFinished();

            Log::channel('imports')->info("[5000] {$filename} importé", $result);

        } catch (\Throwable $e) {
            $importLog->markAsFailed($e->getMessage());
            Log::channel('imports')->error("[5000] Échec {$filename}", [
                'error' => $e->getMessage(),
            ]);
            throw $e; // Remonté au handle() qui décide si c'est systémique
        } finally {
            // Suppression garantie du fichier traité (succès ou échec).
            $this->cleanupFile($filePath);
        }
    }

    /**
     * Supprime un fichier newsletter du disque (best-effort, ne lève jamais).
     */
    private function cleanupFile(string $filePath): void
    {
        if (!$this->deleteAfterImport) {
            return;
        }

        try {
            if (is_file($filePath)) {
                @unlink($filePath);
                Log::channel('imports')->info('[5000] Fichier supprimé', [
                    'file' => basename($filePath),
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('imports')->warning('[5000] Échec nettoyage', [
                'file'  => basename($filePath),
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ── Parsing et upsert optimisé ────────────────────────────────────────────

    /**
     * Parse le fichier et effectue l'upsert en chunks.
     *
     * @return array{total: int, inserted: int, updated: int, skipped: int, failed: int}
     */
    private function parseAndUpsert(string $filePath, ImportLog $importLog): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Impossible d'ouvrir : {$filePath}");
        }

        $stats = ['total' => 0, 'inserted' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

        try {
            // Lecture des en-têtes
            $firstLine = AstraFileParser::toUtf8(fgets($handle) ?: '');
            $separator = AstraFileParser::detectSeparator($firstLine);
            $headers   = AstraFileParser::normalizeHeaders(
                str_getcsv(rtrim($firstLine, "\r\n"), $separator)
            );

            $chunk = [];

            while (!feof($handle)) {
                $rawLine = fgets($handle);
                if ($rawLine === false) break;

                $line   = AstraFileParser::toUtf8(rtrim($rawLine, "\r\n"));
                if (empty(trim($line)) || str_starts_with($line, '#')) {
                    $stats['skipped']++;
                    continue;
                }

                $values = str_getcsv($line, $separator);
                $parsed = AstraFileParser::parseLine($headers, $values);

                if ($parsed === null) {
                    $stats['failed']++;
                    continue;
                }

                // Les fichiers newsletter 5000 couvrent les voitures par défaut.
                // Le type est nécessaire pour l'upsert (colonne NOT NULL avec défaut 'car').
                $parsed['vehicle_type'] = $parsed['vehicle_type'] ?? \App\Models\Vehicle::TYPE_CAR;

                $stats['total']++;
                $chunk[] = $parsed;

                if (count($chunk) >= self::CHUNK_SIZE) {
                    $chunkResult = $this->upsertChunk($chunk);
                    $stats['inserted'] += $chunkResult['inserted'];
                    $stats['updated']  += $chunkResult['updated'];
                    $chunk = [];
                }
            }

            // Dernier chunk
            if (!empty($chunk)) {
                $chunkResult = $this->upsertChunk($chunk);
                $stats['inserted'] += $chunkResult['inserted'];
                $stats['updated']  += $chunkResult['updated'];
            }
        } finally {
            // Fermeture du handle garantie même si l'upsert lève une exception.
            if (is_resource($handle)) {
                fclose($handle);
            }
        }

        return $stats;
    }

    /**
     * Upsert d'un chunk avec comptage précis des insertions vs mises à jour.
     *
     * @return array{inserted: int, updated: int}
     */
    private function upsertChunk(array $chunk): array
    {
        $tgNumbers   = array_column($chunk, 'numero_tg');
        $existingTgs = Vehicle::whereIn('numero_tg', $tgNumbers)
            ->pluck('numero_tg')
            ->flip()
            ->toArray();

        $rows = [];
        foreach ($chunk as $data) {
            $isNew = !isset($existingTgs[$data['numero_tg']]);

            if ($isNew && empty($data['slug'])) {
                $data['slug'] = Vehicle::slugFromData($data);
            }

            $data['is_active']   = true;
            $data['imported_at'] = now();
            $data['updated_at']  = now();

            if ($isNew) {
                $data['id']         = Str::ulid()->toBase32Lower();
                $data['created_at'] = now();
            }

            $rows[] = $data;
        }

        DB::transaction(function () use ($rows) {
            Vehicle::upsert($rows, ['numero_tg'], [
                'marque', 'modele', 'variante',
                'vin_prefix', 'eu_type_approval', 'vehicle_type',
                'energie', 'puissance_kw', 'cylindree', 'boite_vitesse',
                'poids_vide', 'poids_total', 'poids_remorquable',
                'co2', 'code_emissions', 'pollution_norm',
                'nb_trous', 'entraxe', 'alesage', 'deport_et', 'pneus_origine',
                'is_active', 'imported_at', 'updated_at',
            ]);
        });

        $inserted = count(array_filter($rows, fn($r) => !isset($existingTgs[$r['numero_tg']])));

        return ['inserted' => $inserted, 'updated' => count($rows) - $inserted];
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('imports')->critical('[5000] Job newsletter échoué définitivement', [
            'directory' => $this->directory,
            'exception' => $exception->getMessage(),
        ]);
    }
}
