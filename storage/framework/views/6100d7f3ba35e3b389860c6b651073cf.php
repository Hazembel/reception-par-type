<?php $__env->startSection('page_title', $user->name); ?>
<?php $__env->startSection('title_icon', '👤'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <a href="<?php echo e(route('admin.users.index')); ?>">Clients</a><span class="sep">›</span><span><?php echo e($user->name); ?></span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<div class="ps-panel">
    <div class="ps-panel-header">
        <span class="ps-icon">👤</span> <?php echo e($user->name); ?>

        <div class="ps-panel-tools">
            <span class="ps-badge ps-badge-<?php echo e($user->subscription_level >= 6 ? 'info' : ($user->subscription_level >= 3 ? 'success' : 'muted')); ?>">
                Niveau <?php echo e($user->subscription_level); ?> — <?php echo e($plans->where('level', $user->subscription_level)->first()->name_fr ?? '?'); ?>

            </span>
        </div>
    </div>
    <div class="ps-panel-body">
        <p style="color:var(--ps-text-muted);margin-bottom:16px"><code><?php echo e($user->email); ?></code></p>
        <div class="ps-grid-4">
            <?php $__currentLoopData = [
                ['Jetons', number_format($user->web_tokens_balance, 0, '.', '\'')],
                ['Consultations / mois', number_format($user->web_monthly_counter, 0, '.', '\'')],
                ['Abonnement jusqu\'au', $user->subscribed_until?->format('d.m.Y') ?? '—'],
                ['Déblocages ce mois', $unlockedCount],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="padding:14px;background:#fafbfc;border:1px solid var(--ps-border);border-radius:4px;text-align:center">
                <div style="font-size:22px;font-weight:700" class="num"><?php echo e($stat[1]); ?></div>
                <div class="ps-help" style="margin-top:3px"><?php echo e($stat[0]); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>


<div class="ps-panel">
    <div class="ps-panel-header"><span class="ps-icon">⭐</span> Modifier le niveau d'abonnement</div>
    <div class="ps-panel-body">
        <form method="POST" action="<?php echo e(route('admin.users.grant', $user)); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="set_level">
            <div class="ps-grid-2">
                <div class="ps-form-group">
                    <label class="ps-label">Nouveau niveau</label>
                    <select name="subscription_level" class="ps-select">
                        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($plan->level < 8): ?>
                                <option value="<?php echo e($plan->level); ?>" <?php echo e($user->subscription_level === $plan->level ? 'selected' : ''); ?>>
                                    Niveau <?php echo e($plan->level); ?> — <?php echo e($plan->name_fr); ?>

                                </option>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Note <span class="ps-help">(journal d'audit)</span></label>
                    <input type="text" name="note" class="ps-input" placeholder="Ex : geste commercial">
                </div>
            </div>
            <button type="submit" class="ps-btn ps-btn-primary">Appliquer le niveau</button>
        </form>
    </div>
</div>


<div class="ps-panel">
    <div class="ps-panel-header"><span class="ps-icon">🎟️</span> Ajouter des jetons <span class="ps-help" style="font-weight:400">· solde : <?php echo e(number_format($user->web_tokens_balance, 0, '.', '\'')); ?></span></div>
    <div class="ps-panel-body">
        <form method="POST" action="<?php echo e(route('admin.users.grant', $user)); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="add_tokens">
            <div class="ps-grid-2">
                <div class="ps-form-group">
                    <label class="ps-label">Nombre de jetons</label>
                    <input type="number" name="tokens_to_add" value="10" min="1" max="1000" class="ps-input num">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Note</label>
                    <input type="text" name="note" class="ps-input" placeholder="Raison">
                </div>
            </div>
            <button type="submit" class="ps-btn ps-btn-success">Créditer les jetons</button>
        </form>
    </div>
</div>


<div class="ps-panel">
    <div class="ps-panel-header"><span class="ps-icon">📅</span> Prolonger l'abonnement <span class="ps-help" style="font-weight:400">· expire : <?php echo e($user->subscribed_until?->format('d.m.Y') ?? 'aucun'); ?></span></div>
    <div class="ps-panel-body">
        <form method="POST" action="<?php echo e(route('admin.users.grant', $user)); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="extend_subscription">
            <div class="ps-grid-2">
                <div class="ps-form-group">
                    <label class="ps-label">Durée</label>
                    <select name="extend_days" class="ps-select">
                        <option value="7">7 jours</option>
                        <option value="30" selected>30 jours (1 mois)</option>
                        <option value="90">90 jours (3 mois)</option>
                        <option value="365">365 jours (1 an)</option>
                    </select>
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Note</label>
                    <input type="text" name="note" class="ps-input" placeholder="Raison">
                </div>
            </div>
            <button type="submit" class="ps-btn ps-btn-primary">Prolonger</button>
        </form>
    </div>
</div>


<div class="ps-panel">
    <div class="ps-panel-header"><span class="ps-icon">♻️</span> Réinitialiser le compteur mensuel</div>
    <div class="ps-panel-body" style="display:flex;justify-content:space-between;align-items:center;gap:16px">
        <span class="ps-help">Compteur actuel : <?php echo e(number_format($user->web_monthly_counter, 0, '.', '\'')); ?> consultations</span>
        <form method="POST" action="<?php echo e(route('admin.users.grant', $user)); ?>" onsubmit="return confirm('Réinitialiser le compteur de <?php echo e($user->name); ?> ?')">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="reset_counter">
            <input type="hidden" name="note" value="Reset manuel admin">
            <button type="submit" class="ps-btn">Réinitialiser</button>
        </form>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.prestashop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/admin/users/show.blade.php ENDPATH**/ ?>