
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title'     => '',
    'icon'      => '',
    'price'     => 2,
    'vehicleId' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title'     => '',
    'icon'      => '',
    'price'     => 2,
    'vehicleId' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div
    class="relative rounded-2xl overflow-hidden"
    x-data="{ hovered: false }"
    x-on:mouseenter="hovered = true"
    x-on:mouseleave="hovered = false"
>
    
    
    <div class="
        bg-white dark:bg-marine/30
        border border-slate-200 dark:border-white/5
        rounded-2xl overflow-hidden
    ">
        
        <div class="
            flex items-center gap-2.5 px-5 py-4
            border-b border-slate-100 dark:border-white/5
            bg-slate-50/50 dark:bg-white/[0.02]
        ">
            <span class="text-base leading-none" aria-hidden="true"><?php echo e($icon); ?></span>
            <h2 class="font-semibold text-sm text-slate-700 dark:text-slate-200 tracking-wide">
                <?php echo e($title); ?>

            </h2>
            <span class="ml-auto">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </span>
        </div>

        
        <div class="divide-y divide-slate-100 dark:divide-white/[0.04] select-none" aria-hidden="true">
            <?php $__currentLoopData = [
                ['w-16', 'w-20'],
                ['w-20', 'w-16'],
                ['w-14', 'w-24'],
                ['w-18', 'w-14'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between px-5 py-3 gap-4">
                    <div class="h-3 <?php echo e($row[0]); ?> rounded-full bg-slate-200 dark:bg-white/10"></div>
                    <div class="h-3 <?php echo e($row[1]); ?> rounded-full bg-slate-200 dark:bg-white/10"></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    
    <div class="
        absolute inset-0 rounded-2xl
        backdrop-blur-md
        bg-white/60 dark:bg-night/70
        flex flex-col items-center justify-center
        transition-all duration-500
    ">

        
        <div
            class="
                w-12 h-12 rounded-2xl mb-3 flex items-center justify-center
                bg-white dark:bg-marine/80
                border border-slate-200 dark:border-white/10
                shadow-lg shadow-black/5
                transition-transform duration-300
            "
            x-bind:class="{ '-translate-y-1 shadow-astra/20 shadow-xl': hovered }"
        >
            <svg class="w-5 h-5 text-slate-400 dark:text-slate-300 transition-colors duration-300"
                 x-bind:class="{ 'text-astra dark:text-spark': hovered }"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                
                <path
                    stroke-linecap="round" stroke-linejoin="round"
                    x-bind:d="hovered
                        ? 'M8 11V7a4 4 0 018 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z'
                        : 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'"
                />
            </svg>
        </div>

        
        <p class="text-xs text-slate-500 dark:text-slate-400 text-center px-4 mb-4 leading-relaxed">
            Ces données sont disponibles<br/>
            avec un abonnement ou à l'unité
        </p>

        
        <a
            href="<?php echo e(url('/' . app()->getLocale() . '/pricing')); ?><?php echo e($vehicleId ? '?unlock=' . $vehicleId : ''); ?>"
            class="
                relative group/btn
                inline-flex items-center gap-2
                px-5 py-2.5 rounded-xl
                text-sm font-semibold
                text-white
                bg-gradient-to-r from-astra to-spark
                transition-all duration-300
                hover:scale-105 active:scale-95
            "
            x-bind:class="{
                'animate-unlock-glow shadow-lg shadow-spark/30': hovered,
                'shadow-sm shadow-astra/20': !hovered
            }"
        >
            
            <span class="
                absolute inset-0 rounded-xl
                bg-white/0 group-hover/btn:bg-white/10
                transition-colors duration-200
            "></span>

            <svg class="w-3.5 h-3.5 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 018 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
            </svg>
            <span class="relative z-10">
                Débloquer pour <?php echo e(number_format($price, 0, '.', '\'')); ?> CHF
            </span>
        </a>

        
        <a href="<?php echo e(url('/' . app()->getLocale() . '/pricing')); ?>"
           class="
               mt-2 text-xs text-slate-400 hover:text-astra
               underline underline-offset-2 decoration-dotted
               transition-colors duration-150
           ">
            Ou voir les abonnements →
        </a>
    </div>
</div>
<?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/components/locked-card.blade.php ENDPATH**/ ?>