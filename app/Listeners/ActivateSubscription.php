<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Models\PricingPlan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener : ActivateSubscription  [CORRECTIF BUG #1]
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * MANQUAIT TOTALEMENT dans le code initial.
 *
 * Sans ce listener, un client payait via PayPal mais son compte n'était
 * jamais mis à niveau : ni changement de subscription_level, ni crédit de jetons.
 *
 * Ce listener lit le contenu de la commande PayPal (purchase_units → custom_id
 * ou items) pour déterminer CE QUI a été acheté, puis applique :
 *   - Un changement de niveau d'abonnement (achat d'un plan), OU
 *   - Un crédit de jetons (achat d'un pack pay-as-you-go)
 *
 * Le custom_id PayPal encode l'intention d'achat sous la forme :
 *   "plan:4"          → abonnement niveau 4
 *   "tokens:50"       → 50 jetons
 *   "plan:4|ref:GARAGE10" → abonnement + code affilié
 * ─────────────────────────────────────────────────────────────────────────────
 */
class ActivateSubscription implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(PaymentSucceeded $event): void
    {
        if (!$event->buyer) {
            // Acheteur introuvable (e-mail PayPal ≠ e-mail du compte).
            // On loggue pour traitement manuel — la facture est quand même générée.
            Log::warning('PaymentSucceeded sans acheteur identifié', [
                'order_id'    => $event->orderId,
                'payer_email' => $event->payerEmail,
            ]);
            return;
        }

        $purchase = $this->parsePurchaseIntent($event);

        // ── IDEMPOTENCE ───────────────────────────────────────────────────────
        // La garde d'idempotence est assurée EN AMONT, de façon atomique et
        // synchrone, par PayPalWebhookController via ProcessedPayment::claim() :
        // l'événement PaymentSucceeded n'est dispatché qu'UNE fois par ordre, même
        // sous double-webhook. Ce listener n'a donc plus besoin de re-réclamer
        // l'ordre (un second claim échouerait toujours et bloquerait l'activation).
        match ($purchase['type']) {
            'plan'   => $this->activatePlan($event, $purchase['value']),
            'tokens' => $this->creditTokens($event, $purchase['value']),
            default  => Log::warning('Intention d\'achat PayPal non reconnue', [
                'order_id'  => $event->orderId,
                'custom_id' => $purchase['raw'],
            ]),
        };
    }

    /**
     * Active ou met à niveau un abonnement.
     * Étend subscribed_until d'un mois (cumulatif si déjà actif).
     */
    private function activatePlan(PaymentSucceeded $event, int $level): void
    {
        $plan = PricingPlan::forLevel($level);
        if (!$plan || !$plan->is_active) {
            Log::error("Plan niveau {$level} introuvable ou inactif", ['order' => $event->orderId]);
            return;
        }

        $user    = $event->buyer;
        $current = $user->subscribed_until && $user->subscribed_until->isFuture()
            ? $user->subscribed_until
            : now();

        $user->forceFill([
            'subscription_level' => $level,
            'subscribed_until'   => $current->addMonth(),
        ])->save();

        Log::info('Abonnement activé', [
            'user_id' => $user->id,
            'level'   => $level,
            'until'   => $user->subscribed_until->toDateString(),
            'order'   => $event->orderId,
        ]);
    }

    /**
     * Crédite des jetons web (achat pay-as-you-go).
     * Incrément atomique pour éviter les race conditions.
     */
    private function creditTokens(PaymentSucceeded $event, int $tokens): void
    {
        \DB::table('users')
            ->where('id', $event->buyer->id)
            ->increment('web_tokens_balance', $tokens);

        Log::info('Jetons crédités', [
            'user_id' => $event->buyer->id,
            'tokens'  => $tokens,
            'order'   => $event->orderId,
        ]);
    }

    /**
     * Décode le custom_id PayPal pour déterminer ce qui a été acheté.
     *
     * Formats supportés :
     *   "plan:4"               → ['type'=>'plan',   'value'=>4]
     *   "tokens:50"            → ['type'=>'tokens', 'value'=>50]
     *   "plan:4|ref:GARAGE10"  → ['type'=>'plan',   'value'=>4]  (ref géré ailleurs)
     *
     * @return array{type: string, value: int, raw: string}
     */
    private function parsePurchaseIntent(PaymentSucceeded $event): array
    {
        $customId = $event->paypalPayload['custom_id']
            ?? $event->paypalPayload['purchase_units'][0]['custom_id']
            ?? '';

        // On ignore la partie "ref:" (gérée par RecordAffiliateCommission)
        $mainPart = explode('|', $customId)[0] ?? '';
        [$type, $value] = array_pad(explode(':', $mainPart, 2), 2, null);

        return [
            'type'  => in_array($type, ['plan', 'tokens'], true) ? $type : 'unknown',
            'value' => (int) $value,
            'raw'   => $customId,
        ];
    }
}
