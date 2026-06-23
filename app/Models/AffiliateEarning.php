<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliateEarning extends Model
{
    protected $fillable = [
        'affiliate_id', 'buyer_user_id', 'paypal_order_id',
        'sale_amount_cts', 'commission_cts', 'rate_permille',
        'status', 'payment_reference', 'paid_at',
    ];

    protected function casts(): array {
        return ['paid_at' => 'datetime'];
    }

    public function affiliate(): BelongsTo { return $this->belongsTo(Affiliate::class); }
    public function buyer(): BelongsTo     { return $this->belongsTo(User::class, 'buyer_user_id'); }

    public function getSaleAmountChfAttribute(): float { return $this->sale_amount_cts / 100; }
    public function getCommissionChfAttribute(): float { return $this->commission_cts / 100; }

    public function scopePending($q)  { return $q->where('status', 'pending'); }
    public function scopeApproved($q) { return $q->where('status', 'approved'); }
    public function scopePaid($q)     { return $q->where('status', 'paid'); }
}


// ══════════════════════════════════════════════════════════════════════════════
// Modèle Invoice
// ══════════════════════════════════════════════════════════════════════════════
