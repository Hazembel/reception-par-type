<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware : EnsureApiKeyHasQuota
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Contrôle d'accès en deux couches pour les routes d'API B2B :
 *
 * COUCHE 1 — Rate Limiting strict (Redis/cache) :
 *   - Par seconde  : protection contre les bursts abusifs
 *   - Par minute   : protection contre les scripts automatisés
 *   Clé de cache = hash(ip + api_key_id) → isolation par client + IP
 *
 * COUCHE 2 — Quota mensuel :
 *   - Vérifie que le client n'a pas dépassé son quota mensuel contractuel
 *   - Lecture depuis personal_access_tokens.calls_this_month (denormalisé)
 *   - Incrément atomique à chaque appel réussi
 *
 * En cas de dépassement → réponse JSON 429 avec headers Retry-After.
 *
 * LOGGING :
 *   - Chaque requête est loguée dans api_logs (async, après réponse)
 *   - Même les requêtes bloquées (pour détecter les attaques)
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Enregistrement dans bootstrap/app.php :
 *   $middleware->alias(['api.quota' => EnsureApiKeyHasQuota::class]);
 *
 * Usage sur les routes API :
 *   Route::middleware(['auth:sanctum', 'api.quota'])->group(...)
 */
class EnsureApiKeyHasQuota
{
    public function __construct(
        private readonly RateLimiter $rateLimiter
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $user      = Auth::user();
        $token     = $request->user()?->currentAccessToken();

        // ── Vérification 0 : clé active et non expirée ───────────────────────
        if (!$token || !$token->is_active) {
            return $this->denyJson(
                'API key revoked or inactive.',
                401,
                [],
                $request, $user, $token, $startTime, false
            );
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            return $this->denyJson(
                'API key expired.',
                401,
                ['key_expired_at' => $token->expires_at->toIso8601String()],
                $request, $user, $token, $startTime, false
            );
        }

        // ── Récupération du plan ──────────────────────────────────────────────
        $planId = (int) ($token->plan_id ?? $user?->subscription_level ?? 6);
        $plan   = config("api_plans.{$planId}");

        if (!$plan) {
            return $this->denyJson(
                'No valid API plan associated with this key.',
                403,
                [],
                $request, $user, $token, $startTime, false
            );
        }

        // ── Vérification que l'endpoint est autorisé pour ce plan ────────────
        $endpointName = $request->route()?->getName();
        $shortName    = $this->resolveEndpointShortName($endpointName);

        if (!in_array($shortName, $plan['endpoints'], true)) {
            return $this->denyJson(
                "Endpoint not available on your plan ({$planId}). Upgrade to access this endpoint.",
                403,
                ['available_endpoints' => $plan['endpoints']],
                $request, $user, $token, $startTime, false
            );
        }

        // ── COUCHE 1 : Rate limiting par seconde ─────────────────────────────
        $ratePerSecond = $token->rate_per_minute
            ? (int) ceil($token->rate_per_minute / 60)
            : ($plan['rate_per_second'] ?? 5);

        $keyPerSecond = $this->buildRateLimitKey($request, $token->id, 'second');

        if ($this->rateLimiter->tooManyAttempts($keyPerSecond, $ratePerSecond)) {
            $retryAfter = $this->rateLimiter->availableIn($keyPerSecond);
            return $this->denyJson(
                "Rate limit exceeded: max {$ratePerSecond} requests per second.",
                429,
                ['retry_after_seconds' => $retryAfter, 'limit_type' => 'per_second'],
                $request, $user, $token, $startTime, true
            );
        }
        $this->rateLimiter->hit($keyPerSecond, 1); // TTL de 1 seconde

        // ── COUCHE 1 : Rate limiting par minute ───────────────────────────────
        $ratePerMinute = $token->rate_per_minute ?? $plan['rate_per_minute'];
        $keyPerMinute  = $this->buildRateLimitKey($request, $token->id, 'minute');

        if ($this->rateLimiter->tooManyAttempts($keyPerMinute, $ratePerMinute)) {
            $retryAfter = $this->rateLimiter->availableIn($keyPerMinute);
            return $this->denyJson(
                "Rate limit exceeded: max {$ratePerMinute} requests per minute.",
                429,
                [
                    'retry_after_seconds' => $retryAfter,
                    'limit_type'          => 'per_minute',
                    'limit_reset_at'      => now()->addSeconds($retryAfter)->toIso8601String(),
                ],
                $request, $user, $token, $startTime, true
            );
        }
        $this->rateLimiter->hit($keyPerMinute, 60); // TTL de 60 secondes

        // ── COUCHE 2 : Quota mensuel ──────────────────────────────────────────
        $monthlyQuota  = $token->monthly_quota ?? $plan['monthly_calls'];
        $usedThisMonth = (int) $token->calls_this_month;

        if ($monthlyQuota !== PHP_INT_MAX && $usedThisMonth >= $monthlyQuota) {
            return $this->denyJson(
                "Monthly quota exhausted ({$usedThisMonth}/{$monthlyQuota}). Quota resets on the 1st of next month.",
                429,
                [
                    'quota_used'       => $usedThisMonth,
                    'quota_limit'      => $monthlyQuota,
                    'quota_reset_date' => now()->startOfMonth()->addMonth()->toDateString(),
                    'limit_type'       => 'monthly_quota',
                ],
                $request, $user, $token, $startTime, true
            );
        }

        // ── Injection des données de plan dans la requête ─────────────────────
        // Permet aux controllers de lire le plan sans re-requêter la config.
        $request->attributes->set('api_plan',    $plan);
        $request->attributes->set('api_plan_id', $planId);
        $request->attributes->set('api_token',   $token);

        // ── Traitement de la requête ──────────────────────────────────────────
        $response = $next($request);

        // ── Post-traitement : incrément compteurs + logging ───────────────────
        $statusCode     = $response->getStatusCode();
        $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);
        $isBilled       = ($statusCode >= 200 && $statusCode < 300);

        if ($isBilled) {
            // [CORRECTIF BUG #2] increment() retourne un int (lignes affectées),
            // donc le chaîner provoquait "Call to a member function increment() on int".
            // On fait tout en UNE seule requête atomique via DB::raw().
            \DB::table('personal_access_tokens')
                ->where('id', $token->id)
                ->update([
                    'calls_this_month' => \DB::raw('calls_this_month + 1'),
                    'calls_total'      => \DB::raw('calls_total + 1'),
                    'last_used_at'     => now(),
                    'last_used_ip'     => $request->ip(),
                ]);
        }

        // Log asynchrone (ne bloque pas la réponse)
        ApiLog::record([
            'user_id'          => $user?->id,
            'api_key_id'       => $token->id,
            'endpoint'         => $shortName,
            'method'           => $request->method(),
            'numero_tg'        => $request->route('numero_tg'),
            'status_code'      => $statusCode,
            'response_time_ms' => $responseTimeMs,
            'ip_address'       => $request->ip(),
            'user_agent'       => substr($request->userAgent() ?? '', 0, 255),
            'billed'           => $isBilled,
            'tokens_charged'   => $isBilled ? 1 : 0,
        ]);

        // ── Ajout des headers d'info quota dans la réponse ────────────────────
        if (method_exists($response, 'headers')) {
            $response->headers->set('X-RateLimit-Limit',       $ratePerMinute);
            $response->headers->set('X-RateLimit-Remaining',   max(0, $ratePerMinute - $this->rateLimiter->attempts($keyPerMinute)));
            $response->headers->set('X-Quota-Limit',           $monthlyQuota === PHP_INT_MAX ? 'unlimited' : $monthlyQuota);
            $response->headers->set('X-Quota-Used',            $usedThisMonth + ($isBilled ? 1 : 0));
            $response->headers->set('X-Quota-Remaining',       $monthlyQuota === PHP_INT_MAX ? 'unlimited' : max(0, $monthlyQuota - $usedThisMonth - 1));
            $response->headers->set('X-Plan',                  $plan['name']);
        }

        return $response;
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    /**
     * Construit la clé de rate limiting.
     * Format : sha256(ip + "|" + token_id) + ":minute|second"
     *
     * Le SHA-256 garantit que :
     *  1. La clé est de longueur fixe (bon pour Redis)
     *  2. L'IP n'est pas lisible dans la clé cache (RGPD)
     *  3. Deux clients derrière le même NAT ont des clés différentes
     */
    private function buildRateLimitKey(Request $request, int $tokenId, string $window): string
    {
        $raw = ($request->ip() ?? 'unknown') . '|' . $tokenId;
        return 'api_rl:' . hash('sha256', $raw) . ':' . $window;
    }

    /**
     * Retourne un nom d'endpoint court depuis le nom de route Laravel.
     * Ex: "api.v1.vehicle.tg" → "vehicle.tg"
     */
    private function resolveEndpointShortName(?string $routeName): string
    {
        if (!$routeName) {
            return 'unknown';
        }
        // Supprime le préfixe "api.v1." standard
        return preg_replace('/^api\.v1\./', '', $routeName);
    }

    /**
     * Retourne une réponse JSON 4xx/429 uniformisée et logue la tentative.
     */
    private function denyJson(
        string  $message,
        int     $statusCode,
        array   $extra,
        Request $request,
        $user,
        $token,
        float   $startTime,
        bool    $counted
    ): JsonResponse {
        $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

        // Log même les requêtes bloquées (pour la sécurité)
        if ($counted) {
            ApiLog::record([
                'user_id'          => $user?->id,
                'api_key_id'       => $token?->id,
                'endpoint'         => $this->resolveEndpointShortName($request->route()?->getName()),
                'method'           => $request->method(),
                'numero_tg'        => $request->route('numero_tg'),
                'status_code'      => $statusCode,
                'response_time_ms' => $responseTimeMs,
                'ip_address'       => $request->ip(),
                'user_agent'       => substr($request->userAgent() ?? '', 0, 255),
                'billed'           => false, // Jamais facturé si bloqué
                'tokens_charged'   => 0,
            ]);
        }

        $body = array_merge([
            'success'   => false,
            'error'     => $this->statusCodeToError($statusCode),
            'message'   => $message,
            'timestamp' => now()->toIso8601String(),
            'docs'      => 'https://reception-par-type.ch/api/docs',
        ], $extra);

        $headers = [];
        if ($statusCode === 429 && isset($extra['retry_after_seconds'])) {
            $headers['Retry-After'] = $extra['retry_after_seconds'];
        }

        return response()->json($body, $statusCode, $headers);
    }

    private function statusCodeToError(int $code): string
    {
        return match($code) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            422 => 'UNPROCESSABLE_ENTITY',
            429 => 'TOO_MANY_REQUESTS',
            500 => 'INTERNAL_SERVER_ERROR',
            default => 'ERROR',
        };
    }
}
