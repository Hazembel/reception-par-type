<?php

// ── Fonctions d'aide globale ──────────────────────────────────────────────────

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
        return \App\Helpers\HreflangHelper::generate($extraParams);
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
        return \App\Helpers\HreflangHelper::canonical($url);
    }
}
