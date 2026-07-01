
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title'  => '',
    'icon'   => '',
    'public' => true,
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
    'title'  => '',
    'icon'   => '',
    'public' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div class="
    group relative
    bg-white dark:bg-marine/30
    border border-slate-200 dark:border-white/5
    rounded-2xl overflow-hidden
    hover:border-slate-300 dark:hover:border-white/10
    hover:shadow-lg hover:shadow-black/5 dark:hover:shadow-black/20
    transition-all duration-300
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
        <?php if(!$public): ?>
            <span class="ml-auto">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </span>
        <?php endif; ?>
    </div>

    
    <div class="divide-y divide-slate-100 dark:divide-white/[0.04]">
        <?php echo e($rows); ?>

    </div>
</div>
<?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/components/vehicle-card.blade.php ENDPATH**/ ?>