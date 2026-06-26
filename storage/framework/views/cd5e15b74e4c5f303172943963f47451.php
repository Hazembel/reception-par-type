<?php $__env->startSection('content'); ?>


<section
    class="relative min-h-[92vh] flex flex-col items-center justify-center overflow-hidden"
    x-data="heroStats()"
    x-init="startCounters()"
>
    
    <div class="absolute inset-0 -z-20">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-night dark:via-night-800 dark:to-night-900"></div>
        <div class="absolute top-[-15%] right-[-5%] w-[500px] h-[500px] rounded-full bg-astra/6 blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[400px] h-[400px] rounded-full bg-spark/5 blur-[80px] animate-pulse" style="animation-delay:1.5s"></div>
    </div>

    
    <div class="absolute inset-0 -z-10 opacity-[0.03] dark:opacity-[0.06]"
         style="background-image:radial-gradient(circle,#2563EB 1px,transparent 1px);background-size:28px 28px"></div>

    
    <div class="mb-8 flex flex-col items-center" data-aos="fade-down" style="animation:fadeDown .7s ease both">
        <div class="flex items-center gap-4">
            
            <div class="w-14 h-14 rounded-xl flex items-center justify-center shadow-lg" style="background:#C8102E">
                <svg viewBox="0 0 40 40" class="w-9 h-9" aria-hidden="true">
                    <rect x="15" y="6" width="10" height="28" rx="1.5" fill="white"/>
                    <rect x="6" y="15" width="28" height="10" rx="1.5" fill="white"/>
                </svg>
            </div>
            <div>
                <div class="font-display font-bold text-3xl tracking-tight text-slate-900 dark:text-white leading-none">
                    reception-par-type<span class="text-red-600">.ch</span>
                </div>
                <div class="text-xs font-semibold tracking-widest uppercase text-slate-400 mt-1">
                    <?php echo e(__('home.hero.tagline')); ?>

                </div>
            </div>
        </div>
        
        <div class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-astra/20 bg-astra/5 text-xs font-semibold text-astra">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <?php echo e(__('home.hero.badge')); ?>

        </div>
    </div>

    
    <div class="text-center px-4 mb-8" style="animation:fadeUp .8s .15s ease both;opacity:0">
        <h1 class="font-display font-bold text-4xl sm:text-5xl md:text-6xl text-slate-900 dark:text-white max-w-3xl leading-tight tracking-tight">
            <?php echo e(__('home.hero.title')); ?><br>
            <span class="text-astra"><?php echo e(__('home.hero.title_highlight')); ?></span><?php echo e(__('home.hero.title_suffix')); ?>

        </h1>
        <p class="mt-4 text-lg text-slate-500 dark:text-slate-400 max-w-2xl mx-auto leading-relaxed">
            <?php echo e(__('home.hero.subtitle')); ?>

        </p>
    </div>

    
    <div class="w-full max-w-2xl px-4 mb-12" style="animation:fadeUp .8s .3s ease both;opacity:0">
        <form method="POST" action="<?php echo e(route('search.results', ['locale' => app()->getLocale()])); ?>"
              class="flex gap-2 p-1.5 rounded-2xl bg-white dark:bg-marine/40 border border-slate-200 dark:border-white/10 shadow-xl shadow-slate-900/5">
            <?php echo csrf_field(); ?>
            <div class="flex-1 flex items-center gap-3 px-4">
                <svg class="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="query" placeholder="<?php echo e(__('home.hero.search_placeholder')); ?>"
                       class="flex-1 bg-transparent text-slate-900 dark:text-white placeholder-slate-400 outline-none text-base py-2">
            </div>
            <button type="submit"
                    class="px-6 py-3 rounded-xl bg-astra hover:bg-astra-600 text-white font-semibold text-sm transition-colors flex-shrink-0">
                <?php echo e(__('home.hero.search_button')); ?>

            </button>
        </form>
        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-3 justify-center">
            <?php $__currentLoopData = ['27.012.000.08.00004', 'WVWZZZCD1…', 'Volkswagen Golf']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ex): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button onclick="document.querySelector('[name=query]').value='<?php echo e($ex); ?>'"
                    class="text-xs text-slate-400 hover:text-astra transition-colors">
                ↗ <?php echo e($ex); ?>

            </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="grid grid-cols-3 gap-6 sm:gap-12 px-4" style="animation:fadeUp .8s .45s ease both;opacity:0">
        <?php $__currentLoopData = [
            ['key'=>'vehicles', 'suffix'=>'+', 'label'=>__('home.hero.stats.vehicles')],
            ['key'=>'brands',   'suffix'=>'+', 'label'=>__('home.hero.stats.brands')],
            ['key'=>'uptime',   'suffix'=>'%', 'label'=>__('home.hero.stats.uptime')],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="text-center">
            <div class="font-display font-bold text-3xl sm:text-4xl text-slate-900 dark:text-white">
                <span x-text="formatNumber(counters.<?php echo e($c['key']); ?>)">0</span><?php echo e($c['suffix']); ?>

            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?php echo e($c['label']); ?></div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 opacity-40 animate-bounce">
        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>


<div class="relative h-64 sm:h-80 overflow-hidden">
    <img src="/images/hero-route-alpes.jpg"
         alt="Route suisse avec vue sur les Alpes"
         class="w-full h-full object-cover object-center scale-105 hover:scale-100 transition-transform duration-[4000ms]"
         loading="lazy"
         onerror="this.parentElement.style.background='linear-gradient(135deg,#0B2A4A,#2563EB)';this.remove()">
    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>
    <div class="absolute bottom-6 left-8 right-8 text-white">
        <p class="text-sm font-semibold tracking-widest uppercase opacity-70"><?php echo e(__('home.photo_band.subtitle')); ?></p>
        <p class="text-xl font-bold mt-1"><?php echo e(__('home.photo_band.title')); ?></p>
    </div>
</div>


<section class="py-24 bg-white dark:bg-night px-4">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <div class="inline-block px-3 py-1 rounded-full bg-astra/10 text-astra text-xs font-bold tracking-widest uppercase mb-4"><?php echo e(__('home.features.badge')); ?></div>
            <h2 class="font-display font-bold text-3xl sm:text-4xl text-slate-900 dark:text-white"><?php echo e(__('home.features.title')); ?></h2>
            <p class="mt-3 text-slate-500 dark:text-slate-400 max-w-xl mx-auto"><?php echo e(__('home.features.subtitle')); ?></p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $atouts_data = __('home.features.items');
            $atouts = [
                [
                    'color' => 'bg-blue-50 dark:bg-blue-900/20',
                    'icon_bg' => 'bg-astra',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
                    'title' => $atouts_data[0]['title'],
                    'desc' => $atouts_data[0]['desc'],
                    'badge' => $atouts_data[0]['badge'],
                ],
                [
                    'color' => 'bg-green-50 dark:bg-green-900/20',
                    'icon_bg' => 'bg-green-600',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
                    'title' => $atouts_data[1]['title'],
                    'desc' => $atouts_data[1]['desc'],
                    'badge' => $atouts_data[1]['badge'],
                ],
                [
                    'color' => 'bg-red-50 dark:bg-red-900/20',
                    'icon_bg' => 'bg-red-600',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>',
                    'title' => $atouts_data[2]['title'],
                    'desc' => $atouts_data[2]['desc'],
                    'badge' => $atouts_data[2]['badge'],
                ],
                [
                    'color' => 'bg-purple-50 dark:bg-purple-900/20',
                    'icon_bg' => 'bg-purple-600',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                    'title' => $atouts_data[3]['title'],
                    'desc' => $atouts_data[3]['desc'],
                    'badge' => $atouts_data[3]['badge'],
                ],
                [
                    'color' => 'bg-orange-50 dark:bg-orange-900/20',
                    'icon_bg' => 'bg-orange-500',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>',
                    'title' => $atouts_data[4]['title'],
                    'desc' => $atouts_data[4]['desc'],
                    'badge' => $atouts_data[4]['badge'],
                ],
                [
                    'color' => 'bg-teal-50 dark:bg-teal-900/20',
                    'icon_bg' => 'bg-teal-600',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>',
                    'title' => $atouts_data[5]['title'],
                    'desc' => $atouts_data[5]['desc'],
                    'badge' => $atouts_data[5]['badge'],
                ],
            ];
            ?>

            <?php $__currentLoopData = $atouts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="group relative p-6 rounded-2xl <?php echo e($a['color']); ?> border border-transparent hover:border-slate-200 dark:hover:border-white/10 transition-all duration-300 hover:-translate-y-1"
                 style="animation:fadeUp .6s <?php echo e($i * 80); ?>ms ease both;opacity:0">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl <?php echo e($a['icon_bg']); ?> flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?php echo $a['icon']; ?></svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <h3 class="font-semibold text-slate-900 dark:text-white"><?php echo e($a['title']); ?></h3>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-white/60 dark:bg-white/10 text-slate-600 dark:text-slate-300 whitespace-nowrap"><?php echo e($a['badge']); ?></span>
                        </div>
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed"><?php echo e($a['desc']); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>


<section class="py-24 bg-slate-50 dark:bg-night-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

            
            <div class="relative rounded-3xl overflow-hidden shadow-2xl shadow-slate-900/20 order-last lg:order-first"
                 style="animation:fadeLeft .8s ease both;opacity:0">
                <img src="/images/carte-grise-suisse.jpg"
                     alt="Carte grise suisse — case 24 numéro de réception par type"
                     class="w-full h-80 lg:h-96 object-cover"
                     loading="lazy"
                     onerror="this.parentElement.style.background='linear-gradient(135deg,#1e3a5f,#2563EB)';this.style.display='none'">
                
                <div class="absolute bottom-6 left-6 right-6">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-astra text-white text-sm font-semibold shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <?php echo e(__('home.how_it_works.image_annotation')); ?>

                    </div>
                </div>
            </div>

            
            <div style="animation:fadeRight .8s .1s ease both;opacity:0">
                <div class="inline-block px-3 py-1 rounded-full bg-astra/10 text-astra text-xs font-bold tracking-widest uppercase mb-4"><?php echo e(__('home.how_it_works.badge')); ?></div>
                <h2 class="font-display font-bold text-3xl sm:text-4xl text-slate-900 dark:text-white mb-6"><?php echo e(__('home.how_it_works.title')); ?></h2>
                <div class="space-y-5">
                    <?php $__currentLoopData = [
                        ['n'=>'1', 'title'=>__('home.how_it_works.steps.0.title'), 'desc'=>__('home.how_it_works.steps.0.desc')],
                        ['n'=>'2', 'title'=>__('home.how_it_works.steps.1.title'), 'desc'=>__('home.how_it_works.steps.1.desc')],
                        ['n'=>'3', 'title'=>__('home.how_it_works.steps.2.title'), 'desc'=>__('home.how_it_works.steps.2.desc')],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex gap-4">
                        <div class="w-9 h-9 rounded-full bg-astra text-white font-bold text-sm flex items-center justify-center flex-shrink-0 mt-0.5"><?php echo e($step['n']); ?></div>
                        <div>
                            <div class="font-semibold text-slate-900 dark:text-white mb-0.5"><?php echo e($step['title']); ?></div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed"><?php echo e($step['desc']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="py-24 bg-night dark:bg-night-900 text-white overflow-hidden relative">
    
    <div class="absolute top-0 right-0 w-96 h-96 bg-astra/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-64 h-64 bg-spark/8 rounded-full blur-[80px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-astra/20 text-spark text-xs font-bold tracking-widest uppercase mb-6">
                    <span class="w-2 h-2 rounded-full bg-spark animate-pulse"></span>
                    <?php echo e(__('home.api.badge')); ?>

                </div>
                <h2 class="font-display font-bold text-3xl sm:text-4xl text-white mb-4"><?php echo e(__('home.api.title')); ?></h2>
                <p class="text-slate-400 mb-8 leading-relaxed"><?php echo e(__('home.api.desc')); ?></p>
                <div class="space-y-3">
                    <?php $__currentLoopData = [
                        ['method'=>'GET', 'path'=>'/api/v1/vehicle/tg/{numero_tg}',         'desc'=>__('home.api.endpoints.0.desc')],
                        ['method'=>'GET', 'path'=>'/api/v1/vehicle/tyres/{numero_tg}',       'desc'=>__('home.api.endpoints.1.desc')],
                        ['method'=>'GET', 'path'=>'/api/v1/vehicle/homologation/{numero_tg}','desc'=>__('home.api.endpoints.2.desc')],
                        ['method'=>'GET', 'path'=>'/api/v1/vehicle/tax/{numero_tg}?canton=VD','desc'=>__('home.api.endpoints.3.desc')],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-white/5 hover:bg-white/8 transition-colors">
                        <span class="font-mono text-xs font-bold px-2 py-1 rounded bg-astra/20 text-spark flex-shrink-0 mt-0.5"><?php echo e($ep['method']); ?></span>
                        <div>
                            <code class="text-xs text-slate-300 font-mono"><?php echo e($ep['path']); ?></code>
                            <p class="text-xs text-slate-500 mt-0.5"><?php echo e($ep['desc']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="mt-8 flex gap-3">
                    <a href="<?php echo e(route('faq', ['locale' => app()->getLocale()])); ?>"
                       class="px-5 py-2.5 rounded-xl bg-astra hover:bg-astra-600 text-white font-semibold text-sm transition-colors">
                        <?php echo e(__('home.api.docs_btn')); ?>

                    </a>
                    <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('account.affiliate.index', ['locale' => app()->getLocale()])); ?>"
                       class="px-5 py-2.5 rounded-xl border border-white/20 hover:border-white/40 text-white font-semibold text-sm transition-colors">
                        <?php echo e(__('home.api.keys_btn')); ?>

                    </a>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="relative" x-data="apiDemo()">
                <div class="rounded-2xl overflow-hidden border border-white/10 shadow-2xl">
                    
                    <div class="flex items-center gap-2 px-4 py-3 bg-white/5 border-b border-white/5">
                        <span class="w-3 h-3 rounded-full bg-red-500/60"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-500/60"></span>
                        <span class="w-3 h-3 rounded-full bg-green-500/60"></span>
                        <span class="ml-3 text-xs text-slate-400 font-mono">API · reception-par-type.ch</span>
                    </div>
                    
                    <div class="p-6 bg-slate-950 font-mono text-xs leading-6 min-h-[220px]">
                        <span class="text-slate-500">$ </span><span class="text-green-400">curl</span><span class="text-slate-300"> -H </span><span class="text-yellow-300">"Authorization: Bearer rpt_..."</span><br>
                        <span class="text-slate-500 ml-4">https://api.reception-par-type.ch/api/v1/</span><br>
                        <span class="text-slate-500 ml-8">vehicle/tg/27.012.000.08.00004</span><br><br>
                        <span class="text-slate-400">{</span><br>
                        <span class="ml-4 text-spark">"tg_code"</span><span class="text-slate-400">: </span><span class="text-green-300">"27.012.000.08.00004"</span><span class="text-slate-400">,</span><br>
                        <span class="ml-4 text-spark">"marque"</span><span class="text-slate-400">: </span><span class="text-green-300">"VOLKSWAGEN"</span><span class="text-slate-400">,</span><br>
                        <span class="ml-4 text-spark">"puissance_kw"</span><span class="text-slate-400">: </span><span class="text-blue-300">110</span><span class="text-slate-400">,</span><br>
                        <span class="ml-4 text-spark">"puissance_ch"</span><span class="text-slate-400">: </span><span class="text-blue-300">150</span><span class="text-slate-400">,</span><br>
                        <span class="ml-4 text-spark">"co2"</span><span class="text-slate-400">: </span><span class="text-blue-300">118</span><span class="text-slate-400">,</span><br>
                        <span class="ml-4 text-spark">"pneu_1"</span><span class="text-slate-400">: </span><span class="text-green-300">"205/55 R16 91H"</span><br>
                        <span class="text-slate-400">}</span><br>
                        <span class="text-slate-600 text-[10px] mt-2 block">// Quota restant : 4 847 / 5 000 · Réponse : 42ms</span>
                    </div>
                </div>
                
                <div class="absolute -top-3 -right-3 w-20 h-20 bg-astra/10 rounded-full blur-xl animate-pulse"></div>
            </div>

        </div>
    </div>
</section>


<div class="relative h-52 sm:h-64 overflow-hidden">
    <img src="/images/garage-diagnostic.jpg"
         alt="Garage professionnel suisse avec logiciel de diagnostic CH Typengenehmigung"
         class="w-full h-full object-cover object-center"
         loading="lazy"
         onerror="this.parentElement.style.background='linear-gradient(135deg,#1e293b,#0f172a)';this.remove()">
    <div class="absolute inset-0 bg-gradient-to-r from-night/70 via-night/30 to-transparent"></div>
    <div class="absolute top-1/2 -translate-y-1/2 left-8 sm:left-16">
        <p class="text-xs font-bold tracking-widest uppercase text-spark mb-1"><?php echo e(__('home.professionals.badge')); ?></p>
        <p class="text-white text-xl sm:text-2xl font-bold max-w-xs leading-snug"><?php echo e(__('home.professionals.title')); ?></p>
    </div>
</div>


<div class="relative h-48 sm:h-64 overflow-hidden">
    <img src="/images/controle-technique-suisse.jpg"
         alt="Contrôle technique officiel en Suisse"
         class="w-full h-full object-cover object-top"
         loading="lazy"
         onerror="this.parentElement.style.background='linear-gradient(135deg,#143a63,#0B2A4A)';this.remove()">
    <div class="absolute inset-0 bg-night/60"></div>
    <div class="absolute inset-0 flex items-center justify-center">
        <p class="text-white text-xl sm:text-2xl font-display font-bold text-center px-4 drop-shadow-lg">
            <?php echo e(__('home.official.title')); ?>

        </p>
    </div>
</div>


<section class="py-20 bg-white dark:bg-night text-center px-4">
    <div class="max-w-2xl mx-auto">
        <div class="w-14 h-14 rounded-xl mx-auto mb-6 flex items-center justify-center shadow-lg" style="background:#C8102E">
            <svg viewBox="0 0 40 40" class="w-9 h-9" aria-hidden="true">
                <rect x="15" y="6" width="10" height="28" rx="1.5" fill="white"/>
                <rect x="6" y="15" width="28" height="10" rx="1.5" fill="white"/>
            </svg>
        </div>
        <h2 class="font-display font-bold text-3xl sm:text-4xl text-slate-900 dark:text-white mb-4"><?php echo e(__('home.cta.title')); ?></h2>
        <p class="text-slate-500 dark:text-slate-400 mb-8"><?php echo e(__('home.cta.desc')); ?></p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="<?php echo e(route('search', ['locale' => app()->getLocale()])); ?>"
               class="px-8 py-3.5 rounded-2xl bg-astra hover:bg-astra-600 text-white font-semibold text-sm transition-colors shadow-lg shadow-astra/20">
                <?php echo e(__('home.cta.search_btn')); ?>

            </a>
            <a href="<?php echo e(route('faq', ['locale' => app()->getLocale()])); ?>"
               class="px-8 py-3.5 rounded-2xl border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 font-semibold text-sm hover:border-astra/30 transition-colors">
                <?php echo e(__('home.cta.pricing_btn')); ?>

            </a>
        </div>
    </div>
</section>

<?php $__env->startPush('scripts'); ?>
<script>
function heroStats() {
    return {
        counters: { vehicles:0, brands:0, uptime:0 },
        targets:  { vehicles:500000, brands:1200, uptime:99.9 },
        startCounters() {
            const observer = new IntersectionObserver(e => {
                if (e[0].isIntersecting) { observer.disconnect(); this.animateAll(); }
            }, { threshold:0.3 });
            observer.observe(this.$el);
        },
        animateAll() {
            const delays = { vehicles:0, brands:150, uptime:300 };
            Object.keys(this.targets).forEach(k => setTimeout(() => this.animateCounter(k), delays[k]));
        },
        animateCounter(key) {
            const target=this.targets[key], duration=key==='vehicles'?2000:1400, start=performance.now();
            const isDecimal=target%1!==0;
            const step=now=>{
                const p=Math.min((now-start)/duration,1), e=1-Math.pow(1-p,3);
                this.counters[key]=isDecimal?parseFloat((target*e).toFixed(1)):Math.floor(target*e);
                if(p<1) requestAnimationFrame(step); else this.counters[key]=target;
            };
            requestAnimationFrame(step);
        },
        formatNumber(n){ return typeof n==='string'?n:new Intl.NumberFormat('fr-CH').format(n); }
    };
}
function apiDemo(){ return {}; }
</script>
<style>
@keyframes fadeDown{ from{opacity:0;transform:translateY(-20px)} to{opacity:1;transform:translateY(0)} }
@keyframes fadeUp  { from{opacity:0;transform:translateY(20px)}  to{opacity:1;transform:translateY(0)} }
@keyframes fadeLeft{ from{opacity:0;transform:translateX(-20px)} to{opacity:1;transform:translateX(0)} }
@keyframes fadeRight{from{opacity:0;transform:translateX(20px)}  to{opacity:1;transform:translateX(0)} }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/pages/home.blade.php ENDPATH**/ ?>