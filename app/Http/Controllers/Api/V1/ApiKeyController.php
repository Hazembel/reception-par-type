<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Controller : ApiKeyController
 *
 * Gestion sécurisée des clés API B2B.
 *
 * SÉCURITÉ CRYPTOGRAPHIQUE :
 * Sanctum stocke automatiquement les tokens sous SHA-256
 * (colonne `token` = hash('sha256', $plaintext)).
 * Le texte clair n'est retourné QU'UNE SEULE FOIS à la création.
 * Après cette réponse, il est impossible de retrouver la clé depuis la BDD.
 *
 * Format de la clé retournée :
 *   rpt_{random_80_chars}
 *   Préfixe "rpt" = reception-par-type (identifiable, non devinable)
 */
class ApiKeyController extends Controller
{
    /**
     * Préfixe des clés API pour identification immédiate en cas de fuite.
     * (GitHub Secret Scanning peut détecter ce pattern)
     */
    private const KEY_PREFIX = 'rpt_';

    // ── Lister les clés de l'utilisateur ──────────────────────────────────────

    /**
     * GET /api/v1/keys
     * Liste les clés actives (jamais le token en clair, seulement les métadonnées).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $keys = $user->tokens()
            ->select([
                'id', 'name', 'key_label', 'plan_id',
                'monthly_quota', 'rate_per_minute',
                'calls_this_month', 'calls_total',
                'last_used_at', 'expires_at', 'is_active', 'created_at',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($key) => $this->sanitizeKeyData($key));

        return response()->json([
            'success' => true,
            'data'    => $keys,
            'meta'    => ['count' => $keys->count()],
        ]);
    }

    // ── Créer une nouvelle clé ─────────────────────────────────────────────────

    /**
     * POST /api/v1/keys
     *
     * Génère une nouvelle clé API sécurisée.
     * ⚠️ Le token en clair n'est retourné QUE dans cette réponse.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Vérification du niveau d'abonnement (6, 7 ou 8 uniquement)
        if ($user->subscription_level < 6) {
            return response()->json([
                'success' => false,
                'error'   => 'FORBIDDEN',
                'message' => 'API access requires Business plan (level 6) or higher.',
            ], 403);
        }

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:100', 'regex:/^[\w\s\-\.]{1,100}$/'],
            'label' => ['nullable', 'string', 'max:100'],
            // Expiration optionnelle (ex: "2025-12-31")
            'expires_at' => ['nullable', 'date', 'after:today', 'before:' . now()->addYears(3)->toDateString()],
        ]);

        // Limite : 5 clés actives max par utilisateur
        $activeCount = $user->tokens()->where('is_active', true)->count();
        if ($activeCount >= 5) {
            return response()->json([
                'success' => false,
                'error'   => 'LIMIT_REACHED',
                'message' => 'Maximum of 5 active API keys per account. Revoke an existing key first.',
            ], 422);
        }

        $planId = (int) $user->subscription_level;
        $plan   = config("api_plans.{$planId}");

        // Création via Sanctum (génère automatiquement le token + son SHA-256)
        $tokenResult = $user->createToken(
            name:       $validated['name'],
            abilities:  ['api:read'],  // Scope minimal
        );

        // Enrichissement avec les colonnes métier ajoutées en Module 7
        $tokenResult->accessToken->update([
            'key_label'       => $validated['label'] ?? null,
            'plan_id'         => $planId,
            'monthly_quota'   => $plan['monthly_calls'] === PHP_INT_MAX ? null : $plan['monthly_calls'],
            'rate_per_minute' => $plan['rate_per_minute'],
            'expires_at'      => $validated['expires_at'] ?? null,
            'is_active'       => true,
        ]);

        // Ajout du préfixe "rpt_" au token en clair pour l'identification
        // Note : Sanctum retourne le token complet dans $tokenResult->plainTextToken
        // On ne peut pas modifier le token lui-même, mais on peut le préfixer dans la réponse.
        $plaintext = self::KEY_PREFIX . $tokenResult->plainTextToken;

        return response()->json([
            'success' => true,
            'data'    => [
                'key_id'    => $tokenResult->accessToken->id,
                'name'      => $validated['name'],
                'label'     => $validated['label'] ?? null,
                'plan'      => $plan['name'],
                'plan_id'   => $planId,
                'api_key'   => $plaintext,  // ← SEULE FOIS où le token en clair est exposé
                'monthly_quota'    => $plan['monthly_calls'] === PHP_INT_MAX ? 'unlimited' : $plan['monthly_calls'],
                'rate_per_minute'  => $plan['rate_per_minute'],
                'expires_at'       => $validated['expires_at'] ?? null,
                'created_at'       => $tokenResult->accessToken->created_at->toIso8601String(),
            ],
            'warning' => 'Store this API key securely. It will never be shown again.',
            'docs'    => 'https://reception-par-type.ch/api/docs#authentication',
        ], 201);
    }

    // ── Révoquer une clé ──────────────────────────────────────────────────────

    /**
     * DELETE /api/v1/keys/{keyId}
     * Révoque une clé (soft: is_active = false, puis suppression après 30j).
     */
    public function revoke(Request $request, int $keyId): JsonResponse
    {
        $user = $request->user();
        $key  = $user->tokens()->where('id', $keyId)->first();

        if (!$key) {
            return response()->json([
                'success' => false,
                'error'   => 'NOT_FOUND',
                'message' => 'API key not found.',
            ], 404);
        }

        // Suspension douce : désactivation + conservation pour l'audit
        $key->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => "API key '{$key->name}' has been revoked.",
            'data'    => ['key_id' => $keyId, 'revoked_at' => now()->toIso8601String()],
        ]);
    }

    // ── Statistiques d'une clé ────────────────────────────────────────────────

    /**
     * GET /api/v1/keys/{keyId}/stats
     * Statistiques d'utilisation pour la facturation.
     */
    public function stats(Request $request, int $keyId): JsonResponse
    {
        $user = $request->user();
        $key  = $user->tokens()->where('id', $keyId)->first();

        if (!$key) {
            return response()->json([
                'success' => false, 'error' => 'NOT_FOUND', 'message' => 'API key not found.',
            ], 404);
        }

        // Statistiques depuis api_logs (agrégées)
        $statsThisMonth = ApiLog::where('api_key_id', $keyId)
            ->thisMonth()
            ->selectRaw('
                COUNT(*) as total_calls,
                SUM(billed) as billed_calls,
                AVG(response_time_ms) as avg_response_ms,
                MIN(response_time_ms) as min_response_ms,
                MAX(response_time_ms) as max_response_ms,
                SUM(CASE WHEN status_code = 200 THEN 1 ELSE 0 END) as success_calls,
                SUM(CASE WHEN status_code = 429 THEN 1 ELSE 0 END) as rate_limited_calls,
                SUM(CASE WHEN status_code = 404 THEN 1 ELSE 0 END) as not_found_calls
            ')
            ->first();

        $endpointBreakdown = ApiLog::where('api_key_id', $keyId)
            ->thisMonth()
            ->billed()
            ->selectRaw('endpoint, COUNT(*) as count')
            ->groupBy('endpoint')
            ->orderByDesc('count')
            ->get();

        $monthlyQuota = $key->monthly_quota
            ?? config("api_plans.{$key->plan_id}.monthly_calls");

        return response()->json([
            'success' => true,
            'data'    => [
                'key_id'     => $keyId,
                'key_name'   => $key->name,
                'period'     => now()->format('Y-m'),
                'quota'      => [
                    'limit'     => $monthlyQuota === PHP_INT_MAX ? 'unlimited' : $monthlyQuota,
                    'used'      => (int) $key->calls_this_month,
                    'remaining' => $monthlyQuota === PHP_INT_MAX
                        ? 'unlimited'
                        : max(0, $monthlyQuota - (int) $key->calls_this_month),
                    'reset_date'=> now()->startOfMonth()->addMonth()->toDateString(),
                ],
                'this_month' => [
                    'total_calls'       => (int) ($statsThisMonth->total_calls ?? 0),
                    'billed_calls'      => (int) ($statsThisMonth->billed_calls ?? 0),
                    'success_calls'     => (int) ($statsThisMonth->success_calls ?? 0),
                    'rate_limited'      => (int) ($statsThisMonth->rate_limited_calls ?? 0),
                    'not_found'         => (int) ($statsThisMonth->not_found_calls ?? 0),
                    'avg_response_ms'   => round((float) ($statsThisMonth->avg_response_ms ?? 0)),
                    'success_rate_pct'  => $statsThisMonth->total_calls > 0
                        ? round($statsThisMonth->success_calls / $statsThisMonth->total_calls * 100, 1)
                        : 0,
                ],
                'endpoint_breakdown' => $endpointBreakdown->map(fn($e) => [
                    'endpoint' => $e->endpoint,
                    'calls'    => (int) $e->count,
                ])->all(),
                'all_time_calls' => (int) $key->calls_total,
            ],
        ]);
    }

    // ── Helper privé ──────────────────────────────────────────────────────────

    /**
     * Sanitise les données d'une clé pour la liste :
     * jamais le token hash, jamais les abilities, jamais les champs système.
     */
    private function sanitizeKeyData(PersonalAccessToken $key): array
    {
        $monthlyQuota = $key->monthly_quota
            ?? config("api_plans.{$key->plan_id}.monthly_calls", 5000);

        return [
            'key_id'          => $key->id,
            'name'            => $key->name,
            'label'           => $key->key_label,
            'plan_id'         => $key->plan_id,
            'plan_name'       => config("api_plans.{$key->plan_id}.name", 'Unknown'),
            'quota_limit'     => $monthlyQuota === PHP_INT_MAX ? 'unlimited' : $monthlyQuota,
            'quota_used'      => $key->calls_this_month,
            'total_calls'     => $key->calls_total,
            'is_active'       => (bool) $key->is_active,
            'last_used_at'    => $key->last_used_at?->toIso8601String(),
            'expires_at'      => $key->expires_at?->toIso8601String(),
            'created_at'      => $key->created_at->toIso8601String(),
            // Jamais : token, tokenable_id, abilities, updated_at
        ];
    }
}
