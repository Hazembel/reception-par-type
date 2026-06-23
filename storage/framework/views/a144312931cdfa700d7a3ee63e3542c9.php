<?php $__env->startSection('content'); ?>
<div class="pt-24 pb-16 max-w-5xl mx-auto px-4 sm:px-6">

    <h1 class="font-display text-2xl text-slate-900 dark:text-white mb-6">
        <?php echo e(__('app.search.title')); ?>

    </h1>

    
    <form method="POST" action="<?php echo e(route('search.results', ['locale' => app()->getLocale()])); ?>" class="mb-8">
        <?php echo csrf_field(); ?>
        <div class="flex gap-3">
            <input type="text" name="query" value="<?php echo e($query ?? ''); ?>"
                   placeholder="<?php echo e(__('app.search.placeholder')); ?>"
                   class="flex-1 px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-marine/40 text-slate-900 dark:text-white focus:border-astra focus:outline-none"
                   autofocus>
            <button type="submit" class="px-6 py-3 rounded-xl bg-astra text-white font-semibold hover:bg-astra-600 transition-colors">
                <?php echo e(__('app.search.button')); ?>

            </button>
        </div>
    </form>

    
    <?php if($results !== null): ?>
        <?php if($results->count() > 0): ?>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                <?php echo e($results->total()); ?> <?php echo e(__('app.search.results_count')); ?>

            </p>
            <div class="space-y-3">
                <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('vehicle.show', ['locale' => app()->getLocale(), 'vehicle' => $vehicle->slug])); ?>"
                   class="block bg-white dark:bg-marine/30 border border-slate-200 dark:border-white/5 rounded-2xl p-5 hover:border-astra/40 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-semibold text-slate-900 dark:text-white"><?php echo e($vehicle->marque); ?> <?php echo e($vehicle->modele); ?></span>
                            <?php if($vehicle->variante): ?><span class="text-slate-400 ml-1"><?php echo e($vehicle->variante); ?></span><?php endif; ?>
                            <div class="text-xs text-slate-400 font-mono mt-1"><?php echo e($vehicle->numero_tg); ?></div>
                        </div>
                        <span class="text-astra text-sm">→</span>
                    </div>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="mt-6"><?php echo e($results->withQueryString()->links()); ?></div>
        <?php else: ?>
            <div class="text-center py-16 text-slate-400">
                <p class="text-lg mb-2"><?php echo e(__('app.search.no_results')); ?></p>
                <p class="text-sm"><?php echo e(__('app.search.no_results_hint')); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/pages/search.blade.php ENDPATH**/ ?>