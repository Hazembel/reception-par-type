<?php $__env->startSection('page_title', $level === '8' ? 'Administrateurs' : 'Clients'); ?>
<?php $__env->startSection('title_icon', $level === '8' ? '🔑' : '👥'); ?>
<?php $__env->startSection('breadcrumb'); ?><span><?php echo e($level === '8' ? 'Administrateurs' : 'Clients'); ?></span><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<div class="ps-kpi-row">
    <div class="ps-kpi">
        <span class="ps-kpi-label">Total inscrits</span>
        <span class="ps-kpi-value"><?php echo e(number_format($stats['total'], 0, '.', '\'')); ?></span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Vérifiés</span>
        <span class="ps-kpi-value green"><?php echo e(number_format($stats['verified'], 0, '.', '\'')); ?></span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Abonnements actifs</span>
        <span class="ps-kpi-value cyan"><?php echo e(number_format($stats['active'], 0, '.', '\'')); ?></span>
    </div>
</div>

<div class="ps-panel">
    <div class="ps-panel-header"><span class="ps-icon">🔍</span> Filtrer</div>
    <div class="ps-panel-body">
        <form method="GET" action="<?php echo e(route('admin.users.index')); ?>" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
            <div style="flex:1;min-width:200px">
                <label class="ps-label">Recherche <span class="ps-help">nom ou e-mail</span></label>
                <input type="text" name="q" value="<?php echo e($search); ?>" class="ps-input" placeholder="Jean Dupont, jean@...">
            </div>
            <div style="width:220px">
                <label class="ps-label">Niveau</label>
                <select name="level" class="ps-select">
                    <option value="">Tous les niveaux</option>
                    <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($plan->level); ?>" <?php echo e((string)$level === (string)$plan->level ? 'selected' : ''); ?>>
                            Niveau <?php echo e($plan->level); ?> — <?php echo e($plan->name_fr); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <button type="submit" class="ps-btn ps-btn-primary">Rechercher</button>
            <?php if($search || $level): ?>
                <a href="<?php echo e(route('admin.users.index')); ?>" class="ps-btn">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="ps-panel">
    <div class="ps-panel-header">
        <span class="ps-icon">📋</span> <?php echo e($level === '8' ? 'Liste des administrateurs' : 'Liste des clients'); ?>

        <div class="ps-panel-tools"><span class="ps-badge ps-badge-muted"><?php echo e($users->total()); ?> résultat(s)</span></div>
    </div>
    <div class="ps-panel-body flush">
        <table class="ps-table">
            <thead>
                <tr>
                    <?php $__currentLoopData = [['name','Nom'],['email','E-mail'],['subscription_level','Niveau'],['web_tokens_balance','Jetons'],['created_at','Inscription']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $th): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => $th[0], 'dir' => $sort === $th[0] && $dir === 'asc' ? 'desc' : 'asc'])); ?>" style="color:inherit">
                            <?php echo e($th[1]); ?>

                            <?php if($sort === $th[0]): ?><?php echo e($dir === 'asc' ? ' ▲' : ' ▼'); ?><?php endif; ?>
                        </a>
                    </th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <th>Statut</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><strong><?php echo e($user->name); ?></strong></td>
                    <td style="color:var(--ps-text-muted)"><code><?php echo e($user->email); ?></code></td>
                    <td>
                        <span class="ps-badge ps-badge-<?php echo e($user->subscription_level >= 6 ? 'info' : ($user->subscription_level >= 3 ? 'success' : 'muted')); ?>">
                            L<?php echo e($user->subscription_level); ?> · <?php echo e($plans[$user->subscription_level]->name_fr ?? '?'); ?>

                        </span>
                    </td>
                    <td class="num"><?php echo e(number_format($user->web_tokens_balance, 0, '.', '\'')); ?></td>
                    <td style="color:var(--ps-text-muted)"><?php echo e($user->created_at->format('d.m.Y')); ?></td>
                    <td>
                        <?php if($user->email_verified_at): ?>
                            <span class="ps-badge ps-badge-success">Vérifié</span>
                        <?php else: ?>
                            <span class="ps-badge ps-badge-warning">Non vérifié</span>
                        <?php endif; ?>
                        <?php if($user->deleted_at): ?><span class="ps-badge ps-badge-danger">Supprimé</span><?php endif; ?>
                    </td>
                    <td style="text-align:right">
                        <a href="<?php echo e(route('admin.users.show', $user)); ?>" class="ps-btn ps-btn-sm">Modifier</a>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--ps-text-muted)">Aucun client trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($users->hasPages()): ?>
    <div style="padding:14px 18px;border-top:1px solid var(--ps-border)"><?php echo e($users->withQueryString()->links()); ?></div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.prestashop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/admin/users/index.blade.php ENDPATH**/ ?>