
<!DOCTYPE html>
<html
    lang="<?php echo e(app()->getLocale()); ?>"
    dir="ltr"
    x-data="themeManager()"
    x-bind:class="{ 'dark': isDark }"
    class="scroll-smooth"
>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />

    
    <title><?php echo e($meta_title ?? config('app.name')); ?></title>
    <meta name="description" content="<?php echo e($meta_description ?? ''); ?>" />
    <meta name="robots" content="<?php echo e($meta_robots ?? 'index, follow'); ?>" />
    <?php echo canonical_tag(); ?>

    <?php echo hreflang_tags($hreflang_params ?? []); ?>


    
    <meta property="og:type"        content="website" />
    <meta property="og:title"       content="<?php echo e($meta_title ?? config('app.name')); ?>" />
    <meta property="og:description" content="<?php echo e($meta_description ?? ''); ?>" />
    <meta property="og:url"         content="<?php echo e(request()->url()); ?>" />
    <meta property="og:site_name"   content="reception-par-type.ch" />
    <meta property="og:locale"      content="<?php echo e(app()->getLocale()); ?>_CH" />

    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" onload="this.onload=null;this.rel='stylesheet'" />
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" /></noscript>

    
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />

    
    
    <script>
        (function () {
            var saved = localStorage.getItem('theme');
            var systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && systemDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    
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

    <?php echo $__env->yieldPushContent('head'); ?>
</head>

<body
    class="
        font-body antialiased
        bg-glacier dark:bg-night
        text-slate-800 dark:text-slate-200
        transition-colors duration-300
    "
>
    
    
    
    <header
        x-data="navbarManager()"
        x-bind:class="{
            'shadow-sm border-b border-white/10 dark:border-white/5': scrolled,
            'border-b border-transparent': !scrolled
        }"
        class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-white/80 dark:bg-night/85 backdrop-blur-xl"
        style="height: var(--navbar-height)"
    >
        <nav class="max-w-7xl mx-auto h-full px-4 sm:px-6 flex items-center justify-between">

            
            <a href="<?php echo e(route('home', ['locale' => app()->getLocale()])); ?>"
               class="flex items-center gap-2.5 group"
               aria-label="reception-par-type.ch — Accueil">
                
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

            
            <nav class="hidden md:flex items-center gap-1" role="navigation">
                <?php
                    $locale = app()->getLocale();
                    $navLinks = [
                        ['route' => 'search',   'label' => __('app.nav.search')],
                        ['route' => 'compare',  'label' => __('app.nav.compare')],
                        ['route' => 'pricing',  'label' => __('app.nav.pricing')],
                    ];
                ?>

                <?php $__currentLoopData = $navLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route($link['route'], ['locale' => $locale])); ?>"
                       class="
                           px-3.5 py-1.5 rounded-lg text-sm font-medium
                           text-slate-600 dark:text-slate-300
                           hover:text-slate-900 dark:hover:text-white
                           hover:bg-black/5 dark:hover:bg-white/10
                           transition-all duration-150
                       ">
                        <?php echo e($link['label']); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>

            
            <div class="flex items-center gap-2">

                
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
                        <span class="font-mono text-xs"><?php echo e(strtoupper(app()->getLocale())); ?></span>
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
                        <?php $__currentLoopData = ['fr' => '🇫🇷 FR', 'de' => '🇩🇪 DE', 'it' => '🇮🇹 IT', 'en' => '🇬🇧 EN']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $localizedUrl = "/{$loc}/";
                                try {
                                    $currentRoute = Route::current();
                                    if ($currentRoute && $currentRoute->getName()) {
                                        $routeParams = Route::current()->parameters();
                                        $routeParams['locale'] = $loc;
                                        $localizedUrl = route($currentRoute->getName(), array_merge($routeParams, request()->query()));
                                    } else {
                                        $currentPath = request()->path();
                                        $segments = explode('/', $currentPath);
                                        if (count($segments) > 0 && in_array($segments[0], ['fr', 'de', 'it', 'en'])) {
                                            $segments[0] = $loc;
                                            $localizedUrl = '/' . implode('/', $segments) . (request()->getQueryString() ? '?' . request()->getQueryString() : '');
                                        }
                                    }
                                } catch (\Exception $e) {
                                    $localizedUrl = "/{$loc}/";
                                }
                            ?>
                            <a href="<?php echo e($localizedUrl); ?>"
                               class="
                                   flex items-center px-3 py-2 text-sm
                                   <?php echo e(app()->getLocale() === $loc
                                       ? 'text-astra font-semibold bg-astra/5 dark:bg-astra/10'
                                       : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5'); ?>

                                   transition-colors duration-100
                               ">
                                <?php echo e($label); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                
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
                    
                    <svg x-show="isDark" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M18.364 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                    </svg>
                    
                    <svg x-show="!isDark" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                
                <?php if(auth()->guard()->check()): ?>
                    <?php if(auth()->user()->isAdmin()): ?>
                        
                        <div x-data="{ open: false }" class="relative">
                            <button
                                x-on:click="open = !open"
                                x-on:click.outside="open = false"
                                class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-sm font-medium
                                       text-slate-700 dark:text-slate-200
                                       hover:bg-black/5 dark:hover:bg-white/10
                                       transition-all duration-150">
                                <?php echo e(__('app.nav.profile')); ?>

                                <svg class="w-3.5 h-3.5 opacity-60 transition-transform duration-150"
                                     :class="{ 'rotate-180': open }"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open" x-cloak x-transition
                                 class="absolute right-0 mt-2 w-52 rounded-xl
                                        bg-white dark:bg-marine-900
                                        border border-slate-200 dark:border-white/10
                                        shadow-xl shadow-slate-900/10
                                        py-1.5 z-50">
                                <a href="<?php echo e(route('admin.dashboard')); ?>"
                                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm
                                          text-slate-700 dark:text-slate-200
                                          hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                    <svg class="w-4 h-4 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    Tableau de bord
                                </a>
                                <a href="<?php echo e(route('account.profile.show', ['locale' => app()->getLocale()])); ?>"
                                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm
                                          text-slate-700 dark:text-slate-200
                                          hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                    <svg class="w-4 h-4 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                    </svg>
                                    Modifier mon profil
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo e(route('account.profile.show', ['locale' => app()->getLocale()])); ?>"
                           class="px-3.5 py-1.5 rounded-lg text-sm font-medium
                                  text-slate-700 dark:text-slate-200
                                  hover:bg-black/5 dark:hover:bg-white/10
                                  transition-all duration-150">
                            <?php echo e(__('app.nav.profile')); ?>

                        </a>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                                class="
                                    px-3.5 py-1.5 rounded-lg text-sm font-medium
                                    text-slate-600 dark:text-slate-300
                                    hover:bg-black/5 dark:hover:bg-white/10
                                    transition-all duration-150
                                ">
                            <?php echo e(__('app.nav.logout')); ?>

                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo e(route('login', ['locale' => app()->getLocale()])); ?>"
                       class="
                           px-4 py-1.5 rounded-lg text-sm font-medium
                           bg-astra hover:bg-astra-600
                           text-white
                           shadow-sm shadow-astra/20 hover:shadow-astra/40
                           transition-all duration-200
                       ">
                        <?php echo e(__('app.nav.login')); ?>

                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    
    
    
    <main id="main-content" role="main" class="min-h-screen" style="padding-top: var(--navbar-height)">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    
    
    
    <footer class="border-t border-slate-200 dark:border-white/5 mt-20">
        <div class="max-w-7xl mx-auto px-4 py-12 sm:px-6">
            <div class="flex flex-col sm:flex-row justify-between items-start gap-6">
                <div>
                    <span class="font-display text-sm text-slate-900 dark:text-white">
                        réception<span class="text-astra">-par-type</span>.ch
                    </span>
                    <p class="mt-1 text-xs text-slate-400 max-w-xs">
                        <?php echo e(__('app.footer.desc_line1')); ?><br>
                        <?php echo e(__('app.footer.desc_line2')); ?>

                    </p>
                </div>
                <div class="flex gap-6 text-xs text-slate-500 dark:text-slate-400">
                    <a href="<?php echo e(route('legal', ['locale' => app()->getLocale()])); ?>" class="hover:text-slate-900 dark:hover:text-white transition-colors"><?php echo e(__('app.footer.legal')); ?></a>
                    <a href="<?php echo e(route('privacy', ['locale' => app()->getLocale()])); ?>" class="hover:text-slate-900 dark:hover:text-white transition-colors"><?php echo e(__('app.footer.privacy')); ?></a>
                    <a href="<?php echo e(route('terms', ['locale' => app()->getLocale()])); ?>" class="hover:text-slate-900 dark:hover:text-white transition-colors"><?php echo e(__('app.footer.terms')); ?></a>
                </div>
            </div>
            <p class="mt-8 text-2xs text-slate-400 dark:text-slate-600">
                © <?php echo e(date('Y')); ?> reception-par-type.ch — Données ASTRA (OFROU) — Tous droits réservés
            </p>
        </div>
    </footer>

    
    
    
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

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/layouts/app.blade.php ENDPATH**/ ?>