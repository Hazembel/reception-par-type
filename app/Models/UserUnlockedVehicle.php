<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle UserUnlockedVehicle
 *
 * Enregistre les déverrouillages de fiches avancées par jeton.
 * Un enregistrement = 1 jeton consommé pour 1 véhicule ce mois-ci.
 *
 * @property int         $id
 * @property string      $user_id
 * @property string      $vehicle_id
 * @property \Carbon\Carbon $unlocked_at
 * @property int         $tokens_spent
 */
class UserUnlockedVehicle extends Model
{
    protected $table = 'user_unlocked_vehicles';

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'unlocked_at',
        'tokens_spent',
    ];

    protected function casts(): array
    {
        return [
            'unlocked_at' => 'datetime',
        ];
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /**
     * Limite la requête aux déverrouillages du mois courant.
     * Utilisé par AdminDashboardController et AdminUserController.
     */
    public function scopeThisMonth($q)
    {
        return $q->whereYear('unlocked_at', now()->year)
                 ->whereMonth('unlocked_at', now()->month);
    }

    // ── Helpers statiques ─────────────────────────────────────────────────────

    /**
     * Vérifie si un utilisateur a déjà débloqué un véhicule ce mois-ci.
     */
    public static function isUnlockedThisMonth(string $userId, string $vehicleId): bool
    {
        return static::where('user_id', $userId)
            ->where('vehicle_id', $vehicleId)
            ->whereYear('unlocked_at', now()->year)
            ->whereMonth('unlocked_at', now()->month)
            ->exists();
    }

    /**
     * Retourne le nombre de déverrouillages effectués par l'utilisateur ce mois.
     */
    public static function countThisMonth(string $userId): int
    {
        return static::where('user_id', $userId)
            ->whereYear('unlocked_at', now()->year)
            ->whereMonth('unlocked_at', now()->month)
            ->count();
    }
}
