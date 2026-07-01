<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex items-center justify-center py-16 px-4
            bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100
            dark:from-night dark:via-marine/20 dark:to-night">
    <div class="w-full max-w-md mx-auto">
        <div class="overflow-hidden rounded-[2rem] bg-white/95 dark:bg-marine-900/80
                    border border-slate-200/70 dark:border-white/10
                    shadow-[0_30px_80px_-40px_rgba(15,23,42,.35)]
                    ring-1 ring-slate-200/70 dark:ring-white/10
                    p-8 sm:p-10 text-center">

            <div class="flex items-center justify-center w-14 h-14 rounded-full bg-blue-50 dark:bg-astra/10 mx-auto mb-5">
                <svg class="w-7 h-7 text-blue-600 dark:text-astra" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V8.844a2.25 2.25 0 011.183-1.98l7.5-4.04a2.25 2.25 0 012.134 0l7.5 4.04a2.25 2.25 0 011.183 1.98V19.5z"/>
                </svg>
            </div>

            <h1 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Vérifiez votre adresse e-mail</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                Un lien de vérification a été envoyé à <strong class="text-slate-700 dark:text-slate-300"><?php echo e(auth()->user()->email); ?></strong>.
                Cliquez sur le lien dans cet e-mail pour activer votre compte.
            </p>

            <?php if(session('resent')): ?>
                <div class="mb-5 px-3.5 py-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20
                            border border-emerald-200 dark:border-emerald-800/40
                            text-sm text-emerald-700 dark:text-emerald-400">
                    Un nouveau lien de vérification a été envoyé à votre adresse e-mail.
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(url('/email/verification-notification')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="w-full py-3 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700
                           text-white text-sm font-semibold tracking-wide
                           transition-all duration-150 shadow-md hover:shadow-lg mb-4">
                    Renvoyer l'e-mail de vérification
                </button>
            </form>

            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:underline">
                    Se déconnecter
                </button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/auth/verify-email.blade.php ENDPATH**/ ?>