<?php

namespace App\Http\Middleware;

use App\Models\Affiliate;
use App\Models\AffiliateClick;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware : TrackAffiliate
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Intercepte le paramètre ?ref=CODE dans l'URL, vérifie le code affilié,
 * enregistre un clic et dépose un cookie sécurisé 30 jours.
 *
 * Sécurités :
 *  - Code validé en BDD avant tout traitement
 *  - IP hachée SHA-256 (RGPD : non stockée en clair)
 *  - Déduplication : 1 clic unique par couple (affiliate_id + ip_hash + jour)
 *  - Cookie HttpOnly + SameSite=Lax + Secure (HTTPS)
 *  - Aucun clic enregistré si affilié inactif ou code invalide
 *
 * Enregistrement global dans bootstrap/app.php :
 *   $middleware->web(append: [TrackAffiliate::class]);
 * ─────────────────────────────────────────────────────────────────────────────
 */
class TrackAffiliate
{
    /** Nom du cookie déposé sur le navigateur */
    public const COOKIE_NAME = 'rpt_ref';

    /** Durée de vie du cookie en minutes (30 jours) */
    private const COOKIE_TTL_MINUTES = 43_200;

    public function handle(Request $request, Closure $next): Response
    {
        $refCode = $request->query('ref');

        if ($refCode !== null) {
            $this->processAffiliateRef($request, (string) $refCode);
        }

        return $next($request);
    }

    private function processAffiliateRef(Request $request, string $rawCode): void
    {
        // Validation du format du code (alphanumérique + tirets, 3-30 chars)
        $code = strtoupper(trim($rawCode));
        if (!preg_match('/^[A-Z0-9\-]{3,30}$/', $code)) {
            return;
        }

        // Lookup de l'affilié en cache (évite une requête BDD à chaque clic)
        $affiliate = \Illuminate\Support\Facades\Cache::remember(
            "affiliate_code:{$code}",
            3600,
            fn() => Affiliate::byCode($code)->active()->first()
        );

        if (!$affiliate) {
            return; // Code invalide ou affilié inactif
        }

        // Hash de l'IP (SHA-256 — RGPD : non réversible)
        $ipHash = hash('sha256', ($request->ip() ?? 'unknown') . config('app.key'));

        // Déduplication : un seul clic comptabilisé par IP et par jour
        $alreadyClicked = AffiliateClick::where('affiliate_id', $affiliate->id)
            ->where('ip_hash', $ipHash)
            ->whereDate('created_at', today())
            ->exists();

        if (!$alreadyClicked) {
            // Enregistrement asynchrone du clic (ne bloque pas la réponse)
            dispatch(function () use ($affiliate, $ipHash, $request) {
                AffiliateClick::create([
                    'affiliate_id' => $affiliate->id,
                    'ip_hash'      => $ipHash,
                    'referrer_url' => mb_substr($request->headers->get('referer', ''), 0, 500),
                    'user_agent'   => mb_substr($request->userAgent() ?? '', 0, 255),
                    'converted'    => false,
                ]);

                // Incrément du compteur dénormalisé
                \DB::table('affiliates')
                    ->where('id', $affiliate->id)
                    ->increment('total_clicks');

            })->afterResponse();
        }

        // Dépôt du cookie (même si déjà cliqué — pour rafraîchir sa durée)
        // Le cookie contient uniquement le code (pas d'info personnelle)
        cookie()->queue(
            cookie(
                name:     self::COOKIE_NAME,
                value:    $code,                          // Valeur : code affilié lisible
                minutes:  self::COOKIE_TTL_MINUTES,       // 30 jours
                path:     '/',
                domain:   null,
                secure:   app()->isProduction(),          // HTTPS uniquement en prod
                httpOnly: true,                           // Non accessible via JS
                sameSite: 'Lax',                          // CSRF protection
            )
        );
    }
}
