
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label' => '',
    'value' => null,
    'mono'  => false,
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
    'label' => '',
    'value' => null,
    'mono'  => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<?php $hasValue = $value !== null && $value !== ''; ?>

<div class="flex items-center justify-between px-5 py-3 gap-4">
    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 shrink-0">
        <?php echo $label; ?>

    </dt>
    <dd class="text-sm text-right <?php echo e($hasValue ? 'text-slate-900 dark:text-white font-medium' : 'text-slate-300 dark:text-slate-600'); ?> <?php echo e($mono ? 'font-mono text-xs tracking-wide' : ''); ?>">
        <?php echo e($hasValue ? $value : '—'); ?>

    </dd>
</div>
<?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/components/data-row.blade.php ENDPATH**/ ?>