<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'user_id', 'invoice_number', 'paypal_order_id',
        'subtotal_cts', 'tax_rate_bp', 'tax_amount_cts', 'total_cts',
        'is_vat_exempt', 'currency', 'billing_address', 'line_items',
        'pdf_path', 'status', 'issued_at', 'due_at',
    ];

    protected function casts(): array {
        return [
            'billing_address' => 'array',
            'line_items'      => 'array',
            'is_vat_exempt'   => 'boolean',
            'issued_at'       => 'datetime',
            'due_at'          => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    // ── Accesseurs ────────────────────────────────────────────────────────────
    public function getSubtotalChfAttribute(): float   { return $this->subtotal_cts   / 100; }
    public function getTaxAmountChfAttribute(): float  { return $this->tax_amount_cts / 100; }
    public function getTotalChfAttribute(): float      { return $this->total_cts      / 100; }
    public function getTaxRatePctAttribute(): float    { return $this->tax_rate_bp    / 100; }

    /** Format suisse : "1'234.50" */
    public function formatChf(int $cents): string {
        return number_format($cents / 100, 2, '.', '\'');
    }

    // ── Numéro séquentiel ─────────────────────────────────────────────────────

    /**
     * Génère le prochain numéro de facture séquentiel.
     * Format : RPT-{ANNÉE}-{NUMÉRO_5_CHIFFRES}
     * Ex: RPT-2024-00042
     *
     * Transaction BDD pour éviter les doublons en parallèle.
     */
    public static function generateNumber(): string {
        return \DB::transaction(function () {
            $year = now()->year;
            $last = static::where('invoice_number', 'like', "RPT-{$year}-%")
                ->lockForUpdate()
                ->orderByDesc('invoice_number')
                ->value('invoice_number');

            $seq = $last
                ? (int) substr($last, -5) + 1
                : 1;

            return sprintf('RPT-%d-%05d', $year, $seq);
        });
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopePaid($q) { return $q->where('status', 'paid'); }
    public function scopeSent($q) { return $q->where('status', 'sent'); }
}
