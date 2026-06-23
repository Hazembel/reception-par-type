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

/**
 * Job : ImportAstraEmissionsJob
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Importe le fichier des émissions ASTRA (emissionen.txt) et COUPLE ses données
 * (CO2, norme antipollution, code émission) aux fiches techniques existantes via
 * le numéro de réception par type (numero_tg).
 *
 * Différence clé avec ImportAstraMainJob :
 *   - Ce job ne CRÉE jamais de véhicule. Il met uniquement à JOUR les fiches déjà
 *     présentes (résultat des imports TG). Une ligne d'émission dont le TG est
 *     inconnu est comptée comme "skipped" (le véhicule arrivera peut-être plus
 *     tard, ou la ligne est orpheline).
 *
 * Stratégie (identique aux autres imports, éprouvée pour les gros volumes) :
 *   1. Lecture ligne par ligne (fopen/fgets) — empreinte mémoire constante
 *   2. Traitement par chunks, chaque chunk en une transaction
 *   3. Mise à jour ciblée par numero_tg (CASE ... WHEN groupé, 1 requête/chunk)
 *   4. try/finally : fermeture du handle + suppression du fichier source GARANTIES
 *      même en cas d'exception (protection des 30 Go d'espace disque)
 *   5. Idempotence : hash SHA-256 du fichier (réimport du même fichier ignoré)
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Déclenchement :
 *   ImportAstraEmissionsJob::dispatch('/path/to/emissionen.txt')->onQueue('imports');
 */
class ImportAstraEmissionsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 7_200; // 2 heures
    public int $tries   = 2;
    public int $backoff = 300;

    private const CHUNK_SIZE        = 1_000;
    private const MAX_ERROR_DETAILS = 100;

    public function __construct(
        private readonly string $filePath,
        private readonly string $triggeredBy = 'scheduler',
        /**
         * Supprime le fichier source après traitement (succès OU échec).
         * Protège l'espace disque limité (30 Go).
         */
        private readonly bool $deleteAfterImport = true
    ) {}

    public function handle(): void
    {
        Log::channel('imports')->info('[EMISSIONS] Démarrage import émissions', [
            'file' => $this->filePath,
        ]);

        // ── Étape 1 : Vérifications préalables ────────────────────────────────
        if (!file_exists($this->filePath) || !is_readable($this->filePath)) {
            Log::channel('imports')->error('[EMISSIONS] Fichier inaccessible', ['path' => $this->filePath]);
            throw new \RuntimeException("Fichier émissions inaccessible : {$this->filePath}");
        }

        $fileHash = AstraFileParser::fileHash($this->filePath);
        $fileSize = filesize($this->filePath);
        $filename = basename($this->filePath);

        // ── Étape 2 : Idempotence (hash déjà importé ?) ───────────────────────
        $alreadyImported = ImportLog::where('file_hash', $fileHash)
            ->whereIn('status', ['completed', 'partial'])
            ->exists();

        if ($alreadyImported) {
            Log::channel('imports')->info('[EMISSIONS] Fichier déjà importé (hash identique), skip.', [
                'hash' => $fileHash,
            ]);
            return;
        }

        // ── Étape 3 : Log d'import ────────────────────────────────────────────
        $importLog = ImportLog::create([
            'import_type'     => 'emissions',
            'filename'        => $filename,
            'file_path'       => $this->filePath,
            'file_size_bytes' => $fileSize,
            'file_hash'       => $fileHash,
            'status'          => 'pending',
            'triggered_by'    => $this->triggeredBy,
        ]);
        $importLog->markAsRunning();

        // ── Étape 4 : Ouverture en streaming (try/finally garanti) ────────────
        $handle = fopen($this->filePath, 'r');
        if ($handle === false) {
            $importLog->markAsFailed("Impossible d'ouvrir le fichier en lecture.");
            $this->cleanupSourceFile();
            throw new \RuntimeException("fopen() a échoué pour : {$this->filePath}");
        }

        $stats = [
            'total_lines'    => 0,
            'lines_inserted' => 0, // toujours 0 ici : pas de création
            'lines_updated'  => 0,
            'lines_skipped'  => 0, // TG inconnu ou ligne sans donnée d'émission
            'lines_failed'   => 0,
        ];
        $errorDetails = [];

        try {
            // En-têtes
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                $importLog->markAsFailed('Fichier vide ou illisible.');
                return; // finally nettoiera
            }

            $firstLine = AstraFileParser::toUtf8($firstLine);
            $separator = AstraFileParser::detectSeparator($firstLine);
            $headers   = AstraFileParser::normalizeHeaders(
                str_getcsv(rtrim($firstLine, "\r\n"), $separator)
            );

            Log::channel('imports')->info('[EMISSIONS] En-têtes détectés', [
                'separator' => $separator === "\t" ? 'TAB' : ';',
                'columns'   => count($headers),
            ]);

            $chunk      = [];
            $lineNumber = 1;

            while (!feof($handle)) {
                $rawLine = fgets($handle);
                if ($rawLine === false) {
                    break;
                }

                $lineNumber++;
                $line = AstraFileParser::toUtf8(rtrim($rawLine, "\r\n"));

                if (empty(trim($line)) || str_starts_with($line, '#')) {
                    $stats['lines_skipped']++;
                    continue;
                }

                $values = str_getcsv($line, $separator);
                $parsed = AstraFileParser::parseEmissionLine($headers, $values);

                if ($parsed === null) {
                    // Ligne sans TG exploitable ou sans aucune donnée d'émission.
                    $stats['lines_failed']++;
                    if (count($errorDetails) < self::MAX_ERROR_DETAILS) {
                        $errorDetails[] = [
                            'line'  => $lineNumber,
                            'raw'   => mb_substr($line, 0, 100),
                            'error' => 'Ligne émission invalide (TG manquant ou aucune donnée)',
                        ];
                    }
                    continue;
                }

                $stats['total_lines']++;
                $chunk[] = $parsed;

                if (count($chunk) >= self::CHUNK_SIZE) {
                    $updatedInChunk          = $this->applyEmissionsChunk($chunk);
                    $stats['lines_updated'] += $updatedInChunk;
                    $stats['lines_skipped'] += count($chunk) - $updatedInChunk;
                    $chunk = [];
                    gc_collect_cycles();

                    if ($stats['total_lines'] % 50_000 === 0) {
                        Log::channel('imports')->info('[EMISSIONS] Progression', [
                            'lines'   => $stats['total_lines'],
                            'updated' => $stats['lines_updated'],
                            'memory'  => round(memory_get_usage(true) / 1_048_576) . ' Mo',
                        ]);
                        $this->job?->ping();
                    }
                }
            }

            // Dernier chunk (résidu)
            if (!empty($chunk)) {
                $updatedInChunk          = $this->applyEmissionsChunk($chunk);
                $stats['lines_updated'] += $updatedInChunk;
                $stats['lines_skipped'] += count($chunk) - $updatedInChunk;
            }

            // ── Finalisation ──────────────────────────────────────────────────
            $importLog->update([
                'total_lines'    => $stats['total_lines'],
                'lines_inserted' => 0,
                'lines_updated'  => $stats['lines_updated'],
                'lines_skipped'  => $stats['lines_skipped'],
                'lines_failed'   => $stats['lines_failed'],
                'error_details'  => !empty($errorDetails) ? $errorDetails : null,
            ]);
            $importLog->markAsFinished();

            Log::channel('imports')->info('[EMISSIONS] Import terminé', $stats);

        } catch (\Throwable $e) {
            $importLog->markAsFailed($e->getMessage(), $errorDetails);
            Log::channel('imports')->error('[EMISSIONS] Erreur critique', [
                'exception' => $e->getMessage(),
            ]);
            throw $e;
        } finally {
            // GARANTI : fermeture du handle + suppression du fichier source.
            if (is_resource($handle)) {
                fclose($handle);
            }
            $this->cleanupSourceFile();
        }
    }

    /**
     * Applique un chunk d'émissions aux véhicules existants, par numero_tg.
     *
     * On ne met à jour QUE les colonnes réellement présentes dans le fichier,
     * et UNIQUEMENT les véhicules déjà en base (jointure implicite via whereIn).
     * Les lignes dont le TG est inconnu sont ignorées (comptées comme skipped).
     *
     * @param  array<int, array<string, mixed>> $chunk
     * @return int  Nombre de véhicules mis à jour
     */
    private function applyEmissionsChunk(array $chunk): int
    {
        if (empty($chunk)) {
            return 0;
        }

        // Dé-doublonnage interne au chunk : si le même TG apparaît plusieurs fois,
        // la dernière occurrence l'emporte (merge des champs).
        $byTg = [];
        foreach ($chunk as $row) {
            $tg = $row['numero_tg'];
            $byTg[$tg] = array_merge($byTg[$tg] ?? [], $row);
        }

        $tgNumbers = array_keys($byTg);

        // Limiter aux véhicules réellement présents (évite des UPDATE inutiles).
        $existingTgs = Vehicle::whereIn('numero_tg', $tgNumbers)
            ->pluck('numero_tg')
            ->all();

        if (empty($existingTgs)) {
            return 0;
        }

        $updated = 0;

        DB::transaction(function () use ($existingTgs, $byTg, &$updated) {
            foreach ($existingTgs as $tg) {
                $data = $byTg[$tg];

                // Ne conserver que les colonnes d'émission présentes et non nulles.
                $payload = array_filter(
                    [
                        'co2'            => $data['co2']            ?? null,
                        'pollution_norm' => $data['pollution_norm'] ?? null,
                        'code_emissions' => $data['code_emissions'] ?? null,
                    ],
                    static fn ($v) => $v !== null
                );

                if (empty($payload)) {
                    continue;
                }

                $payload['updated_at'] = now();

                Vehicle::where('numero_tg', $tg)->update($payload);
                $updated++;
            }
        });

        return $updated;
    }

    /**
     * Supprime le fichier source du disque (best-effort, ne lève jamais).
     * Appelé depuis le finally : s'exécute même si l'import plante.
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
                    Log::channel('imports')->info('[EMISSIONS] Fichier source supprimé', [
                        'file'  => basename($this->filePath),
                        'freed' => $sizeMb . ' Mo',
                    ]);
                } else {
                    Log::channel('imports')->warning('[EMISSIONS] Échec suppression fichier source', [
                        'file' => $this->filePath,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::channel('imports')->warning('[EMISSIONS] Exception durant le nettoyage', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('imports')->critical('[EMISSIONS] Job échoué définitivement', [
            'file'      => $this->filePath,
            'exception' => $exception->getMessage(),
        ]);

        ImportLog::where('file_path', $this->filePath)
            ->where('status', 'running')
            ->first()
            ?->markAsFailed('Job échoué : ' . $exception->getMessage());
    }
}
