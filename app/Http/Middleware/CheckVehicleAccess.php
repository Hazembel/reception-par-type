<?php

namespace App\Http\Middleware;

use App\Models\Vehicle;
use App\Models\UserUnlockedVehicle;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware : CheckVehicleAccess
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Matrice complète de contrôle d'accès aux données avancées d'une fiche
 * technique véhicule. Exécuté APRÈS les middlewares `auth` et `verified`.
 *
 * Niveaux et règles :
 *
 * Niveau 1 (Gratuit) :
 *   → Données de base uniquement (motorisation, émissions)
 *   → Données avancées masquées côté PHP (Anti-Cloaking — rien dans le HTML)
 *   → Affichage du CTA upgrade (2 CHF à l'unité ou abonnement)
 *
 * Niveaux 2–3 (Starter / Basic — Jetons) :
 *   → Vérifier si le véhicule est déjà débloqué ce mois-ci
 *     → OUI : accès direct (0 jeton)
 *     → NON : décrémenter 1 jeton + enregistrer le déverrouillage
 *     → Jetons épuisés : afficher CTA recharge / upgrade
 *
 * Niveau 4 (Pro — Quota mensuel 500 fiches) :
 *   → Incrémenter web_monthly_counter
 *   → Si > 500 : bloquer + CTA upgrade vers niveau 5
 *   → Sinon : accès total
 *
 * Niveaux 5–8 (Pro+ / Business / Enterprise) :
 *   → Accès total et illimité, aucune vérification de quota
 *
 * Paramètres de route requis : {vehicle} (résolution par slug via Route Model Binding)
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Enregistrement dans bootstrap/app.php :
 *   $middleware->alias(['vehicle.access' => CheckVehicleAccess::class]);
 *
 * Usage sur la route :
 *   Route::get('/vehicle/{vehicle:slug}', [VehicleController::class, 'show'])
 *       ->middleware(['auth', 'verified', 'vehicle.access']);
 */
class CheckVehicleAccess
{
    /**
     * Limite mensuelle pour le niveau Pro (niveau 4).
     * Configurable dans config/freemium.php si nécessaire.
     */
    private const PRO_MONTHLY_LIMIT = 500;

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // ── Utilisateur non connecté : accès partiel (niveau 1 implicite) ────
        if (!$user) {
            $request->attributes->set('vehicle_access', 'partial');
            $request->attributes->set('can_view_advanced', false);
            return $next($request);
        }

        $vehicle = $request->route('vehicle');
        $level   = (int) $user->subscription_level;

        // ── Niveau 1 : Gratuit — données de base uniquement ──────────────────
        if ($level <= 1) {
            $request->attributes->set('vehicle_access', 'basic');
            $request->attributes->set('can_view_advanced', false);
            $request->attributes->set('access_reason', 'level_1_free');
            return $next($request);
        }

        // ── Niveaux 5–8 : Accès total illimité (fast path) ───────────────────
        if ($level >= 5) {
            $request->attributes->set('vehicle_access', 'full');
            $request->attributes->set('can_view_advanced', true);
            $request->attributes->set('access_reason', 'unlimited');
            // [CORRECTIF BUG #6] Pas d'incrément de compteur pour les illimités :
            // écriture BDD inutile sur chaque page vue. Les stats d'usage passent
            // par api_logs ou un compteur cache si nécessaire.
            return $next($request);
        }

        // ── Vérification de l'abonnement actif (niveaux 2–4) ─────────────────
        if (!$user->hasActiveSubscription()) {
            // Abonnement expiré → retomber au niveau 1
            $request->attributes->set('vehicle_access', 'basic');
            $request->attributes->set('can_view_advanced', false);
            $request->attributes->set('access_reason', 'subscription_expired');
            return $next($request);
        }

        // ── Niveaux 2–3 : Système de jetons ──────────────────────────────────
        if ($level <= 3) {
            return $this->handleTokenAccess($request, $next, $user, $vehicle);
        }

        // ── Niveau 4 : Pro — quota mensuel 500 fiches ────────────────────────
        if ($level === 4) {
            return $this->handleProQuota($request, $next, $user);
        }

        // Fallback sécuritaire (ne devrait jamais être atteint)
        $request->attributes->set('vehicle_access', 'basic');
        $request->attributes->set('can_view_advanced', false);
        return $next($request);
    }

    // ── Niveau 2–3 : Logique des jetons ──────────────────────────────────────

    /**
     * Vérifie si le véhicule est déjà débloqué ce mois-ci.
     * Si oui → accès gratuit. Sinon → décrémenter 1 jeton.
     */
    private function handleTokenAccess(
        Request $request,
        Closure $next,
        $user,
        $vehicle
    ): Response {
        if (!$vehicle) {
            $request->attributes->set('can_view_advanced', false);
            return $next($request);
        }

        // Vérification du déverrouillage existant CE mois-ci
        $alreadyUnlocked = UserUnlockedVehicle::where('user_id', $user->id)
            ->where('vehicle_id', $vehicle->id)
            ->whereYear('unlocked_at', now()->year)
            ->whereMonth('unlocked_at', now()->month)
            ->exists();

        if ($alreadyUnlocked) {
            // Déjà payé ce mois-ci → accès sans coût
            $request->attributes->set('vehicle_access', 'full');
            $request->attributes->set('can_view_advanced', true);
            $request->attributes->set('access_reason', 'already_unlocked_this_month');
            return $next($request);
        }

        // Vérification du solde de jetons
        if ($user->web_tokens_balance < 1) {
            $request->attributes->set('vehicle_access', 'blocked');
            $request->attributes->set('can_view_advanced', false);
            $request->attributes->set('access_reason', 'insufficient_tokens');
            $request->attributes->set('tokens_balance', $user->web_tokens_balance);
            return $next($request);
        }

        // Déverrouillage atomique : décrémentation + insertion dans une transaction.
        // consumeTokens() effectue un décrément CONDITIONNEL côté base : si une
        // requête concurrente a déjà consommé le dernier jeton entre la vérification
        // ligne 150 et ici, le décrément renvoie false et on N'OUVRE PAS l'accès.
        $tokenConsumed = false;

        DB::transaction(function () use ($user, $vehicle, &$tokenConsumed) {
            $tokenConsumed = $user->consumeTokens(1);

            // On n'enregistre le déverrouillage QUE si un jeton a réellement été débité.
            if ($tokenConsumed) {
                UserUnlockedVehicle::updateOrCreate(
                    [
                        'user_id'    => $user->id,
                        'vehicle_id' => $vehicle->id,
                    ],
                    [
                        'unlocked_at'  => now(),
                        'tokens_spent' => 1,
                    ]
                );
            }
        });

        // Si le jeton n'a pas pu être débité (course / solde épuisé) → accès refusé.
        if (!$tokenConsumed) {
            $request->attributes->set('vehicle_access', 'blocked');
            $request->attributes->set('can_view_advanced', false);
            $request->attributes->set('access_reason', 'insufficient_tokens');
            // No DB write occurred — in-memory balance is still authoritative.
            $request->attributes->set('tokens_balance', $user->web_tokens_balance);
            return $next($request);
        }

        $request->attributes->set('vehicle_access', 'full');
        $request->attributes->set('can_view_advanced', true);
        $request->attributes->set('access_reason', 'token_spent');
        $request->attributes->set('tokens_balance', $user->fresh()->web_tokens_balance);

        return $next($request);
    }

    // ── Niveau 4 : Quota mensuel Pro ──────────────────────────────────────────

    /**
     * Vérifie et incrémente le compteur mensuel pour les abonnés Pro.
     * Bloque si la limite de 500 fiches/mois est atteinte.
     */
    private function handleProQuota(Request $request, Closure $next, $user): Response
    {
        $limit = config('freemium.monthly_limits.4', self::PRO_MONTHLY_LIMIT);

        // Single atomic conditional increment: the WHERE guard closes the race window
        // where two concurrent requests could both read counter < limit and both pass.
        $affected = DB::table('users')
            ->where('id', $user->id)
            ->where('web_monthly_counter', '<', $limit)
            ->increment('web_monthly_counter');

        if ($affected === 0) {
            // Quota épuisé (or concurrent request consumed the last slot)
            $request->attributes->set('vehicle_access', 'blocked');
            $request->attributes->set('can_view_advanced', false);
            $request->attributes->set('access_reason', 'pro_quota_exceeded');
            $request->attributes->set('monthly_limit', $limit);
            $request->attributes->set('monthly_counter', (int) $user->web_monthly_counter);
            return $next($request);
        }

        $newCounter = $user->web_monthly_counter + 1;

        $request->attributes->set('vehicle_access', 'full');
        $request->attributes->set('can_view_advanced', true);
        $request->attributes->set('access_reason', 'pro_quota_ok');
        $request->attributes->set('monthly_remaining', $limit - $newCounter);

        return $next($request);
    }
}
