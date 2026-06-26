
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'text'     => '',
    'position' => 'top',      // top | bottom | left | right
    'icon'     => 'info',     // Lucide icon name — 'info' ou 'circle-help'
    'size'     => 'sm',       // sm | md
    'id'       => null,
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
    'text'     => '',
    'position' => 'top',      // top | bottom | left | right
    'icon'     => 'info',     // Lucide icon name — 'info' ou 'circle-help'
    'size'     => 'sm',       // sm | md
    'id'       => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $tooltipId = $id ?? 'tooltip-' . Str::random(6);

    // Classes de positionnement de la bulle
    $positionClasses = match($position) {
        'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
        'left'   => 'right-full top-1/2 -translate-y-1/2 mr-2',
        'right'  => 'left-full top-1/2 -translate-y-1/2 ml-2',
        default  => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
    };

    // Classes de la flèche (triangle CSS)
    $arrowClasses = match($position) {
        'bottom' => 'top-0 left-1/2 -translate-x-1/2 -translate-y-full border-b-slate-800 dark:border-b-slate-700 border-x-transparent border-t-transparent border-b-4 border-x-4 border-t-0',
        'left'   => 'right-0 top-1/2 -translate-y-1/2 translate-x-full border-l-slate-800 dark:border-l-slate-700 border-y-transparent border-r-transparent border-l-4 border-y-4 border-r-0',
        'right'  => 'left-0 top-1/2 -translate-y-1/2 -translate-x-full border-r-slate-800 dark:border-r-slate-700 border-y-transparent border-l-transparent border-r-4 border-y-4 border-l-0',
        default  => 'bottom-0 left-1/2 -translate-x-1/2 translate-y-full border-t-slate-800 dark:border-t-slate-700 border-x-transparent border-b-transparent border-t-4 border-x-4 border-b-0',
    };

    $iconSize = $size === 'md' ? 'w-4 h-4' : 'w-3.5 h-3.5';
?>

<span
    id="<?php echo e($tooltipId); ?>-wrap"
    class="relative inline-flex items-center"
    x-data="{
        open: false,
        isMobile: false,
        init() {
            this.isMobile = window.matchMedia('(hover: none)').matches;
        },
        show() { if (!this.isMobile) this.open = true; },
        hide() { if (!this.isMobile) this.open = false; },
        toggle() { if (this.isMobile) this.open = !this.open; },
    }"
    x-on:mouseenter="show()"
    x-on:mouseleave="hide()"
    x-on:click.outside="open = false"
>
    
    <button
        type="button"
        x-on:click="toggle()"
        x-bind:aria-expanded="open"
        aria-describedby="<?php echo e($tooltipId); ?>"
        class="
            inline-flex items-center justify-center
            rounded-full
            text-slate-400 dark:text-slate-500
            hover:text-astra dark:hover:text-spark
            focus-visible:text-astra dark:focus-visible:text-spark
            transition-colors duration-150
            cursor-help
            ml-1 -mb-0.5
        "
        aria-label="Aide contextuelle"
    >
        <?php if($icon === 'circle-help'): ?>
            <svg class="<?php echo e($iconSize); ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><circle cx="12" cy="17" r=".5" fill="currentColor"/>
            </svg>
        <?php else: ?>
            <svg class="<?php echo e($iconSize); ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 16v-4m0-4h.01"/>
            </svg>
        <?php endif; ?>
    </button>

    
    <div
        id="<?php echo e($tooltipId); ?>"
        role="tooltip"
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="
            absolute z-50 w-72 pointer-events-none
            <?php echo e($positionClasses); ?>

        "
        style="display: none;"
    >
        
        <div class="
            relative
            bg-slate-800 dark:bg-slate-700
            text-white
            text-xs leading-relaxed
            rounded-xl px-3.5 py-2.5
            shadow-xl shadow-black/20
            pointer-events-auto
        ">
            
            <span
                class="absolute w-0 h-0 <?php echo e($arrowClasses); ?>"
                aria-hidden="true"
            ></span>

            
            <p class="[&_strong]:font-semibold [&_strong]:text-spark [&_code]:font-mono [&_code]:bg-white/10 [&_code]:px-1 [&_code]:rounded [&_em]:italic [&_em]:text-slate-300">
                <?php echo $text; ?>

            </p>
        </div>
    </div>
</span>
<?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/components/tooltip.blade.php ENDPATH**/ ?>