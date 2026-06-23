<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliateClick extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'affiliate_id', 'ip_hash', 'referrer_url', 'user_agent', 'converted',
    ];

    protected function casts(): array {
        return ['converted' => 'boolean', 'created_at' => 'datetime'];
    }

    public function affiliate(): BelongsTo { return $this->belongsTo(Affiliate::class); }

    public function scopeThisMonth($q) {
        return $q->whereYear('created_at', now()->year)
                 ->whereMonth('created_at', now()->month);
    }
}


// ══════════════════════════════════════════════════════════════════════════════
// Modèle AffiliateEarning
// ══════════════════════════════════════════════════════════════════════════════
