<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware : SetLocale
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Responsabilités :
 *  1. Extraire la locale depuis le segment d'URL (ex: /fr/search → "fr")
 *  2. Valider STRICTEMENT contre la liste blanche des locales supportées
 *  3. En cas d'URL invalide → abort(400) (Bad Request)
 *  4. Configurer l'application Laravel avec la bonne locale
 *  5. Stocker la locale en session pour les redirections internes
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Enregistrement dans bootstrap/app.php :
 *   ->withMiddleware(function (Middleware $middleware) {
 *       $middleware->alias(['setlocale' => SetLocale::class]);
 *   })
 *
 * Usage dans les routes :
 *   Route::prefix('{locale}')->middleware('setlocale')->group(...)
 */
class SetLocale
{
    /**
     * Liste blanche des locales autorisées.
     * ATTENTION : toute valeur absente de cette liste → HTTP 400.
     *
     * @var array<string>
     */
    private const ALLOWED_LOCALES = ['fr', 'de', 'it', 'en'];

    /**
     * Locale de fallback si aucune session n'est disponible.
     */
    private const DEFAULT_LOCALE = 'fr';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ── Étape 1 : Extraction de la locale depuis le segment de route ──────
        $locale = $request->route('locale');

        // ── Étape 2 : Validation stricte (liste blanche) ─────────────────────
        if (!in_array($locale, self::ALLOWED_LOCALES, true)) {
            /*
             * La locale est absente ou invalide.
             * On renvoie une erreur 400 Bad Request (pas 404) car l'URL
             * est malformée intentionnellement (ex: /xx/search, /admin/search).
             *
             * Cela :
             *  - Protège contre le scraping ciblé
             *  - Évite l'indexation Google de locales fantômes
             *  - Retourne une réponse explicite pour le debugging en dev
             */
            abort(400, sprintf(
                'Locale "%s" invalide. Les locales supportées sont : %s',
                e((string) $locale),
                implode(', ', self::ALLOWED_LOCALES)
            ));
        }

        // ── Étape 3 : Application de la locale à Laravel ──────────────────────
        app()->setLocale($locale);

        // Carbon utilise la locale pour formater les dates (ex: "lundi 12 juin 2024")
        // Nécessite l'extension PHP intl
        if (class_exists('\Carbon\Carbon')) {
            \Carbon\Carbon::setLocale($locale);
        }

        // ── Étape 4 : Persistance en session ─────────────────────────────────
        // Permet aux formulaires POST et aux redirections internes de conserver la locale
        $request->session()->put('locale', $locale);

        // ── Étape 5 : Header HTTP pour les proxies et CDN ────────────────────
        $response = $next($request);

        $response->headers->set('Content-Language', $locale);
        $response->headers->set('Vary', 'Accept-Language'); // Hint pour les caches HTTP

        return $response;
    }
}
