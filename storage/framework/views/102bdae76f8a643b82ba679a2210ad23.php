
<!DOCTYPE html>
<html
    lang="<?php echo e(app()->getLocale()); ?>"
    dir="ltr"
    x-data="themeManager()"
    x-bind:class="{ 'dark': isDark }"
    class="h-full scroll-smooth"
>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />
    <title><?php echo $__env->yieldContent('title', config('app.name')); ?></title>
    <meta name="robots" content="noindex, nofollow" />

    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />

    
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
            --color-astra:   #2563EB;
            --color-spark:   #38BDF8;
            --color-night:   #0A0F1E;
            --color-glacier: #F0F4FF;
            --color-marine:  #1E3A5F;
        }
        *, *::before, *::after { box-sizing: border-box; }
        ::selection { background: rgba(37,99,235,.25); color: inherit; }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                transition-duration: .01ms !important;
            }
        }
        :focus-visible { outline: 2px solid var(--color-astra); outline-offset: 3px; }
    </style>

    <?php echo $__env->yieldPushContent('head'); ?>
</head>

<body class="font-body antialiased h-full bg-white dark:bg-night text-slate-800 dark:text-slate-200">

    <?php echo $__env->yieldContent('content'); ?>

    
    <script>
        function themeManager() {
            return {
                isDark: false,
                init() {
                    const saved = localStorage.getItem('theme');
                    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    this.isDark = saved ? saved === 'dark' : systemDark;
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                        if (!localStorage.getItem('theme')) this.isDark = e.matches;
                    });
                },
                toggleTheme() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                }
            };
        }
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/layouts/auth.blade.php ENDPATH**/ ?>