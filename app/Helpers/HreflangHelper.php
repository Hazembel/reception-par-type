<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

/**
 * Helper : HreflangHelper
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Génère automatiquement les balises <link rel="alternate" hreflang="xx" />
 * pour le SEO international (Google International Targeting).
 *
 * Ces balises indiquent aux moteurs de recherche qu'une même page existe
 * en plusieurs langues, évitant le duplicate content et ciblant les bonnes
 * audiences géographiques.
 *
 * Spécification Google :
 * https://developers.google.com/search/docs/specialty/international/localization-versioning
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Usage dans un ServiceProvider (AppServiceProvider) :
 *   Blade::directive('hreflang', fn() => "<?php echo hreflang_tags(); ?>");
 *
 * Usage direct dans une vue Blade :
 *   {!! App\Helpers\HreflangHelper::generate() !!}
 *
 * Ou via la fonction d'aide globale :
 *   {!! hreflang_tags() !!}
 */
class HreflangHelper
{
    /**
     * Mapping locale → hreflang ISO 639-1 + région (pour Google)
     * La Suisse est multilingue, on cible les langues sans région
     * mais on peut affiner pour le marché local.
     *
     * @var array<string, string>
     */
    private const LOCALE_TO_HREFLANG = [
        'fr' => 'fr-CH',  // Français (Suisse romande)
        'de' => 'de-CH',  // Allemand (Suisse alémanique)
        'it' => 'it-CH',  // Italien (Tessin)
        'en' => 'en',     // Anglais (international, sans région)
    ];

    /**
     * Génère les balises hreflang HTML pour toutes les locales supportées.
     *
     * ─────────────────────────────────────────────────────────────────────────
     * Algorithme :
     *  1. Récupère le nom de la route courante
     *  2. Pour chaque locale supportée, génère l'URL équivalente
     *  3. Construit les balises <link> HTML
     *  4. Ajoute x-default pointant vers la locale française (priorité)
     * ─────────────────────────────────────────────────────────────────────────
     *
     * @param  array<string, mixed> $extraParams  Paramètres supplémentaires de route
     *                                            (ex: ['slug' => 'volkswagen-golf'])
     * @return string  HTML des balises hreflang, prêt pour le <head>
     */
    public static function generate(array $extraParams = []): string
    {
        $currentRouteName = Route::currentRouteName();

        // Sécurité : si pas de route nommée, on ne peut pas générer les URLs
        if (empty($currentRouteName)) {
            return '<!-- hreflang: route sans nom, balises non générées -->';
        }

        $tags = [];

        foreach (self::LOCALE_TO_HREFLANG as $locale => $hreflang) {
            try {
                $url = static::buildLocalizedUrl($currentRouteName, $locale, $extraParams);

                $tags[] = sprintf(
                    '<link rel="alternate" hreflang="%s" href="%s" />',
                    e($hreflang),
                    e($url)
                );
            } catch (\Exception $e) {
                // En cas d'erreur de génération d'URL (route inexistante pour cette locale),
                // on saute silencieusement plutôt que de casser la page.
                // Logguer en développement uniquement.
                if (app()->isLocal()) {
                    logger()->warning("hreflang: impossible de générer l'URL pour [{$locale}]", [
                        'route'  => $currentRouteName,
                        'locale' => $locale,
                        'error'  => $e->getMessage(),
                    ]);
                }
            }
        }

        // x-default : pointe vers la version par défaut (FR pour la Suisse).
        // Utilisé quand Google ne peut pas déterminer la langue de l'utilisateur.
        try {
            $defaultUrl = static::buildLocalizedUrl($currentRouteName, 'fr', $extraParams);
            $tags[] = sprintf(
                '<link rel="alternate" hreflang="x-default" href="%s" />',
                e($defaultUrl)
            );
        } catch (\Exception $e) {
            // Fail gracefully
        }

        if (empty($tags)) {
            return '<!-- hreflang: aucune URL générée -->';
        }

        return implode("\n    ", $tags);
    }

    /**
     * Construit l'URL localisée pour une route et une locale données.
     *
     * La route est supposée avoir un paramètre {locale} en préfixe.
     * Exemple : route "fr.vehicle.show" → route "de.vehicle.show"
     *       ou : même nom de route avec paramètre locale différent.
     *
     * @param  string               $routeName
     * @param  string               $locale
     * @param  array<string, mixed> $extraParams
     * @return string
     */
    private static function buildLocalizedUrl(
        string $routeName,
        string $locale,
        array  $extraParams = []
    ): string {
        // Stratégie 1 : Le nom de route contient la locale (ex: "fr.vehicle.show")
        // On remplace le préfixe de locale pour construire le nom équivalent.
        $baseRouteName = preg_replace('/^(fr|de|it|en)\./', '', $routeName);
        $localizedRouteName = "{$locale}.{$baseRouteName}";

        if (Route::has($localizedRouteName)) {
            return route($localizedRouteName, array_merge(['locale' => $locale], $extraParams));
        }

        // Stratégie 2 : Route avec paramètre {locale} (ex: {locale}.search)
        if (Route::has($routeName)) {
            return route($routeName, array_merge(['locale' => $locale], $extraParams));
        }

        // Stratégie 3 : Remplacement simple du segment de locale dans l'URL courante
        $currentUrl = URL::current();
        $localePattern = '/\/(fr|de|it|en)\//';

        if (preg_match($localePattern, $currentUrl)) {
            return preg_replace($localePattern, "/{$locale}/", $currentUrl);
        }

        throw new \RuntimeException("Impossible de construire l'URL localisée pour [{$locale}]");
    }

    /**
     * Génère la balise canonical pour la page courante.
     * Recommandé pour éviter le contenu dupliqué sur les pages paginées.
     *
     * @param  string|null $overrideUrl  URL personnalisée (optionnel)
     * @return string
     */
    public static function canonical(?string $overrideUrl = null): string
    {
        $url = $overrideUrl ?? URL::current();

        return sprintf('<link rel="canonical" href="%s" />', e($url));
    }
}

// ── Fonction d'aide globale ───────────────────────────────────────────────────
// Enregistrée via composer.json "autoload.files" ou AppServiceProvider

if (!function_exists('hreflang_tags')) {
    /**
     * Génère les balises hreflang pour la vue Blade courante.
     *
     * Usage Blade : {!! hreflang_tags() !!}
     * Usage Blade avec params : {!! hreflang_tags(['slug' => $vehicle->slug]) !!}
     *
     * @param  array<string, mixed> $extraParams
     * @return string
     */
    function hreflang_tags(array $extraParams = []): string
    {
        return HreflangHelper::generate($extraParams);
    }
}

if (!function_exists('canonical_tag')) {
    /**
     * Génère la balise canonical pour la vue Blade courante.
     *
     * Usage Blade : {!! canonical_tag() !!}
     *
     * @param  string|null $url
     * @return string
     */
    function canonical_tag(?string $url = null): string
    {
        return HreflangHelper::canonical($url);
    }
}
