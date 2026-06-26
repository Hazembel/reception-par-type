<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle ImportLog
 *
 * Représente une entrée dans l'historique des imports ASTRA.
 *
 * @property int         $id
 * @property string      $import_type     '2000' | '5000'
 * @property string      $filename
 * @property string|null $file_path
 * @property int|null    $file_size_bytes
 * @property string|null $file_hash
 * @property string      $status          pending | running | completed | partial | failed
 * @property int         $total_lines
 * @property int         $lines_inserted
 * @property int         $lines_updated
 * @property int         $lines_skipped
 * @property int         $lines_failed
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $finished_at
 * @property int|null    $duration_seconds
 * @property string|null $error_message
 * @property array|null  $error_details
 * @property string      $triggered_by
 */
class ImportLog extends Model
{
    protected $table = 'imports_log';

    protected $fillable = [
        'import_type',
        'filename',
        'file_path',
        'file_size_bytes',
        'file_hash',
        'status',
        'total_lines',
        'lines_inserted',
        'lines_updated',
        'lines_skipped',
        'lines_failed',
        'started_at',
        'finished_at',
        'duration_seconds',
        'error_message',
        'error_details',
        'triggered_by',
    ];

    protected function casts(): array
    {
        return [
            'started_at'   => 'datetime',
            'finished_at'  => 'datetime',
            'error_details'=> 'array',
        ];
    }

    // ── Helpers d'état ────────────────────────────────────────────────────────

    /** Marque l'import comme démarré */
    public function markAsRunning(): void
    {
        $this->update([
            'status'     => 'running',
            'started_at' => now(),
        ]);
    }

    /** Marque l'import comme terminé avec succès ou partiellement */
    public function markAsFinished(array $stats = []): void
    {
        $finishedAt = now();
        $status = ($this->lines_failed > 0) ? 'partial' : 'completed';

        // > 5% de lignes en erreur → statut "failed"
        if ($this->total_lines > 0 && ($this->lines_failed / $this->total_lines) > 0.05) {
            $status = 'failed';
        }

        $this->update(array_merge([
            'status'           => $status,
            'finished_at'      => $finishedAt,
            'duration_seconds' => max(0, (int) $finishedAt->diffInSeconds($this->started_at)),
        ], $stats));
    }

    /** Marque l'import comme échoué avec message d'erreur */
    public function markAsFailed(string $message, array $details = []): void
    {
        $finishedAt = now();

        $this->update([
            'status'           => 'failed',
            'finished_at'      => $finishedAt,
            'duration_seconds' => $this->started_at
                ? max(0, (int) $finishedAt->diffInSeconds($this->started_at))
                : null,
            'error_message'    => $message,
            'error_details'    => !empty($details) ? array_slice($details, 0, 100) : null,
        ]);
    }

    /** Retourne un résumé lisible des performances */
    public function getSummaryAttribute(): string
    {
        $rate = $this->duration_seconds > 0
            ? round($this->total_lines / $this->duration_seconds)
            : 0;

        return sprintf(
            '%s | +%d créés | ~%d MàJ | %d erreurs | %ds (%d l/s)',
            strtoupper($this->status),
            $this->lines_inserted,
            $this->lines_updated,
            $this->lines_failed,
            $this->duration_seconds ?? 0,
            $rate
        );
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeType2000($query) { return $query->where('import_type', '2000'); }
    public function scopeType5000($query) { return $query->where('import_type', '5000'); }
    public function scopeCompleted($query) { return $query->whereIn('status', ['completed', 'partial']); }
    public function scopeFailed($query) { return $query->where('status', 'failed'); }
    public function scopeLatest($query) { return $query->orderByDesc('created_at'); }
}
