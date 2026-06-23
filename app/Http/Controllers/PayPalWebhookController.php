<?php

namespace App\Http\Controllers;

use App\Events\PaymentSucceeded;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller : PayPalWebhookController
 *
 * Reçoit et vérifie les webhooks PayPal.
 * Route : POST /webhooks/paypal (sans auth, sans CSRF — vérification par signature)
 *
 * Événements PayPal traités :
 *   CHECKOUT.ORDER.APPROVED → Ordre approuvé (pré-autorisation)
 *   PAYMENT.CAPTURE.COMPLETED → Paiement capturé et confirmé ← On agit ici
 *   PAYMENT.CAPTURE.REFUNDED → Remboursement
 */
class PayPalWebhookController extends Controller
{
    /**
     * POST /webhooks/paypal
     *
     * SÉCURITÉ :
     * PayPal signe chaque webhook via HMAC-SHA256.
     * On vérifie la signature AVANT tout traitement.
     */
    public function handle(Request $request): JsonResponse
    {
        // ── Vérification de la signature PayPal ──────────────────────────────
        if (!$this->verifySignature($request)) {
            Log::warning('PayPal webhook: signature invalide', [
                'ip'      => $request->ip(),
                'headers' => $request->headers->all(),
            ]);
            // Retourner 200 même en cas d'échec (évite que PayPal ne retry indéfiniment)
            return response()->json(['status' => 'invalid_signature'], 200);
        }

        $payload   = $request->json()->all();
        $eventType = $payload['event_type'] ?? 'UNKNOWN';
        $orderId   = $payload['resource']['id'] ?? null;

        Log::info("PayPal webhook reçu : {$eventType}", ['order_id' => $orderId]);

        match($eventType) {
            // [CORRECTIF BUG #4] Orders API v2 émet CAPTURE.COMPLETED ;
            // l'API classique / Subscriptions / IPN émet SALE.COMPLETED.
            // On gère les deux pour être robuste quelle que soit l'intégration.
            'PAYMENT.SALE.COMPLETED',
            'PAYMENT.CAPTURE.COMPLETED' => $this->handleCaptureCompleted($payload, $request),

            // Remboursement — Annuler les commissions éventuelles
            'PAYMENT.SALE.REFUNDED',
            'PAYMENT.CAPTURE.REFUNDED'  => $this->handleRefunded($payload),

            default => null, // Autres événements ignorés
        };

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Traitement d'un paiement capturé et confirmé par PayPal.
     */
    private function handleCaptureCompleted(array $payload, Request $request): void
    {
        $resource  = $payload['resource'] ?? [];
        $orderId   = $payload['resource']['supplementary_data']['related_ids']['order_id']
            ?? $resource['id']
            ?? null;

        if (!$orderId) {
            Log::warning('PayPal webhook: order_id manquant');
            return;
        }

        // Extraction du montant — 'value' (Capture v2) OU 'total' (Sale classique)
        $amountDecimal = (float) (
            $resource['amount']['value']
            ?? $resource['amount']['total']
            ?? 0
        );
        $amountCts     = (int) round($amountDecimal * 100);
        $currency      = $resource['amount']['currency_code']
            ?? $resource['amount']['currency']
            ?? 'CHF';

        if ($amountCts <= 0 || $currency !== 'CHF') {
            Log::warning("PayPal webhook: montant invalide", compact('amountCts', 'currency'));
            return;
        }

        // ── GARDE D'IDEMPOTENCE ATOMIQUE (en amont du dispatch) ───────────────
        // PayPal peut livrer le même événement plusieurs fois (« at-least-once »).
        // On RÉSERVE l'ordre de façon atomique AVANT de dispatcher l'événement :
        // le premier webhook réussit la réservation et déclenche le traitement,
        // tout webhook ultérieur du même ordre est ignoré ici, à la source.
        //
        // Contrairement à un test Invoice::exists() (la facture est générée par un
        // job asynchrone, donc absente lors d'un 2e webhook arrivant en quelques ms),
        // ProcessedPayment::claim() s'appuie sur une contrainte UNIQUE en base :
        // il est donc fiable même sous course (deux webhooks quasi simultanés).
        $claimed = \App\Models\ProcessedPayment::claim(
            orderId:   $orderId,
            amountCts: $amountCts,
        );

        if (!$claimed) {
            Log::info("PayPal webhook: ordre {$orderId} déjà traité — skip (idempotence)");
            return;
        }

        // [CORRECTIF BUG #3] Le custom_id est transmis par CheckoutController au
        // moment de la création de l'ordre, au format "plan:4|ref:GARAGE10".
        // Le webhook étant serveur-à-serveur, il ne reçoit AUCUN cookie : le code
        // affilié doit donc voyager via ce custom_id.
        $customId = $resource['custom_id']
            ?? $resource['purchase_units'][0]['custom_id']
            ?? null;

        // Extraction du code affilié depuis le segment "ref:CODE"
        $affiliateCode = null;
        if ($customId && str_contains($customId, 'ref:')) {
            foreach (explode('|', $customId) as $segment) {
                if (str_starts_with($segment, 'ref:')) {
                    $affiliateCode = substr($segment, 4);
                }
            }
        }

        // Récupération de l'utilisateur depuis le payeur PayPal
        $payerEmail = $resource['payer']['email_address']
            ?? $resource['payer']['payer_info']['email']
            ?? null;
        $buyer      = $payerEmail
            ? \App\Models\User::where('email', $payerEmail)->first()
            : null;

        // ── Dispatch de l'événement PaymentSucceeded ──────────────────────────
        // Les Listeners s'occupent des commissions et de la facture
        event(new PaymentSucceeded(
            orderId:       $orderId,
            amountCts:     $amountCts,
            affiliateCode: $affiliateCode,
            buyer:         $buyer,
            payerEmail:    $payerEmail ?? '',
            paypalPayload: $resource,
        ));
    }

    /**
     * Traitement d'un remboursement PayPal.
     * Annule la commission en attente si elle existe.
     */
    private function handleRefunded(array $payload): void
    {
        $orderId = $payload['resource']['id'] ?? null;
        if (!$orderId) return;

        \App\Models\AffiliateEarning::where('paypal_order_id', $orderId)
            ->whereIn('status', ['pending', 'approved'])
            ->update(['status' => 'cancelled']);

        \App\Models\Invoice::where('paypal_order_id', $orderId)
            ->update(['status' => 'cancelled']);

        Log::info("PayPal refund traité : {$orderId}");
    }

    /**
     * Vérification de la signature HMAC-SHA256 PayPal.
     *
     * PayPal envoie :
     *   PAYPAL-AUTH-ALGO: SHA256withRSA
     *   PAYPAL-CERT-URL: https://api.paypal.com/v1/notifications/...
     *   PAYPAL-TRANSMISSION-ID: ...
     *   PAYPAL-TRANSMISSION-SIG: {base64_signature}
     *   PAYPAL-TRANSMISSION-TIME: ...
     */
    private function verifySignature(Request $request): bool
    {
        // En développement : skip la vérification (PayPal Sandbox n'a pas de vraie sig)
        if (app()->isLocal() || app()->environment('testing')) {
            return true;
        }

        $webhookId   = config('services.paypal.webhook_id');
        $transmId    = $request->header('PAYPAL-TRANSMISSION-ID');
        $transmTime  = $request->header('PAYPAL-TRANSMISSION-TIME');
        $certUrl     = $request->header('PAYPAL-CERT-URL');
        $algo        = $request->header('PAYPAL-AUTH-ALGO', 'SHA256withRSA');
        $signature   = $request->header('PAYPAL-TRANSMISSION-SIG');

        if (!$transmId || !$transmTime || !$certUrl || !$signature || !$webhookId) {
            return false;
        }

        // Validation que l'URL du certificat est bien chez PayPal (SSRF protection)
        if (!str_starts_with($certUrl, 'https://api.paypal.com/')
            && !str_starts_with($certUrl, 'https://api.sandbox.paypal.com/')) {
            return false;
        }

        // Message à vérifier (format PayPal officiel)
        $message   = "{$transmId}|{$transmTime}|{$webhookId}|" . $request->getContent();
        $sigBytes  = base64_decode($signature);

        try {
            // Récupération du certificat PayPal (mis en cache 24h)
            $certPem = \Illuminate\Support\Facades\Cache::remember(
                'paypal_cert:' . md5($certUrl),
                86400,
                fn() => file_get_contents($certUrl)
            );

            $pubKey = openssl_pkey_get_public($certPem);
            if ($pubKey === false) return false;

            $result = openssl_verify($message, $sigBytes, $pubKey, OPENSSL_ALGO_SHA256);
            return $result === 1;

        } catch (\Throwable $e) {
            Log::error('PayPal signature verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}


// ══════════════════════════════════════════════════════════════════════════════
// Événement : PaymentSucceeded
// ══════════════════════════════════════════════════════════════════════════════
