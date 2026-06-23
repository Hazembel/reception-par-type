<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle ApiLog
 *
 * Enregistre chaque appel API pour la facturation et l'audit.
 *
 * @property int         $id
 * @property string|null $user_id
 * @property int|null    $api_key_id
 * @property string      $endpoint
 * @property string      $method
 * @property string|null $numero_tg
 * @property int         $status_code
 * @property int|null    $response_time_ms
 * @property string|null $ip_address
 * @property bool        $billed
 * @property int         $tokens_charged
 * @property \Carbon\Carbon $created_at
 */
class ApiLog extends Model
{
    // Pas d'updated_at : les logs ne sont jamais modifiés
    public const UPDATED_AT = null;

    protected $table  = 'api_logs';

    protected $fillable = [
        'user_id', 'api_key_id', 'endpoint', 'method', 'numero_tg',
        'status_code', 'response_time_ms', 'ip_address', 'user_agent',
        'billed', 'tokens_charged',
    ];

    protected function casts(): array
    {
        return [
            'billed'     => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers statiques ─────────────────────────────────────────────────────

    /**
     * Enregistre un appel API de manière asynchrone (via queue).
     * Utilisé dans le middleware pour ne pas impacter le temps de réponse.
     */
    public static function record(array $data): void
    {
        // Dispatch en async pour ne pas bloquer la réponse HTTP
        dispatch(static fn() => static::create($data))->afterResponse();
    }

    /**
     * Compte les appels facturables d'un utilisateur pour le mois courant.
     */
    public static function countBilledThisMonth(string $userId): int
    {
        return static::where('user_id', $userId)
            ->where('billed', true)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeBilled($q)    { return $q->where('billed', true); }
    public function scopeSuccess($q)   { return $q->where('status_code', 200); }
    public function scopeThisMonth($q) {
        return $q->whereYear('created_at', now()->year)
                 ->whereMonth('created_at', now()->month);
    }
}
