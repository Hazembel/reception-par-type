<?php $__env->startSection('content'); ?>
<section class="relative py-24 overflow-hidden" x-data="{ billingCycle: 'monthly' }">
    
    <div class="absolute inset-0 -z-20">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-night dark:via-night-800 dark:to-night-900"></div>
        <div class="absolute top-[-10%] right-[-10%] w-[600px] h-[600px] rounded-full bg-astra/5 blur-[120px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-spark/4 blur-[100px]"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-astra/20 bg-astra/5 text-xs font-semibold text-astra uppercase tracking-wider mb-4">
                Tarification transparente
            </span>
            <h1 class="font-display font-bold text-4xl sm:text-5xl text-slate-900 dark:text-white tracking-tight leading-none mb-4">
                Des forfaits adaptés à vos besoins
            </h1>
            <p class="text-lg text-slate-500 dark:text-slate-400">
                Consultez les données de motorisation gratuitement, ou optez pour un accès illimité et des outils professionnels de pointe.
            </p>

            
            <div class="inline-flex items-center gap-1.5 p-1.5 rounded-2xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/5 mt-10">
                <button
                    x-on:click="billingCycle = 'monthly'"
                    x-bind:class="{ 'bg-white dark:bg-marine text-slate-900 dark:text-white shadow-sm': billingCycle === 'monthly', 'text-slate-500 hover:text-slate-900 dark:hover:text-white': billingCycle !== 'monthly' }"
                    class="px-5 py-2 rounded-xl text-sm font-semibold transition-all duration-200"
                >
                    Mensuel
                </button>
                <button
                    x-on:click="billingCycle = 'yearly'"
                    x-bind:class="{ 'bg-white dark:bg-marine text-slate-900 dark:text-white shadow-sm': billingCycle === 'yearly', 'text-slate-500 hover:text-slate-900 dark:hover:text-white': billingCycle !== 'yearly' }"
                    class="px-5 py-2 rounded-xl text-sm font-semibold transition-all duration-200 relative"
                >
                    Annuel
                    <span class="absolute -top-3.5 -right-3 px-2 py-0.5 rounded-full bg-green-500 text-[10px] font-bold text-white shadow-sm">
                        -15%
                    </span>
                </button>
            </div>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $localeName = $plan->{'name_' . app()->getLocale()} ?? $plan->name_fr;
                ?>
                <div
                    class="relative group rounded-3xl bg-white dark:bg-marine-800/40 border border-slate-200 dark:border-white/5 p-8 flex flex-col justify-between shadow-lg hover:shadow-xl hover:border-astra/20 transition-all duration-300 hover:-translate-y-1"
                >
                    <?php if($plan->level === 4): ?>
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                            <span class="text-xs bg-astra text-white px-3 py-1 rounded-full font-bold shadow-md tracking-wider uppercase">
                                Populaire
                            </span>
                        </div>
                    <?php endif; ?>

                    <div>
                        
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-display text-xl font-bold text-slate-900 dark:text-white">
                                <?php echo e($localeName); ?>

                            </h3>
                            <span class="text-2xs font-bold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-400">
                                Niveau <?php echo e($plan->level); ?>

                            </span>
                        </div>

                        
                        <div class="mb-6">
                            
                            <div x-show="billingCycle === 'monthly'" class="flex items-baseline gap-1.5">
                                <?php if($plan->price_monthly_chf === 0): ?>
                                    <span class="font-display text-4xl font-extrabold text-slate-900 dark:text-white">Gratuit</span>
                                <?php else: ?>
                                    <span class="font-display text-4xl font-extrabold text-slate-900 dark:text-white">
                                        <?php echo e(number_format($plan->price_monthly, 0, '.', '\'')); ?>

                                    </span>
                                    <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">CHF/mois</span>
                                <?php endif; ?>
                            </div>

                            
                            <div x-show="billingCycle === 'yearly'" class="flex items-baseline gap-1.5" style="display: none;">
                                <?php if($plan->price_yearly_chf === 0 || $plan->price_yearly_chf === null): ?>
                                    <span class="font-display text-4xl font-extrabold text-slate-900 dark:text-white">Gratuit</span>
                                <?php else: ?>
                                    <span class="font-display text-4xl font-extrabold text-slate-900 dark:text-white">
                                        <?php echo e(number_format($plan->price_yearly / 12, 0, '.', '\'')); ?>

                                    </span>
                                    <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">CHF/mois</span>
                                    <span class="text-xs text-slate-400 block mt-1">Facturé <?php echo e(number_format($plan->price_yearly, 0, '.', '\'')); ?> CHF/an</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-8 leading-relaxed">
                            <?php echo e($plan->description_fr); ?>

                        </p>

                        
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-300">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>
                                    <strong><?php echo e($plan->web_monthly_limit === -1 ? 'Illimité' : $plan->web_monthly_limit); ?></strong> consultations web
                                </span>
                            </li>

                            <?php if($plan->can_use_api): ?>
                                <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-300">
                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>
                                        Accès API : <strong><?php echo e(number_format($plan->api_monthly_limit, 0, '.', '\'')); ?></strong> requêtes/mois
                                    </span>
                                </li>
                            <?php else: ?>
                                <li class="flex items-start gap-3 text-sm text-slate-400 dark:text-slate-600">
                                    <svg class="w-5 h-5 text-slate-300 dark:text-slate-700 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span class="line-through">Pas d'accès API</span>
                                </li>
                            <?php endif; ?>

                            <?php $__currentLoopData = [
                                'can_export_pdf'   => 'Fiches PDF téléchargeables',
                                'can_export_csv'   => 'Export CSV en masse',
                                'can_compare'      => 'Comparateur de véhicules',
                                'can_use_tax_calc' => 'Simulateur fiscal cantonal',
                            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($plan->$key): ?>
                                    <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-300">
                                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span><?php echo e($label); ?></span>
                                    </li>
                                <?php else: ?>
                                    <li class="flex items-start gap-3 text-sm text-slate-400 dark:text-slate-600">
                                        <svg class="w-5 h-5 text-slate-300 dark:text-slate-700 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <span class="line-through"><?php echo e($label); ?></span>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>

                    
                    <?php if(auth()->guard()->check()): ?>
                        <?php if(auth()->user()->subscription_level === $plan->level): ?>
                            <button disabled class="w-full text-center py-3 rounded-2xl text-sm font-semibold bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-600 cursor-not-allowed">
                                Votre formule actuelle
                            </button>
                        <?php elseif($plan->price_monthly_chf === 0): ?>
                            <button disabled class="w-full text-center py-3 rounded-2xl text-sm font-semibold bg-slate-100 dark:bg-white/5 text-slate-400 dark:text-slate-600 cursor-not-allowed">
                                Disponible gratuitement
                            </button>
                        <?php else: ?>
                            <form action="<?php echo e(route('checkout.create-order', ['locale' => app()->getLocale()])); ?>" method="POST" class="w-full">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="type" value="plan">
                                <input type="hidden" name="value" value="<?php echo e($plan->level); ?>">
                                <button type="submit" class="w-full text-center py-3 rounded-2xl text-sm font-semibold bg-astra hover:bg-astra-600 text-white shadow-md shadow-astra/20 transition-all duration-200">
                                    Mettre à niveau
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if($plan->price_monthly_chf === 0): ?>
                            <a href="<?php echo e(route('register', ['locale' => app()->getLocale()])); ?>" class="block w-full text-center py-3 rounded-2xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-white/5 dark:hover:bg-white/10 text-slate-700 dark:text-slate-300 transition-colors">
                                Commencer gratuitement
                            </a>
                        <?php else: ?>
                            <a href="<?php echo e(route('login', ['locale' => app()->getLocale()])); ?>" class="block w-full text-center py-3 rounded-2xl text-sm font-semibold bg-astra hover:bg-astra-600 text-white shadow-md shadow-astra/20 transition-all duration-200">
                                Choisir ce plan
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/pages/pricing.blade.php ENDPATH**/ ?>