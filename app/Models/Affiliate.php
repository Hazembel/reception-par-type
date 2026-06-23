<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends Model
{
    protected $fillable = [
        'user_id', 'affiliate_code', 'commission_rate_permille',
        'iban_encrypted', 'beneficiary_name', 'status',
        'total_clicks', 'total_conversions', 'total_earned_cts',
        'payout_threshold_cts', 'last_paid_at',
    ];

    protected function casts(): array {
        return [
            'iban_encrypted' => 'encrypted', // AES-256 via APP_KEY
            'last_paid_at'   => 'datetime',
        ];
    }

    // ── Relations ─────────────────────────────────────────────────────────────
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
    public function clicks(): HasMany    { return $this->hasMany(AffiliateClick::class); }
    public function earnings(): HasMany  { return $this->hasMany(AffiliateEarning::class); }

    // ── Accesseurs ────────────────────────────────────────────────────────────

    /** Taux de commission en % (ex: 100 ‰ → 10.0%) */
    public function getCommissionRatePctAttribute(): float {
        return $this->commission_rate_permille / 10;
    }

    /** Total gagné en CHF */
    public function getTotalEarnedChfAttribute(): float {
        return $this->total_earned_cts / 100;
    }

    /** Commission en attente (non encore payée) en centimes */
    public function getPendingEarningsCtSAttribute(): int {
        // Utilise la relation pré-chargée si disponible (évite N+1 dans les listings admin).
        if ($this->relationLoaded('earnings')) {
            return $this->earnings->whereIn('status', ['pending', 'approved'])->sum('commission_cts');
        }
        return $this->earnings()->whereIn('status', ['pending', 'approved'])->sum('commission_cts');
    }

    /** Le seuil de paiement est-il atteint ? */
    public function isPayoutThresholdReached(): bool {
        return $this->pendingEarningsCts >= $this->payout_threshold_cts;
    }

    /**
     * Calcule la commission sur un montant de vente (centimes).
     * @param  int $saleAmountCts  Montant de la vente en centimes
     * @return int                 Commission en centimes
     */
    public function calculateCommission(int $saleAmountCts): int {
        return (int) round($saleAmountCts * $this->commission_rate_permille / 1000);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopeActive($q)   { return $q->where('status', 'active'); }
    public function scopeByCode($q, string $code) {
        return $q->where('affiliate_code', strtoupper(trim($code)));
    }
}


// ══════════════════════════════════════════════════════════════════════════════
// Modèle AffiliateClick
// ══════════════════════════════════════════════════════════════════════════════
