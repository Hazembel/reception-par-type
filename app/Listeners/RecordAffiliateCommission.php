<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Models\Affiliate;
use App\Models\AffiliateClick;
use App\Models\AffiliateEarning;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener : RecordAffiliateCommission
 *
 * Écoute PaymentSucceeded.
 * Si un code affilié est présent (cookie ou custom_id PayPal),
 * calcule et enregistre la commission en attente.
 */
class RecordAffiliateCommission implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(PaymentSucceeded $event): void
    {
        if (!$event->affiliateCode) {
            return; // Pas d'affilié → rien à faire
        }

        $affiliate = Affiliate::byCode($event->affiliateCode)->active()->first();
        if (!$affiliate) {
            return;
        }

        // Idempotence (1re barrière) : pas de double commission pour le même ordre.
        if (AffiliateEarning::where('paypal_order_id', $event->orderId)->exists()) {
            return;
        }

        $commissionCts = $affiliate->calculateCommission($event->amountCts);

        // Idempotence (2e barrière, atomique) : la contrainte UNIQUE sur
        // affiliate_earnings.paypal_order_id rejette tout doublon créé par une
        // exécution concurrente. On intercepte l'exception d'unicité pour ne pas
        // faire échouer le job (et ne pas double-compter les stats ci-dessous).
        try {
            AffiliateEarning::create([
                'affiliate_id'    => $affiliate->id,
                'buyer_user_id'   => $event->buyer?->id,
                'paypal_order_id' => $event->orderId,
                'sale_amount_cts' => $event->amountCts,
                'commission_cts'  => $commissionCts,
                'rate_permille'   => $affiliate->commission_rate_permille,
                'status'          => 'pending',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::info(
                "Commission déjà enregistrée pour l'ordre {$event->orderId} (course détectée) — skip."
            );
            return;
        }

        // Mise à jour des stats dénormalisées
        \DB::table('affiliates')->where('id', $affiliate->id)->update([
            'total_conversions' => \DB::raw('total_conversions + 1'),
            'total_earned_cts'  => \DB::raw("total_earned_cts + {$commissionCts}"),
        ]);

        // Marquer le clic comme converti (si on retrouve l'IP — best effort)
        if ($event->buyer) {
            AffiliateClick::where('affiliate_id', $affiliate->id)
                ->where('converted', false)
                ->latest('created_at')
                ->first()
                ?->update(['converted' => true]);
        }
    }
}
