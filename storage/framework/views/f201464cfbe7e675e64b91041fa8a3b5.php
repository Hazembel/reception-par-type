<?php $__env->startSection('content'); ?>


<?php
    $steps   = __('help.guide.steps');
    $locale  = app()->getLocale();
    $step1   = __('help.guide.step1');
    $step2   = __('help.guide.step2');
    $step3   = __('help.guide.step3');
?>

<div
    class="pt-20 pb-24 max-w-6xl mx-auto px-4 sm:px-6"
    x-data="guideNav()"
    x-init="init()"
>
    
    <div class="text-center mb-16 max-w-2xl mx-auto">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium
                    bg-astra/10 text-astra border border-astra/20 mb-4">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <?php echo e(__('help.guide.nav_title')); ?>

        </div>
        <h1 class="font-display text-display text-slate-900 dark:text-white mb-3">
            <?php echo e(__('help.guide.page_title')); ?>

        </h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed mb-2">
            <?php echo e(__('help.guide.page_description')); ?>

        </p>
        <span class="text-xs text-slate-400 flex items-center justify-center gap-1.5">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <?php echo e(__('help.guide.reading_time')); ?>

        </span>
    </div>

    
    <div class="flex gap-8 lg:gap-12 items-start">

        
        
        
        <aside class="hidden lg:block w-56 flex-shrink-0">
            <nav
                class="sticky top-20 space-y-1"
                aria-label="<?php echo e(__('help.guide.nav_title')); ?>"
            >
                <p class="text-2xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3 mb-3">
                    <?php echo e(__('help.guide.nav_title')); ?>

                </p>

                <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a
                        href="#<?php echo e($step['id']); ?>"
                        x-on:click.prevent="scrollTo('<?php echo e($step['id']); ?>')"
                        x-bind:class="{
                            'bg-astra/8 dark:bg-astra/12 text-astra border-l-2 border-astra pl-2.5': activeSection === '<?php echo e($step['id']); ?>',
                            'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white pl-3': activeSection !== '<?php echo e($step['id']); ?>'
                        }"
                        class="
                            flex items-start gap-3 py-2.5 pr-3 rounded-r-lg
                            text-xs transition-all duration-150
                            border-l-2 border-transparent
                        "
                    >
                        <span
                            class="flex-shrink-0 font-mono font-bold text-2xs mt-0.5 transition-colors duration-150"
                            x-bind:class="{
                                'text-astra': activeSection === '<?php echo e($step['id']); ?>',
                                'text-slate-300 dark:text-slate-600': activeSection !== '<?php echo e($step['id']); ?>'
                            }"
                        ><?php echo e($step['number']); ?></span>
                        <span class="leading-snug font-medium"><?php echo e($step['title']); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                
                <div class="pt-4 mt-4 border-t border-slate-200 dark:border-white/5 pl-3 space-y-2">
                    <a href="<?php echo e(route('faq', ['locale' => $locale])); ?>"
                       class="flex items-center gap-1.5 text-xs text-slate-400 hover:text-astra transition-colors duration-150">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        FAQ
                    </a>
                    <a href="<?php echo e(route('search', ['locale' => $locale])); ?>"
                       class="flex items-center gap-1.5 text-xs text-slate-400 hover:text-astra transition-colors duration-150">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                        </svg>
                        Recherche TG
                    </a>
                </div>
            </nav>
        </aside>

        
        
        
        <div class="flex-1 min-w-0 space-y-16">

            
            
            
            <section id="<?php echo e($steps[0]['id']); ?>" class="scroll-mt-24">

                
                <div class="flex items-start gap-4 mb-6">
                    <span class="flex-shrink-0 font-mono text-3xl font-bold text-slate-200 dark:text-white/10 leading-none select-none">
                        <?php echo e($steps[0]['number']); ?>

                    </span>
                    <div>
                        <h2 class="font-display text-xl text-slate-900 dark:text-white leading-tight mb-1">
                            <?php echo e($steps[0]['title']); ?>

                        </h2>
                        <p class="text-xs text-astra font-medium"><?php echo e($steps[0]['subtitle']); ?></p>
                    </div>
                </div>

                <div class="prose-custom space-y-6">
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        <?php echo $step1['body']; ?>

                    </p>

                    
                    <div class="
                        rounded-2xl overflow-hidden
                        border border-slate-200 dark:border-white/10
                        bg-white dark:bg-marine/30
                        p-5
                    ">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-4">
                            Permis de circulation suisse — Identification de la case 24
                        </p>
                        <svg viewBox="0 0 560 200" class="w-full max-w-lg mx-auto" aria-label="Illustration du permis de circulation suisse avec la case 24 mise en évidence" role="img">
                            <title>Case 24 du permis de circulation suisse</title>

                            
                            <rect x="10" y="10" width="540" height="180" rx="8" fill="none" stroke="#CBD5E1" stroke-width="1.5"/>

                            
                            <?php $boxes = [
                                ['x'=>20,'y'=>20,'w'=>80,'h'=>35,'num'=>'A','label'=>'Immatriculation'],
                                ['x'=>110,'y'=>20,'w'=>150,'h'=>35,'num'=>'B','label'=>'Date de 1ère immatriculation'],
                                ['x'=>270,'y'=>20,'w'=>130,'h'=>35,'num'=>'C.1','label'=>'Détenteur'],
                                ['x'=>410,'y'=>20,'w'=>135,'h'=>35,'num'=>'C.2','label'=>'Commune'],
                                ['x'=>20,'y'=>65,'w'=>120,'h'=>35,'num'=>'D.1','label'=>'Marque'],
                                ['x'=>150,'y'=>65,'w'=>150,'h'=>35,'num'=>'D.2','label'=>'Type / Variante'],
                                ['x'=>310,'y'=>65,'w'=>80,'h'=>35,'num'=>'E','label'=>'N° châssis'],
                                ['x'=>400,'y'=>65,'w'=>60,'h'=>35,'num'=>'F.1','label'=>'PMA (kg)'],
                                ['x'=>470,'y'=>65,'w'=>75,'h'=>35,'num'=>'F.2','label'=>'PRA (kg)'],
                                ['x'=>20,'y'=>110,'w'=>65,'h'=>35,'num'=>'G','label'=>'Poids vide'],
                                ['x'=>95,'y'=>110,'w'=>65,'h'=>35,'num'=>'J','label'=>'Catégorie'],
                                ['x'=>170,'y'=>110,'w'=>65,'h'=>35,'num'=>'K','label'=>'N° réception'],
                            ]; ?>

                            <?php $__currentLoopData = $boxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $box): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <rect x="<?php echo e($box['x']); ?>" y="<?php echo e($box['y']); ?>" width="<?php echo e($box['w']); ?>" height="<?php echo e($box['h']); ?>" rx="3" fill="#F8FAFC" stroke="#E2E8F0" stroke-width="1"/>
                                <text x="<?php echo e($box['x'] + 4); ?>" y="<?php echo e($box['y'] + 12); ?>" font-family="monospace" font-size="7" fill="#94A3B8"><?php echo e($box['num']); ?></text>
                                <text x="<?php echo e($box['x'] + 4); ?>" y="<?php echo e($box['y'] + 26); ?>" font-family="sans-serif" font-size="7" fill="#64748B"><?php echo e($box['label']); ?></text>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            
                            <rect x="245" y="110" width="120" height="55" rx="5"
                                  fill="#EFF6FF" stroke="#2563EB" stroke-width="2"/>
                            <text x="249" y="122" font-family="monospace" font-size="8" fill="#2563EB" font-weight="bold">24</text>
                            <text x="249" y="133" font-family="sans-serif" font-size="7" fill="#1E40AF">N° réception par type</text>
                            
                            <text x="249" y="148" font-family="monospace" font-size="9" fill="#1E3A8A" font-weight="bold">27.012.000.08.00004</text>

                            
                            <path d="M 380 135 L 370 135" stroke="#2563EB" stroke-width="1.5" fill="none" marker-end="url(#arrow)"/>
                            <text x="383" y="139" font-family="sans-serif" font-size="9" fill="#2563EB" font-weight="600">← Case 24</text>

                            
                            <rect x="375" y="110" width="80" height="35" rx="3" fill="#F8FAFC" stroke="#E2E8F0" stroke-width="1"/>
                            <text x="379" y="122" font-family="monospace" font-size="7" fill="#94A3B8">P.1</text>
                            <text x="379" y="133" font-family="sans-serif" font-size="7" fill="#64748B">Cylindrée</text>

                            <rect x="465" y="110" width="80" height="35" rx="3" fill="#F8FAFC" stroke="#E2E8F0" stroke-width="1"/>
                            <text x="469" y="122" font-family="monospace" font-size="7" fill="#94A3B8">P.2</text>
                            <text x="469" y="133" font-family="sans-serif" font-size="7" fill="#64748B">Puissance (kW)</text>

                            
                            <rect x="20" y="155" width="525" height="25" rx="3" fill="#F1F5F9" stroke="#E2E8F0" stroke-width="1"/>
                            <text x="30" y="172" font-family="sans-serif" font-size="8" fill="#94A3B8">CANTON DE GENÈVE — Permis de circulation</text>
                        </svg>
                    </div>

                    
                    <div class="rounded-2xl bg-slate-900 dark:bg-black/40 border border-white/5 p-5">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">
                            <?php echo e($step1['format_title']); ?>

                        </p>
                        <div class="font-mono text-lg text-white mb-5 text-center tracking-widest">
                            <?php $parts = explode('.', $step1['format_example']); ?>
                            <?php $__currentLoopData = $parts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pi => $part): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="<?php echo e($pi === 0 ? 'text-spark' : ($pi === 3 ? 'text-astra' : 'text-white')); ?>"><?php echo e($part); ?></span><?php if(!$loop->last): ?><span class="text-slate-600">.</span><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                            <?php $__currentLoopData = $step1['format_parts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pi => $part): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="text-center">
                                    <div class="font-mono text-sm font-bold <?php echo e($pi === 0 ? 'text-spark' : ($pi === 3 ? 'text-astra' : 'text-slate-300')); ?> mb-1">
                                        <?php echo e($part['label']); ?>

                                    </div>
                                    <div class="text-2xs text-slate-500 leading-tight"><?php echo e($part['desc']); ?></div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    
                    <?php if (isset($component)) { $__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.guide-callout','data' => ['type' => 'tip']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guide-callout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'tip']); ?><?php echo e($step1['tip']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5)): ?>
<?php $attributes = $__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5; ?>
<?php unset($__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5)): ?>
<?php $component = $__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5; ?>
<?php unset($__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.guide-callout','data' => ['type' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guide-callout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'info']); ?><?php echo e($step1['note']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5)): ?>
<?php $attributes = $__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5; ?>
<?php unset($__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5)): ?>
<?php $component = $__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5; ?>
<?php unset($__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5); ?>
<?php endif; ?>
                </div>
            </section>

            
            
            
            <section id="<?php echo e($steps[1]['id']); ?>" class="scroll-mt-24">

                <div class="flex items-start gap-4 mb-6">
                    <span class="flex-shrink-0 font-mono text-3xl font-bold text-slate-200 dark:text-white/10 leading-none select-none">
                        <?php echo e($steps[1]['number']); ?>

                    </span>
                    <div>
                        <h2 class="font-display text-xl text-slate-900 dark:text-white leading-tight mb-1">
                            <?php echo e($steps[1]['title']); ?>

                        </h2>
                        <p class="text-xs text-astra font-medium"><?php echo e($steps[1]['subtitle']); ?></p>
                    </div>
                </div>

                <div class="space-y-6">
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        <?php echo $step2['body']; ?>

                    </p>

                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <div class="rounded-2xl border border-emerald-200 dark:border-emerald-500/20 bg-emerald-50 dark:bg-emerald-500/5 p-5">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                                    <?php echo e($step2['free_title']); ?>

                                </span>
                            </div>
                            <ul class="space-y-2">
                                <?php $__currentLoopData = $step2['free_items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="flex items-start gap-2 text-xs text-emerald-800 dark:text-emerald-300">
                                        <svg class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <?php echo e($item); ?>

                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>

                        
                        <div class="rounded-2xl border border-astra/20 dark:border-astra/20 bg-astra/5 dark:bg-astra/8 p-5">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="w-5 h-5 rounded-full bg-astra flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </span>
                                <span class="text-xs font-semibold text-astra-700 dark:text-astra-400 text-astra">
                                    <?php echo e($step2['premium_title']); ?>

                                </span>
                            </div>
                            <ul class="space-y-2">
                                <?php $__currentLoopData = $step2['premium_items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="flex items-start gap-2 text-xs text-slate-600 dark:text-slate-300">
                                        <svg class="w-3.5 h-3.5 text-astra flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <?php echo e($item); ?>

                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>

                    
                    <div class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-marine/30 p-5">
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 mb-3">
                            Termes techniques — passez la souris sur les icônes <kbd class="text-2xs font-sans bg-slate-100 dark:bg-white/10 px-1.5 py-0.5 rounded">?</kbd> pour les définitions
                        </p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-2">
                            <?php $__currentLoopData = [
                                'entraxe', 'alesage', 'deport_et',
                                'poids_remorquable', 'co2', 'code_emissions'
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $term): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-center gap-1 text-xs text-slate-600 dark:text-slate-300 py-1.5 border-b border-slate-100 dark:border-white/5">
                                    <?php echo e(Str::title(str_replace('_', ' ', $term))); ?>

                                    <?php if (isset($component)) { $__componentOriginal7875b222dc4d64f17fd6d2e345da8799 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7875b222dc4d64f17fd6d2e345da8799 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tooltip','data' => ['text' => __('help.tooltip.' . $term),'position' => 'top']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tooltip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['text' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('help.tooltip.' . $term)),'position' => 'top']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7875b222dc4d64f17fd6d2e345da8799)): ?>
<?php $attributes = $__attributesOriginal7875b222dc4d64f17fd6d2e345da8799; ?>
<?php unset($__attributesOriginal7875b222dc4d64f17fd6d2e345da8799); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7875b222dc4d64f17fd6d2e345da8799)): ?>
<?php $component = $__componentOriginal7875b222dc4d64f17fd6d2e345da8799; ?>
<?php unset($__componentOriginal7875b222dc4d64f17fd6d2e345da8799); ?>
<?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <?php if (isset($component)) { $__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.guide-callout','data' => ['type' => 'tip']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guide-callout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'tip']); ?><?php echo e($step2['tip']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5)): ?>
<?php $attributes = $__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5; ?>
<?php unset($__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5)): ?>
<?php $component = $__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5; ?>
<?php unset($__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5); ?>
<?php endif; ?>
                </div>
            </section>

            
            
            
            <section id="<?php echo e($steps[2]['id']); ?>" class="scroll-mt-24">

                <div class="flex items-start gap-4 mb-6">
                    <span class="flex-shrink-0 font-mono text-3xl font-bold text-slate-200 dark:text-white/10 leading-none select-none">
                        <?php echo e($steps[2]['number']); ?>

                    </span>
                    <div>
                        <h2 class="font-display text-xl text-slate-900 dark:text-white leading-tight mb-1">
                            <?php echo e($steps[2]['title']); ?>

                        </h2>
                        <p class="text-xs text-astra font-medium"><?php echo e($steps[2]['subtitle']); ?></p>
                    </div>
                </div>

                <div class="space-y-6">
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        <?php echo $step3['body']; ?>

                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <div class="rounded-2xl border border-slate-200 dark:border-white/10 bg-white dark:bg-marine/30 p-5">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-500/15 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e($step3['pdf_title']); ?></h3>
                            </div>
                            <ol class="space-y-2">
                                <?php $__currentLoopData = $step3['pdf_steps']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pi => $pdfStep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="flex items-start gap-2.5 text-xs text-slate-600 dark:text-slate-300">
                                        <span class="flex-shrink-0 w-4 h-4 rounded-full bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400 flex items-center justify-center text-2xs font-bold">
                                            <?php echo e($pi + 1); ?>

                                        </span>
                                        <?php echo e($pdfStep); ?>

                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ol>
                        </div>

                        
                        <div class="rounded-2xl border border-slate-200 dark:border-white/10 bg-white dark:bg-marine/30 p-5">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-astra/10 dark:bg-astra/15 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-astra" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e($step3['api_title']); ?></h3>
                            </div>

                            
                            <div class="mb-3 rounded-lg bg-slate-900 dark:bg-black/50 px-3 py-2">
                                <code class="text-xs font-mono text-spark"><?php echo e($step3['api_endpoint']); ?></code>
                            </div>

                            
                            <pre class="text-2xs font-mono text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-white/[0.03] rounded-lg p-3 overflow-x-auto leading-relaxed"><code><?php echo e($step3['api_example']); ?></code></pre>

                            <?php if (isset($component)) { $__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.guide-callout','data' => ['type' => 'tip','class' => 'mt-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guide-callout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'tip','class' => 'mt-3']); ?><?php echo e($step3['api_tip']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5)): ?>
<?php $attributes = $__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5; ?>
<?php unset($__attributesOriginal2dd6fc9e51d1f9243fef6b450fdff1d5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5)): ?>
<?php $component = $__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5; ?>
<?php unset($__componentOriginal2dd6fc9e51d1f9243fef6b450fdff1d5); ?>
<?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="text-center py-6">
                        <a href="<?php echo e(route('register', ['locale' => $locale])); ?>"
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-astra hover:bg-astra-600 text-white font-semibold text-sm transition-all duration-200 shadow-sm shadow-astra/20 hover:shadow-astra/40">
                            Commencer gratuitement — 10 fiches/mois
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
/**
 * guideNav() — Navigation sidebar avec IntersectionObserver
 * Surligne la section active dans la sidebar sans event scroll coûteux.
 */
function guideNav() {
    return {
        activeSection: '<?php echo e($steps[0]['id']); ?>',

        init() {
            const sections = document.querySelectorAll('section[id]');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.activeSection = entry.target.id;
                    }
                });
            }, {
                rootMargin: '-20% 0px -60% 0px', // Déclenchement au tiers supérieur du viewport
                threshold: 0,
            });

            sections.forEach(section => observer.observe(section));
        },

        /**
         * Scroll fluide vers une section avec offset navbar.
         */
        scrollTo(sectionId) {
            const el = document.getElementById(sectionId);
            if (!el) return;

            const navbarHeight = 80; // var(--navbar-height) = 64px + marge
            const top = el.getBoundingClientRect().top + window.scrollY - navbarHeight;

            window.scrollTo({ top, behavior: 'smooth' });
        }
    };
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/pages/guide.blade.php ENDPATH**/ ?>