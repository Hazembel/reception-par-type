<?php

namespace App\Jobs;

use App\Models\ImportLog;
use App\Services\AstraFileParser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job : ImportAstraVerbrauchJob
 *
 * Importe verbrauch.txt (consommation / Verbrauch) et met à jour les véhicules
 * existants avec les données de consommation NEDC et WLTP.
 * Ne crée jamais de véhicule — UPDATE uniquement par numero_tg.
 */
class ImportAstraVerbrauchJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 3_600;
    public int $tries   = 2;
    public int $backoff = 300;

    private const CHUNK_SIZE        = 1_000;
    private const MAX_ERROR_DETAILS = 100;

    public function __construct(
        private readonly string $filePath,
        private readonly string $triggeredBy = 'manual',
        private readonly bool   $deleteAfterImport = false
    ) {}

    public function handle(): void
    {
        Log::channel('imports')->info('[VERBRAUCH] Démarrage import consommation', [
            'file' => $this->filePath,
        ]);

        if (!file_exists($this->filePath) || !is_readable($this->filePath)) {
            throw new \RuntimeException("Fichier verbrauch inaccessible : {$this->filePath}");
        }

        $fileHash = AstraFileParser::fileHash($this->filePath);
        $fileSize = filesize($this->filePath);
        $filename = basename($this->filePath);

        $alreadyImported = ImportLog::where('file_hash', $fileHash)
            ->whereIn('status', ['completed', 'partial'])
            ->exists();

        if ($alreadyImported) {
            Log::channel('imports')->info('[VERBRAUCH] Fichier déjà importé (hash identique), skip.', [
                'hash' => $fileHash,
            ]);
            return;
        }

        $importLog = ImportLog::create([
            'import_type'     => 'verbrauch',
            'filename'        => $filename,
            'file_path'       => $this->filePath,
            'file_size_bytes' => $fileSize,
            'file_hash'       => $fileHash,
            'status'          => 'pending',
            'triggered_by'    => $this->triggeredBy,
        ]);
        $importLog->markAsRunning();

        $handle = fopen($this->filePath, 'r');
        if ($handle === false) {
            $importLog->markAsFailed("Impossible d'ouvrir le fichier en lecture.");
            throw new \RuntimeException("fopen() a échoué pour : {$this->filePath}");
        }

        $stats = [
            'total_lines'    => 0,
            'lines_updated'  => 0,
            'lines_skipped'  => 0,
            'lines_failed'   => 0,
        ];
        $errorDetails = [];

        try {
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                $importLog->markAsFailed('Fichier vide ou illisible.');
                return;
            }

            $firstLine = AstraFileParser::toUtf8($firstLine);
            $separator = AstraFileParser::detectSeparator($firstLine);
            $headers   = AstraFileParser::normalizeHeaders(
                str_getcsv(rtrim($firstLine, "\r\n"), $separator)
            );

            Log::channel('imports')->info('[VERBRAUCH] En-têtes détectés', [
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
                $parsed = AstraFileParser::parseVerbrauchLine($headers, $values);

                if ($parsed === null) {
                    $stats['lines_failed']++;
                    if (count($errorDetails) < self::MAX_ERROR_DETAILS) {
                        $errorDetails[] = [
                            'line'  => $lineNumber,
                            'raw'   => mb_substr($line, 0, 100),
                            'error' => 'Ligne verbrauch invalide (TG manquant ou aucune donnée)',
                        ];
                    }
                    continue;
                }

                $stats['total_lines']++;
                $chunk[] = $parsed;

                if (count($chunk) >= self::CHUNK_SIZE) {
                    $updatedInChunk          = $this->applyChunk($chunk);
                    $stats['lines_updated'] += $updatedInChunk;
                    $stats['lines_skipped'] += count($chunk) - $updatedInChunk;
                    $chunk = [];
                    gc_collect_cycles();

                    if ($stats['total_lines'] % 50_000 === 0) {
                        Log::channel('imports')->info('[VERBRAUCH] Progression', [
                            'lines'   => $stats['total_lines'],
                            'updated' => $stats['lines_updated'],
                            'memory'  => round(memory_get_usage(true) / 1_048_576) . ' Mo',
                        ]);
                        if ($this->job && method_exists($this->job, 'ping')) {
                            $this->job->ping();
                        }
                    }
                }
            }

            if (!empty($chunk)) {
                $updatedInChunk          = $this->applyChunk($chunk);
                $stats['lines_updated'] += $updatedInChunk;
                $stats['lines_skipped'] += count($chunk) - $updatedInChunk;
            }

            $importLog->update([
                'total_lines'    => $stats['total_lines'],
                'lines_inserted' => 0,
                'lines_updated'  => $stats['lines_updated'],
                'lines_skipped'  => $stats['lines_skipped'],
                'lines_failed'   => $stats['lines_failed'],
                'error_details'  => !empty($errorDetails) ? $errorDetails : null,
            ]);
            $importLog->markAsFinished();

            try {
                Log::channel('imports')->info('[VERBRAUCH] Import terminé', $stats);
            } catch (\Throwable) {
            }

        } catch (\Throwable $e) {
            $importLog->markAsFailed($e->getMessage(), $errorDetails);
            Log::channel('imports')->error('[VERBRAUCH] Erreur critique', [
                'exception' => $e->getMessage(),
            ]);
            throw $e;
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
            $this->cleanupSourceFile();
        }
    }

    private function applyChunk(array $chunk): int
    {
        if (empty($chunk)) {
            return 0;
        }

        $byTg = [];
        foreach ($chunk as $row) {
            $tg = $row['numero_tg'];
            $byTg[$tg] = array_merge($byTg[$tg] ?? [], $row);
        }

        $tgNumbers = array_keys($byTg);

        $fields = [
            'consommation_mixte', 'co2_wltp', 'consommation_wltp',
            'consommation_el', 'autonomie_min', 'autonomie_max', 'energie_label',
        ];

        $setClauses = [];
        $bindings   = [];

        foreach ($fields as $field) {
            $cases    = [];
            $hasValue = false;

            foreach ($byTg as $tg => $data) {
                if (isset($data[$field]) && $data[$field] !== null && $data[$field] !== '') {
                    $cases[]    = 'WHEN ? THEN ?';
                    $bindings[] = $tg;
                    $bindings[] = $data[$field];
                    $hasValue   = true;
                }
            }

            if ($hasValue) {
                $setClauses[] = "`{$field}` = CASE `numero_tg` " . implode(' ', $cases) . " ELSE `{$field}` END";
            }
        }

        if (empty($setClauses)) {
            return 0;
        }

        $setClauses[] = '`updated_at` = NOW()';
        $placeholders = implode(',', array_fill(0, count($tgNumbers), '?'));

        foreach ($tgNumbers as $tg) {
            $bindings[] = $tg;
        }

        $sql = 'UPDATE `vehicles` SET ' . implode(', ', $setClauses)
             . " WHERE `numero_tg` IN ({$placeholders})";

        DB::statement($sql, $bindings);

        return count($tgNumbers);
    }

    private function cleanupSourceFile(): void
    {
        if (!$this->deleteAfterImport) {
            return;
        }
        try {
            if (is_file($this->filePath)) {
                @unlink($this->filePath);
            }
        } catch (\Throwable) {
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('imports')->critical('[VERBRAUCH] Job échoué définitivement', [
            'file'      => $this->filePath,
            'exception' => $exception->getMessage(),
        ]);

        ImportLog::where('file_path', $this->filePath)
            ->where('status', 'running')
            ->first()
            ?->markAsFailed('Job échoué : ' . $exception->getMessage());
    }
}
