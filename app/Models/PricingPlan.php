<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Modèle PricingPlan
 *
 * Source de vérité pour les quotas et tarifs.
 * Les middlewares interrogent ce modèle via un cache 1h pour éviter
 * les requêtes BDD à chaque appel entrant.
 *
 * Convention : les prix sont stockés en centimes CHF (integer) pour éviter
 * les problèmes d'arrondi des flottants. Les accesseurs retournent les CHF.
 *
 * @property int    $level
 * @property string $name_fr
 * @property int    $price_monthly_chf   (centimes)
 * @property int    $price_yearly_chf    (centimes)
 * @property int    $token_price_chf     (centimes)
 * @property int    $web_monthly_limit   (-1 = illimité)
 * @property int    $api_monthly_limit   (0 = pas d'accès, -1 = illimité)
 * @property int    $api_rate_per_minute
 * @property bool   $can_export_pdf
 * @property bool   $can_export_csv
 * @property bool   $can_use_api
 * @property bool   $can_compare
 * @property bool   $can_use_tax_calc
 */
class PricingPlan extends Model
{
    /** Clé du cache global (tous les plans indexés par level) */
    private const CACHE_KEY = 'pricing_plans_all';
    private const CACHE_TTL = 3600; // 1 heure

    protected $fillable = [
        'level', 'name_fr', 'name_de', 'name_it', 'name_en',
        'price_monthly_chf', 'price_yearly_chf', 'token_price_chf',
        'web_monthly_limit', 'api_monthly_limit', 'api_rate_per_minute',
        'can_export_pdf', 'can_export_csv', 'can_use_api',
        'can_compare', 'can_use_tax_calc',
        'is_public', 'is_active', 'display_order',
        'stripe_price_id', 'paypal_plan_id', 'description_fr',
    ];

    protected function casts(): array
    {
        return [
            'can_export_pdf'   => 'boolean',
            'can_export_csv'   => 'boolean',
            'can_use_api'      => 'boolean',
            'can_compare'      => 'boolean',
            'can_use_tax_calc' => 'boolean',
            'is_public'        => 'boolean',
            'is_active'        => 'boolean',
        ];
    }

    // ── Accesseurs CHF (centimes → CHF) ──────────────────────────────────────

    /** Retourne le prix mensuel en CHF (float). Ex : 4900 → 49.0 */
    public function getPriceMonthlyAttribute(): float
    {
        return $this->price_monthly_chf / 100;
    }

    /** Retourne le prix annuel en CHF. */
    public function getPriceYearlyAttribute(): ?float
    {
        return $this->price_yearly_chf !== null
            ? $this->price_yearly_chf / 100
            : null;
    }

    /** Retourne le prix d'un jeton en CHF. */
    public function getTokenPriceAttribute(): float
    {
        return $this->token_price_chf / 100;
    }

    /** Retourne le libellé de la limite web formaté. */
    public function getWebLimitLabelAttribute(): string
    {
        return $this->web_monthly_limit === -1
            ? 'Illimité'
            : number_format($this->web_monthly_limit, 0, '.', '\'') . ' / mois';
    }

    /** Retourne vrai si l'accès web est illimité. */
    public function isWebUnlimited(): bool
    {
        return $this->web_monthly_limit === -1;
    }

    // ── Lookup par niveau (avec cache) ────────────────────────────────────────

    /**
     * Retourne le plan pour un niveau donné depuis le cache.
     * Utilisé par les middlewares à chaque requête.
     *
     * @param  int $level  1–8
     * @return static|null
     */
    public static function forLevel(int $level): ?self
    {
        return static::allCached()->get($level);
    }

    /**
     * Retourne tous les plans indexés par level (depuis le cache).
     * Le cache est invalidé automatiquement après chaque sauvegarde.
     *
     * @return \Illuminate\Support\Collection<int, static>
     */
    public static function allCached(): \Illuminate\Support\Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return static::orderBy('level')->get()->keyBy('level');
        });
    }

    /**
     * Invalide le cache. Appelé dans les observers (saved, deleted).
     */
    public static function invalidateCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // ── Observers ─────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saved(fn() => static::invalidateCache());
        static::deleted(fn() => static::invalidateCache());
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($q)  { return $q->where('is_active', true); }
    public function scopePublic($q)  { return $q->where('is_public', true)->where('is_active', true); }
    public function scopeOrdered($q) { return $q->orderBy('display_order')->orderBy('level'); }
}
