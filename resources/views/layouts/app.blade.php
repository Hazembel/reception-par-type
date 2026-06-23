{{--
    Layout principal : resources/views/layouts/app.blade.php
    reception-par-type.ch — Design System "Nuit Alpine × Précision Helvétique"

    Fonctionnalités :
    - Navbar fixe effet verre dépoli (glassmorphism)
    - Mode sombre/clair automatique (préférence système + toggle utilisateur)
    - Alpine.js pour le toggle dark mode et la navbar shrink au scroll
    - Hreflang + canonical + Open Graph injectés automatiquement
    - CSS custom properties pour le design system
--}}
<!DOCTYPE html>
<html
    lang="{{ app()->getLocale() }}"
    dir="ltr"
    x-data="themeManager()"
    x-bind:class="{ 'dark': isDark }"
    class="scroll-smooth"
>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    {{-- ══ SEO ════════════════════════════════════════════════════════════════ --}}
    <title>{{ $meta_title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $meta_description ?? '' }}" />
    <meta name="robots" content="{{ $meta_robots ?? 'index, follow' }}" />
    {!! canonical_tag() !!}
    {!! hreflang_tags($hreflang_params ?? []) !!}

    {{-- ══ Open Graph ════════════════════════════════════════════════════════ --}}
    <meta property="og:type"        content="website" />
    <meta property="og:title"       content="{{ $meta_title ?? config('app.name') }}" />
    <meta property="og:description" content="{{ $meta_description ?? '' }}" />
    <meta property="og:url"         content="{{ request()->url() }}" />
    <meta property="og:site_name"   content="reception-par-type.ch" />
    <meta property="og:locale"      content="{{ app()->getLocale() }}_CH" />

    {{-- ══ Fonts ══════════════════════════════════════════════════════════════ --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />

    {{-- ══ Favicon ════════════════════════════════════════════════════════════ --}}
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />

    {{-- ══ Assets (Vite) ══════════════════════════════════════════════════════ --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- ══ CSS Custom Properties (Design Tokens) ═════════════════════════════ --}}
    <style>
        :root {
            --color-astra:     #2563EB;
            --color-spark:     #38BDF8;
            --color-night:     #0A0F1E;
            --color-glacier:   #F0F4FF;
            --color-marine:    #1E3A5F;
            --color-slate:     #64748B;
            --navbar-height:   64px;
            --transition-base: 200ms ease;
        }

        /* Réinitialisation minimale */
        *, *::before, *::after { box-sizing: border-box; }

        /* Scrollbar sur-mesure (dark) */
        ::-webkit-scrollbar            { width: 6px; }
        ::-webkit-scrollbar-track      { background: transparent; }
        ::-webkit-scrollbar-thumb      { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover{ background: #2563EB; }

        /* Sélection de texte */
        ::selection { background: rgba(37,99,235,0.25); color: inherit; }

        /* Respect reduced-motion */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Focus visible accessible */
        :focus-visible {
            outline: 2px solid var(--color-astra);
            outline-offset: 3px;
        }
    </style>

    @stack('head')
</head>

<body
    class="
        font-body antialiased
        bg-glacier dark:bg-night
        text-slate-800 dark:text-slate-200
        transition-colors duration-300
    "
>
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- NAVBAR : Verre dépoli fixe                                           --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <header
        x-data="navbarManager()"
        x-bind:class="{
            'bg-white/80 dark:bg-night/80 backdrop-blur-xl shadow-sm border-b border-white/20 dark:border-white/5': scrolled,
            'bg-transparent': !scrolled
        }"
        class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
        style="height: var(--navbar-height)"
    >
        <nav class="max-w-7xl mx-auto h-full px-4 sm:px-6 flex items-center justify-between">

            {{-- Logo --}}
            <a href="{{ route(app()->getLocale() . '.home', ['locale' => app()->getLocale()]) }}"
               class="flex items-center gap-2.5 group"
               aria-label="reception-par-type.ch — Accueil">
                {{-- Icône vectorielle (compteur de vitesse stylisé) --}}
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" class="transition-transform duration-300 group-hover:rotate-12">
                    <circle cx="16" cy="16" r="14" stroke="#2563EB" stroke-width="2" fill="none"/>
                    <circle cx="16" cy="16" r="9"  stroke="#38BDF8" stroke-width="1.5" stroke-dasharray="3 2" fill="none" class="animate-spin-slow origin-center" style="transform-origin: 16px 16px"/>
                    <path d="M10 20 L16 12 L22 20" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    <circle cx="16" cy="16" r="2" fill="#38BDF8"/>
                </svg>
                <div class="leading-none">
                    <span class="font-display text-sm font-normal text-slate-900 dark:text-white tracking-tight">
                        réception<span class="text-astra">-par-type</span>.ch
                    </span>
                </div>
            </a>

            {{-- Navigation centrale (desktop) --}}
            <nav class="hidden md:flex items-center gap-1" role="navigation">
                @php
                    $locale = app()->getLocale();
                    $navLinks = [
                        ['route' => 'search',   'label' => __('app.nav.search')],
                        ['route' => 'compare',  'label' => __('app.nav.compare')],
                        ['route' => 'pricing',  'label' => __('app.nav.pricing')],
                    ];
                @endphp

                @foreach($navLinks as $link)
                    <a href="{{ route("{$locale}.{$link['route']}", ['locale' => $locale]) }}"
                       class="
                           px-3.5 py-1.5 rounded-lg text-sm font-medium
                           text-slate-600 dark:text-slate-300
                           hover:text-slate-900 dark:hover:text-white
                           hover:bg-black/5 dark:hover:bg-white/10
                           transition-all duration-150
                       ">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- Actions droite --}}
            <div class="flex items-center gap-2">

                {{-- Sélecteur de langue --}}
                <div x-data="{ open: false }" class="relative">
                    <button
                        x-on:click="open = !open"
                        x-on:click.outside="open = false"
                        class="
                            flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-sm font-medium
                            text-slate-600 dark:text-slate-300
                            hover:bg-black/5 dark:hover:bg-white/10
                            transition-all duration-150
                        "
                        aria-label="Changer de langue"
                    >
                        <span class="font-mono text-xs">{{ strtoupper(app()->getLocale()) }}</span>
                        <svg class="w-3.5 h-3.5 transition-transform duration-150" x-bind:class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="
                            absolute right-0 top-full mt-1 w-28
                            bg-white dark:bg-marine-800
                            border border-slate-200 dark:border-white/10
                            rounded-xl shadow-xl shadow-black/10 dark:shadow-black/30
                            overflow-hidden z-50
                        "
                    >
                        @foreach(['fr' => '🇫🇷 FR', 'de' => '🇩🇪 DE', 'it' => '🇮🇹 IT', 'en' => '🇬🇧 EN'] as $loc => $label)
                            <a href="/{{ $loc }}/"
                               class="
                                   flex items-center px-3 py-2 text-sm
                                   {{ app()->getLocale() === $loc
                                       ? 'text-astra font-semibold bg-astra/5 dark:bg-astra/10'
                                       : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5' }}
                                   transition-colors duration-100
                               ">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Toggle Dark Mode --}}
                <button
                    x-on:click="toggleTheme()"
                    class="
                        p-2 rounded-lg
                        text-slate-600 dark:text-slate-300
                        hover:bg-black/5 dark:hover:bg-white/10
                        transition-all duration-150
                    "
                    x-bind:aria-label="isDark ? 'Activer le mode clair' : 'Activer le mode sombre'"
                >
                    {{-- Soleil (mode clair) --}}
                    <svg x-show="isDark" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M18.364 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                    </svg>
                    {{-- Lune (mode sombre) --}}
                    <svg x-show="!isDark" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                {{-- CTA Auth --}}
                @auth
                    <a href="{{ route(app()->getLocale() . '.profile', ['locale' => app()->getLocale()]) }}"
                       class="
                           px-3.5 py-1.5 rounded-lg text-sm font-medium
                           text-slate-700 dark:text-slate-200
                           hover:bg-black/5 dark:hover:bg-white/10
                           transition-all duration-150
                       ">
                        {{ __('app.nav.profile') }}
                    </a>
                @else
                    <a href="{{ route(app()->getLocale() . '.login', ['locale' => app()->getLocale()]) }}"
                       class="
                           px-4 py-1.5 rounded-lg text-sm font-medium
                           bg-astra hover:bg-astra-600
                           text-white
                           shadow-sm shadow-astra/20 hover:shadow-astra/40
                           transition-all duration-200
                       ">
                        {{ __('app.nav.login') }}
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- CONTENU PRINCIPAL                                                    --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <main id="main-content" role="main" class="min-h-screen">
        @yield('content')
    </main>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- FOOTER                                                               --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <footer class="border-t border-slate-200 dark:border-white/5 mt-20">
        <div class="max-w-7xl mx-auto px-4 py-12 sm:px-6">
            <div class="flex flex-col sm:flex-row justify-between items-start gap-6">
                <div>
                    <span class="font-display text-sm text-slate-900 dark:text-white">
                        réception<span class="text-astra">-par-type</span>.ch
                    </span>
                    <p class="mt-1 text-xs text-slate-400 max-w-xs">
                        Données techniques ASTRA — Homologations suisses.
                        Non affilié à l'OFROU/ASTRA.
                    </p>
                </div>
                <div class="flex gap-6 text-xs text-slate-500 dark:text-slate-400">
                    <a href="{{ route(app()->getLocale() . '.legal',   ['locale' => app()->getLocale()]) }}" class="hover:text-slate-900 dark:hover:text-white transition-colors">Mentions légales</a>
                    <a href="{{ route(app()->getLocale() . '.privacy', ['locale' => app()->getLocale()]) }}" class="hover:text-slate-900 dark:hover:text-white transition-colors">Confidentialité</a>
                    <a href="{{ route(app()->getLocale() . '.terms',   ['locale' => app()->getLocale()]) }}" class="hover:text-slate-900 dark:hover:text-white transition-colors">CGU</a>
                </div>
            </div>
            <p class="mt-8 text-2xs text-slate-400 dark:text-slate-600">
                © {{ date('Y') }} reception-par-type.ch — Données ASTRA (OFROU) — Tous droits réservés
            </p>
        </div>
    </footer>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- ALPINE.JS — COMPOSANTS GLOBAUX                                       --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <script>
        /**
         * Gestionnaire de thème — Dark Mode
         * Priorité : localStorage > préférence système
         */
        function themeManager() {
            return {
                isDark: false,

                init() {
                    // Lecture de la préférence sauvegardée ou du système
                    const saved = localStorage.getItem('theme');
                    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    this.isDark = saved ? saved === 'dark' : systemDark;

                    // Écoute les changements système en temps réel
                    window.matchMedia('(prefers-color-scheme: dark)')
                        .addEventListener('change', (e) => {
                            if (!localStorage.getItem('theme')) {
                                this.isDark = e.matches;
                            }
                        });
                },

                toggleTheme() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                }
            };
        }

        /**
         * Gestionnaire de navbar — Effet verre dépoli au scroll
         */
        function navbarManager() {
            return {
                scrolled: false,

                init() {
                    // IntersectionObserver = plus performant que l'event scroll
                    const sentinel = document.createElement('div');
                    sentinel.style.cssText = 'position:absolute;top:80px;left:0;width:1px;height:1px;';
                    document.body.prepend(sentinel);

                    new IntersectionObserver(
                        ([entry]) => { this.scrolled = !entry.isIntersecting; },
                        { threshold: 0 }
                    ).observe(sentinel);
                }
            };
        }
    </script>

    @stack('scripts')
</body>
</html>
