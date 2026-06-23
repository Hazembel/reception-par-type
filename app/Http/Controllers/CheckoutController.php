<?php

namespace App\Http\Controllers;

use App\Http\Middleware\TrackAffiliate;
use App\Models\PricingPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller : CheckoutController  [CORRECTIF BUG #3]
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Crée un ordre PayPal en encodant l'intention d'achat ET le code affilié
 * dans le champ custom_id.
 *
 * POURQUOI CE CONTROLLER EST CRITIQUE :
 * Le webhook PayPal est un appel serveur-à-serveur : il ne reçoit AUCUN cookie
 * du navigateur. Le cookie `rpt_ref` posé par TrackAffiliate ne peut donc PAS
 * être lu côté webhook. La seule façon de transmettre le code affilié jusqu'au
 * webhook est de le lire ICI (où les cookies sont disponibles) et de l'injecter
 * dans le custom_id de l'ordre PayPal — que PayPal renverra ensuite tel quel
 * dans le payload du webhook.
 *
 * Format du custom_id : "plan:4" ou "tokens:50" ou "plan:4|ref:GARAGE10"
 * ─────────────────────────────────────────────────────────────────────────────
 */
class CheckoutController extends Controller
{
    /**
     * POST /{locale}/checkout/create-order
     * Crée un ordre PayPal et renvoie son identifiant au front.
     */
    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'  => ['required', 'in:plan,tokens'],
            'value' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        // ── Lecture du cookie affilié déposé par TrackAffiliate ──────────────
        // C'EST ICI que le cookie devient custom_id (transmis à PayPal → webhook).
        // Sans cette étape, aucune commission ne serait jamais attribuée (BUG #3).
        $affiliateCode = $request->cookie(TrackAffiliate::COOKIE_NAME);

        // Construction du custom_id : "plan:4|ref:GARAGE10"
        $customId = "{$validated['type']}:{$validated['value']}";
        if ($affiliateCode && preg_match('/^[A-Z0-9\-]{3,30}$/', strtoupper($affiliateCode))) {
            $customId .= '|ref:' . strtoupper($affiliateCode);
        }

        // Montant selon l'achat
        $amountChf = $this->resolveAmount($validated['type'], (int) $validated['value']);

        if ($amountChf <= 0) {
            return response()->json([
                'success' => false,
                'error'   => 'INVALID_AMOUNT',
                'message' => 'Montant invalide pour cet achat.',
            ], 422);
        }

        // ── Création de l'ordre via le SDK PayPal ────────────────────────────
        // Adapter selon le SDK utilisé (paypal/paypal-server-sdk ou srmklive/paypal).
        // Pseudo-implémentation : remplacer par votre client PayPal réel.
        try {
            $order = $this->createPayPalOrder($amountChf, $customId, $validated);
        } catch (\Throwable $e) {
            \Log::error('Échec création ordre PayPal', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error'   => 'PAYPAL_ERROR',
                'message' => 'Impossible de créer l\'ordre de paiement.',
            ], 502);
        }

        return response()->json([
            'success'  => true,
            'order_id' => $order['id'] ?? null,
        ]);
    }

    /**
     * Résout le montant en CHF selon le type et la valeur achetés.
     */
    private function resolveAmount(string $type, int $value): float
    {
        if ($type === 'plan') {
            $plan = PricingPlan::forLevel($value);
            return $plan ? $plan->price_monthly_chf : 0.0;
        }

        // tokens : value × prix unitaire du jeton (niveau 1)
        $tokenPriceCts = PricingPlan::forLevel(1)?->token_price_chf ?? 200;
        return $value * ($tokenPriceCts / 100);
    }

    /**
     * Crée l'ordre via l'API PayPal Orders v2.
     *
     * ⚠️ Implémentation à adapter selon le SDK PayPal installé.
     * Cette méthode illustre la structure de la requête : l'essentiel est que
     * `custom_id` contienne bien l'intention d'achat + le code affilié.
     */
    private function createPayPalOrder(float $amountChf, string $customId, array $purchase): array
    {
        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => 'CHF',
                    'value'         => number_format($amountChf, 2, '.', ''),
                ],
                'custom_id'   => $customId, // ← Le code affilié voyage jusqu'au webhook
                'description' => $this->buildDescription($purchase),
            ]],
            'application_context' => [
                'return_url' => config('services.paypal.return_url', config('app.url') . '/payment/success'),
                'cancel_url' => config('services.paypal.cancel_url', config('app.url') . '/payment/cancel'),
            ],
        ];

        // Exemple d'appel réel (à décommenter selon votre intégration) :
        //
        //   $client = app(\PayPalCheckoutSdk\Core\PayPalHttpClient::class);
        //   $req = new \PayPalCheckoutSdk\Orders\OrdersCreateRequest();
        //   $req->body = $payload;
        //   $response = $client->execute($req);
        //   return ['id' => $response->result->id];
        //
        // Placeholder de développement : génère un faux ID d'ordre.
        return ['id' => 'DEV-ORDER-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(12))];
    }

    private function buildDescription(array $purchase): string
    {
        return $purchase['type'] === 'plan'
            ? "Abonnement niveau {$purchase['value']} — reception-par-type.ch"
            : "{$purchase['value']} jetons — reception-par-type.ch";
    }
}
