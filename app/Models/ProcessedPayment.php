<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Modèle ProcessedPayment — garde d'idempotence des webhooks PayPal.
 *
 * @property int         $id
 * @property string      $paypal_order_id
 * @property string|null $intent
 * @property int         $amount_cts
 * @property string|null $buyer_id
 * @property \Carbon\Carbon $processed_at
 */
class ProcessedPayment extends Model
{
    protected $fillable = [
        'paypal_order_id',
        'intent',
        'amount_cts',
        'buyer_id',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_cts'   => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Tente de "réserver" un ordre PayPal pour traitement.
     *
     * Retourne true si l'ordre n'avait jamais été traité (réservation réussie),
     * false s'il l'avait déjà été (doublon → le listener doit s'arrêter).
     *
     * S'appuie sur la contrainte UNIQUE de paypal_order_id : même en cas de
     * race condition (deux webnhooks simultanés), une seule insertion réussit ;
     * l'autre lève une QueryException d'unicité, interceptée ici → false.
     *
     * @return bool  true = premier traitement, false = doublon
     */
    public static function claim(
        string $orderId,
        ?string $intent = null,
        int $amountCts = 0,
        ?string $buyerId = null
    ): bool {
        try {
            static::create([
                'paypal_order_id' => $orderId,
                'intent'          => $intent,
                'amount_cts'      => $amountCts,
                'buyer_id'        => $buyerId,
                'processed_at'    => now(),
            ]);

            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            // Violation de contrainte d'unicité = ordre déjà traité.
            // (code SQLSTATE 23000 / 23505 selon le SGBD)
            return false;
        }
    }
}
