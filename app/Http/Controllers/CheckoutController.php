<?php

namespace App\Http\Controllers;

use App\Http\Middleware\TrackAffiliate;
use App\Models\PricingPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            Log::error('Échec création ordre PayPal', ['error' => $e->getMessage()]);
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
     * Crée l'ordre via l'API PayPal Orders v2 (REST, sans SDK tiers).
     * Doc : https://developer.paypal.com/docs/api/orders/v2/#orders_create
     */
    private function createPayPalOrder(float $amountChf, string $customId, array $purchase): array
    {
        $baseUrl = config('services.paypal.mode', 'sandbox') === 'live'
            ? 'https://api.paypal.com'
            : 'https://api.sandbox.paypal.com';

        // Token OAuth2 (expires_in ~32 400 s) — mis en cache 8h pour éviter un
        // appel supplémentaire à chaque checkout.
        $accessToken = Cache::remember('paypal_access_token', 3600 * 8, function () use ($baseUrl) {
            $response = Http::withBasicAuth(
                config('services.paypal.client_id'),
                config('services.paypal.client_secret')
            )->asForm()->post("{$baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException('PayPal OAuth failed: ' . $response->body());
            }

            return $response->json('access_token');
        });

        $response = Http::withToken($accessToken)
            ->post("{$baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => 'CHF',
                        'value'         => number_format($amountChf, 2, '.', ''),
                    ],
                    'custom_id'   => $customId,
                    'description' => $this->buildDescription($purchase),
                ]],
                'application_context' => [
                    'return_url'  => config('services.paypal.return_url', config('app.url') . '/payment/success'),
                    'cancel_url'  => config('services.paypal.cancel_url', config('app.url') . '/payment/cancel'),
                    'brand_name'  => config('app.name', 'reception-par-type.ch'),
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        if (!$response->successful()) {
            // Vider le token caché si PayPal renvoie 401 (token expiré avant 8h)
            if ($response->status() === 401) {
                Cache::forget('paypal_access_token');
            }
            throw new \RuntimeException(
                'PayPal order creation failed (' . $response->status() . '): ' . $response->body()
            );
        }

        return $response->json();
    }

    private function buildDescription(array $purchase): string
    {
        return $purchase['type'] === 'plan'
            ? "Abonnement niveau {$purchase['value']} — reception-par-type.ch"
            : "{$purchase['value']} jetons — reception-par-type.ch";
    }
}
