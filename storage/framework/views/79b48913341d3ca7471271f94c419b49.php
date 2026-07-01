<?php $__env->startSection('content'); ?>
<div class="pt-20 pb-16 max-w-5xl mx-auto px-4 sm:px-6">

    
    <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6" aria-label="Fil d'Ariane">
        <a href="<?php echo e(route('home', ['locale' => app()->getLocale()])); ?>"
           class="hover:text-astra transition-colors">Accueil</a>
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="<?php echo e(route('search', ['locale' => app()->getLocale()])); ?>"
           class="hover:text-astra transition-colors">Recherche</a>
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600 dark:text-slate-300"><?php echo e($vehicle->marque); ?> <?php echo e($vehicle->modele); ?></span>
    </nav>

    
    <div class="mb-8">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="font-display text-display text-slate-900 dark:text-white mb-1">
                    <?php echo e($vehicle->marque); ?>

                    <span class="italic text-astra"><?php echo e($vehicle->modele); ?></span>
                </h1>
                <?php if($vehicle->variante): ?>
                    <p class="text-slate-500 dark:text-slate-400 text-sm"><?php echo e($vehicle->variante); ?></p>
                <?php endif; ?>
            </div>

            
            <div class="flex-shrink-0">
                <div class="
                    px-4 py-2 rounded-xl
                    bg-white dark:bg-marine/40
                    border border-slate-200 dark:border-white/10
                    text-center
                ">
                    <div class="text-2xs text-slate-400 uppercase tracking-wider mb-0.5">
                        N° Réception par type
                    </div>
                    <div class="font-mono text-sm font-semibold text-slate-900 dark:text-white tracking-widest">
                        <?php echo e($vehicle->numero_tg); ?>

                    </div>
                </div>
            </div>
        </div>

        
        <div class="flex items-center gap-3 mt-4">
            <?php if($vehicle->is_active): ?>
                <span class="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Homologation active
                </span>
            <?php else: ?>
                <span class="flex items-center gap-1.5 text-xs text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    Homologation archivée
                </span>
            <?php endif; ?>
            <span class="text-slate-200 dark:text-white/10">|</span>
            <span class="text-xs text-slate-400">
                Mise à jour <?php echo e($vehicle->imported_at?->diffForHumans() ?? 'inconnue'); ?>

            </span>
        </div>
    </div>

    
    
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        
        <?php if (isset($component)) { $__componentOriginale56e826e722be54fb6fce6ea617765f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale56e826e722be54fb6fce6ea617765f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.vehicle-card','data' => ['title' => 'Motorisation','icon' => '⚡','public' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('vehicle-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Motorisation','icon' => '⚡','public' => true]); ?>
             <?php $__env->slot('rows', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal171fd21bf0ba4db75b316a7b1349104f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-row','data' => ['label' => ''.e(__('app.vehicle.energie')).'','value' => $vehicle->energie ? App\Models\VehicleTranslation::translate('energie', $vehicle->energie, app()->getLocale()) : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('app.vehicle.energie')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->energie ? App\Models\VehicleTranslation::translate('energie', $vehicle->energie, app()->getLocale()) : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $attributes = $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $component = $__componentOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal171fd21bf0ba4db75b316a7b1349104f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-row','data' => ['label' => ''.e(__('app.vehicle.puissance_kw')).'','value' => $vehicle->puissance_kw ? $vehicle->puissance_kw . ' kW (' . $vehicle->puissance_cv . ' CV)' : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('app.vehicle.puissance_kw')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->puissance_kw ? $vehicle->puissance_kw . ' kW (' . $vehicle->puissance_cv . ' CV)' : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $attributes = $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $component = $__componentOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal171fd21bf0ba4db75b316a7b1349104f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-row','data' => ['label' => ''.e(__('app.vehicle.cylindree')).'','value' => $vehicle->cylindree ? number_format($vehicle->cylindree, 0, '.', chr(39)) . ' cm³' : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('app.vehicle.cylindree')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->cylindree ? number_format($vehicle->cylindree, 0, '.', chr(39)) . ' cm³' : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $attributes = $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $component = $__componentOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal171fd21bf0ba4db75b316a7b1349104f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-row','data' => ['label' => ''.e(__('app.vehicle.boite_vitesse')).'','value' => $vehicle->boite_vitesse ? App\Models\VehicleTranslation::translate('boite_vitesse', $vehicle->boite_vitesse, app()->getLocale()) : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('app.vehicle.boite_vitesse')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->boite_vitesse ? App\Models\VehicleTranslation::translate('boite_vitesse', $vehicle->boite_vitesse, app()->getLocale()) : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $attributes = $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $component = $__componentOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale56e826e722be54fb6fce6ea617765f3)): ?>
<?php $attributes = $__attributesOriginale56e826e722be54fb6fce6ea617765f3; ?>
<?php unset($__attributesOriginale56e826e722be54fb6fce6ea617765f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale56e826e722be54fb6fce6ea617765f3)): ?>
<?php $component = $__componentOriginale56e826e722be54fb6fce6ea617765f3; ?>
<?php unset($__componentOriginale56e826e722be54fb6fce6ea617765f3); ?>
<?php endif; ?>

        
        <?php if($canViewAdvanced): ?>
            <?php if (isset($component)) { $__componentOriginale56e826e722be54fb6fce6ea617765f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale56e826e722be54fb6fce6ea617765f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.vehicle-card','data' => ['title' => 'Masses & Charges','icon' => '⚖️','public' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('vehicle-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Masses & Charges','icon' => '⚖️','public' => true]); ?>
                 <?php $__env->slot('rows', null, []); ?> 
                    <?php if (isset($component)) { $__componentOriginal171fd21bf0ba4db75b316a7b1349104f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-row','data' => ['label' => ''.e(__('app.vehicle.poids_vide')).'','value' => $vehicle->poids_vide ? number_format($vehicle->poids_vide, 0, '.', chr(39)) . ' kg' : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('app.vehicle.poids_vide')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->poids_vide ? number_format($vehicle->poids_vide, 0, '.', chr(39)) . ' kg' : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $attributes = $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $component = $__componentOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal171fd21bf0ba4db75b316a7b1349104f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-row','data' => ['label' => ''.e(__('app.vehicle.poids_total')).'','value' => $vehicle->poids_total ? number_format($vehicle->poids_total, 0, '.', chr(39)) . ' kg' : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('app.vehicle.poids_total')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->poids_total ? number_format($vehicle->poids_total, 0, '.', chr(39)) . ' kg' : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $attributes = $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $component = $__componentOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal171fd21bf0ba4db75b316a7b1349104f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-row','data' => ['label' => ''.e(__('app.vehicle.poids_remorquable')).'','value' => $vehicle->poids_remorquable ? number_format($vehicle->poids_remorquable, 0, '.', chr(39)) . ' kg' : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('app.vehicle.poids_remorquable')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->poids_remorquable ? number_format($vehicle->poids_remorquable, 0, '.', chr(39)) . ' kg' : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $attributes = $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $component = $__componentOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
                 <?php $__env->endSlot(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale56e826e722be54fb6fce6ea617765f3)): ?>
<?php $attributes = $__attributesOriginale56e826e722be54fb6fce6ea617765f3; ?>
<?php unset($__attributesOriginale56e826e722be54fb6fce6ea617765f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale56e826e722be54fb6fce6ea617765f3)): ?>
<?php $component = $__componentOriginale56e826e722be54fb6fce6ea617765f3; ?>
<?php unset($__componentOriginale56e826e722be54fb6fce6ea617765f3); ?>
<?php endif; ?>
        <?php else: ?>
            
            <?php if (isset($component)) { $__componentOriginalab8711229888162abbcb12040d21830c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab8711229888162abbcb12040d21830c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.locked-card','data' => ['title' => 'Masses & Charges','icon' => '⚖️','price' => 2,'vehicleId' => $vehicle->id]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('locked-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Masses & Charges','icon' => '⚖️','price' => 2,'vehicle-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->id)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab8711229888162abbcb12040d21830c)): ?>
<?php $attributes = $__attributesOriginalab8711229888162abbcb12040d21830c; ?>
<?php unset($__attributesOriginalab8711229888162abbcb12040d21830c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab8711229888162abbcb12040d21830c)): ?>
<?php $component = $__componentOriginalab8711229888162abbcb12040d21830c; ?>
<?php unset($__componentOriginalab8711229888162abbcb12040d21830c); ?>
<?php endif; ?>
        <?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginale56e826e722be54fb6fce6ea617765f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale56e826e722be54fb6fce6ea617765f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.vehicle-card','data' => ['title' => 'Émissions','icon' => '🌿','public' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('vehicle-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Émissions','icon' => '🌿','public' => true]); ?>
             <?php $__env->slot('rows', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal171fd21bf0ba4db75b316a7b1349104f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-row','data' => ['label' => ''.e(__('app.vehicle.co2')).'','value' => $vehicle->co2 !== null
                                 ? ($vehicle->co2 === 0 ? '0 g/km (ZEV)' : $vehicle->co2 . ' g/km')
                                 : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('app.vehicle.co2')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->co2 !== null
                                 ? ($vehicle->co2 === 0 ? '0 g/km (ZEV)' : $vehicle->co2 . ' g/km')
                                 : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $attributes = $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $component = $__componentOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal171fd21bf0ba4db75b316a7b1349104f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-row','data' => ['label' => ''.e(__('app.vehicle.code_emissions')).'','value' => $vehicle->code_emissions]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('app.vehicle.code_emissions')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->code_emissions)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $attributes = $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $component = $__componentOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
             <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale56e826e722be54fb6fce6ea617765f3)): ?>
<?php $attributes = $__attributesOriginale56e826e722be54fb6fce6ea617765f3; ?>
<?php unset($__attributesOriginale56e826e722be54fb6fce6ea617765f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale56e826e722be54fb6fce6ea617765f3)): ?>
<?php $component = $__componentOriginale56e826e722be54fb6fce6ea617765f3; ?>
<?php unset($__componentOriginale56e826e722be54fb6fce6ea617765f3); ?>
<?php endif; ?>

        
        <?php if($canViewAdvanced): ?>
            <?php if (isset($component)) { $__componentOriginale56e826e722be54fb6fce6ea617765f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale56e826e722be54fb6fce6ea617765f3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.vehicle-card','data' => ['title' => 'Jantes & Pneumatiques','icon' => '🔩','public' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('vehicle-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Jantes & Pneumatiques','icon' => '🔩','public' => true]); ?>
                 <?php $__env->slot('rows', null, []); ?> 
                    <?php if (isset($component)) { $__componentOriginal171fd21bf0ba4db75b316a7b1349104f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-row','data' => ['label' => ''.e(__('app.vehicle.nb_trous')).'','value' => $vehicle->nb_trous ? $vehicle->nb_trous . ' trous' : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('app.vehicle.nb_trous')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->nb_trous ? $vehicle->nb_trous . ' trous' : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $attributes = $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $component = $__componentOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal171fd21bf0ba4db75b316a7b1349104f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-row','data' => ['label' => ''.e(__('app.vehicle.entraxe')).'','value' => $vehicle->entraxe ? $vehicle->entraxe . ' mm' : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('app.vehicle.entraxe')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->entraxe ? $vehicle->entraxe . ' mm' : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $attributes = $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $component = $__componentOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal171fd21bf0ba4db75b316a7b1349104f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-row','data' => ['label' => ''.e(__('app.vehicle.alesage')).'','value' => $vehicle->alesage ? $vehicle->alesage . ' mm' : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('app.vehicle.alesage')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->alesage ? $vehicle->alesage . ' mm' : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $attributes = $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $component = $__componentOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal171fd21bf0ba4db75b316a7b1349104f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-row','data' => ['label' => ''.e(__('app.vehicle.deport_et')).'','value' => $vehicle->deport_et !== null ? 'ET ' . ($vehicle->deport_et >= 0 ? '+' : '') . $vehicle->deport_et : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('app.vehicle.deport_et')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->deport_et !== null ? 'ET ' . ($vehicle->deport_et >= 0 ? '+' : '') . $vehicle->deport_et : null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $attributes = $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $component = $__componentOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal171fd21bf0ba4db75b316a7b1349104f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.data-row','data' => ['label' => ''.e(__('app.vehicle.pneus_origine')).'','value' => $vehicle->pneus_origine,'mono' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('data-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => ''.e(__('app.vehicle.pneus_origine')).'','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->pneus_origine),'mono' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $attributes = $__attributesOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__attributesOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f)): ?>
<?php $component = $__componentOriginal171fd21bf0ba4db75b316a7b1349104f; ?>
<?php unset($__componentOriginal171fd21bf0ba4db75b316a7b1349104f); ?>
<?php endif; ?>
                 <?php $__env->endSlot(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale56e826e722be54fb6fce6ea617765f3)): ?>
<?php $attributes = $__attributesOriginale56e826e722be54fb6fce6ea617765f3; ?>
<?php unset($__attributesOriginale56e826e722be54fb6fce6ea617765f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale56e826e722be54fb6fce6ea617765f3)): ?>
<?php $component = $__componentOriginale56e826e722be54fb6fce6ea617765f3; ?>
<?php unset($__componentOriginale56e826e722be54fb6fce6ea617765f3); ?>
<?php endif; ?>
        <?php else: ?>
            <?php if (isset($component)) { $__componentOriginalab8711229888162abbcb12040d21830c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab8711229888162abbcb12040d21830c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.locked-card','data' => ['title' => 'Jantes & Pneumatiques','icon' => '🔩','price' => 2,'vehicleId' => $vehicle->id]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('locked-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Jantes & Pneumatiques','icon' => '🔩','price' => 2,'vehicle-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($vehicle->id)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab8711229888162abbcb12040d21830c)): ?>
<?php $attributes = $__attributesOriginalab8711229888162abbcb12040d21830c; ?>
<?php unset($__attributesOriginalab8711229888162abbcb12040d21830c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab8711229888162abbcb12040d21830c)): ?>
<?php $component = $__componentOriginalab8711229888162abbcb12040d21830c; ?>
<?php unset($__componentOriginalab8711229888162abbcb12040d21830c); ?>
<?php endif; ?>
        <?php endif; ?>
    </div>

    
    <div class="mt-8 flex items-center justify-end gap-3">
        <span class="text-xs text-slate-400">Partager :</span>
        <button
            onclick="navigator.clipboard.writeText(window.location.href).then(() => alert('URL copiée !'))"
            class="
                flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs
                bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400
                hover:bg-astra/10 hover:text-astra transition-colors duration-150
            "
        >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Copier le lien
        </button>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/pages/vehicle/show.blade.php ENDPATH**/ ?>