<?php $__env->startSection('page_title', 'Tableau de bord'); ?>
<?php $__env->startSection('title_icon', '📊'); ?>

<?php $__env->startSection('content'); ?>


<div class="ps-kpi-row">
    <div class="ps-kpi">
        <span class="ps-kpi-label">CA mensuel estimé</span>
        <span class="ps-kpi-value"><?php echo e(number_format($business['monthly_revenue_chf'], 0, '.', '\'')); ?><span style="font-size:14px;color:var(--ps-text-muted)"> CHF</span></span>
        <span class="ps-kpi-sub"><?php echo e($business['active_subscribers']); ?> abonnés actifs</span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Nouveaux abonnements</span>
        <span class="ps-kpi-value green">+<?php echo e($business['new_this_month']); ?></span>
        <span class="ps-kpi-sub">ce mois-ci</span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Déblocages / jetons</span>
        <span class="ps-kpi-value cyan"><?php echo e(number_format($business['token_unlocks_month'], 0, '.', '\'')); ?></span>
        <span class="ps-kpi-sub">consultations payantes</span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Appels API facturés</span>
        <span class="ps-kpi-value"><?php echo e(number_format($business['api_calls_this_month'], 0, '.', '\'')); ?></span>
        <span class="ps-kpi-sub">ce mois-ci</span>
    </div>
</div>

<div class="ps-grid-2">
    
    <div class="ps-panel">
        <div class="ps-panel-header"><span class="ps-icon">👥</span> Clients</div>
        <div class="ps-panel-body flush">
            <table class="ps-table">
                <tbody>
                    <?php $__currentLoopData = [
                        ['Inscrits au total',   number_format($users['total'], 0, '.', '\''), ''],
                        ['E-mails vérifiés',     $users['verified'] . ' (' . $users['verified_pct'] . '%)', 'success'],
                        ['E-mails non vérifiés', $users['unverified'], $users['unverified'] > 0 ? 'warning' : ''],
                        ['Nouveaux ce mois',     '+' . $users['new_this_month'], 'info'],
                        ['Actifs (30 jours)',     number_format($users['recently_active'], 0, '.', '\''), ''],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td style="color:var(--ps-text-muted)"><?php echo e($row[0]); ?></td>
                        <td style="text-align:right" class="num">
                            <?php if($row[2]): ?>
                                <span class="ps-badge ps-badge-<?php echo e($row[2]); ?>"><?php echo e($row[1]); ?></span>
                            <?php else: ?>
                                <strong><?php echo e($row[1]); ?></strong>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="ps-panel">
        <div class="ps-panel-header"><span class="ps-icon">⚙️</span> Système &amp; Imports</div>
        <div class="ps-panel-body flush">
            <table class="ps-table">
                <tbody>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Fiches ASTRA en base</td>
                        <td style="text-align:right" class="num"><strong><?php echo e(number_format($system['total_vehicles'], 0, '.', '\'')); ?></strong></td>
                    </tr>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Fiches actives</td>
                        <td style="text-align:right" class="num"><span class="ps-badge ps-badge-success"><?php echo e(number_format($system['active_vehicles'], 0, '.', '\'')); ?></span></td>
                    </tr>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Dernier import 2000</td>
                        <td style="text-align:right">
                            <?php if($system['last_import_2000']): ?>
                                <?php $s = $system['last_import_2000']['status']; ?>
                                <span class="ps-badge ps-badge-<?php echo e($s === 'completed' ? 'success' : ($s === 'running' ? 'info' : ($s === 'partial' ? 'warning' : 'danger'))); ?>"><?php echo e(strtoupper($s)); ?></span>
                                <div class="ps-help" style="margin-top:3px"><?php echo e($system['last_import_2000']['date']); ?> · +<?php echo e(number_format($system['last_import_2000']['inserted'], 0, '.', '\'')); ?></div>
                            <?php else: ?>
                                <span class="ps-badge ps-badge-muted">JAMAIS</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Dernier import 5000</td>
                        <td style="text-align:right">
                            <?php if($system['last_import_5000']): ?>
                                <?php $s = $system['last_import_5000']['status']; ?>
                                <span class="ps-badge ps-badge-<?php echo e($s === 'completed' ? 'success' : ($s === 'running' ? 'info' : 'warning')); ?>"><?php echo e(strtoupper($s)); ?></span>
                                <div class="ps-help" style="margin-top:3px"><?php echo e($system['last_import_5000']['date']); ?></div>
                            <?php else: ?>
                                <span class="ps-badge ps-badge-muted">JAMAIS</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Imports échoués (7j)</td>
                        <td style="text-align:right">
                            <span class="ps-badge ps-badge-<?php echo e($system['failed_recent'] > 0 ? 'danger' : 'success'); ?>">
                                <?php echo e($system['failed_recent'] === 0 ? '0' : $system['failed_recent']); ?>

                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="ps-panel">
    <div class="ps-panel-header">
        <span class="ps-icon">🔒</span> Sécurité — aujourd'hui
        <div class="ps-panel-tools">
            <span class="ps-badge ps-badge-<?php echo e($security['alert_level'] === 'red' ? 'danger' : ($security['alert_level'] === 'amber' ? 'warning' : 'success')); ?>">
                <?php echo e(strtoupper($security['alert_level'])); ?>

            </span>
        </div>
    </div>
    <div class="ps-panel-body">
        <div class="ps-grid-4">
            <?php $__currentLoopData = [
                ['Rate Limits (429)',   $security['rate_limited_today'],  $security['rate_limited_today'] > 100],
                ['Auth invalides (401)', $security['unauthorized_today'], $security['unauthorized_today'] > 50],
                ['Taux d\'erreur API',  $security['error_rate_pct'] . '%', $security['error_rate_pct'] > 5],
                ['Anomalies en attente', $security['pending_anomalies'],  $security['pending_anomalies'] > 0],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="padding:14px;border:1px solid <?php echo e($stat[2] ? 'var(--ps-warning)' : 'var(--ps-border)'); ?>;border-radius:4px;">
                <div class="ps-kpi-label"><?php echo e($stat[0]); ?></div>
                <div style="font-size:24px;font-weight:700;margin-top:4px;color:<?php echo e($stat[2] ? 'var(--ps-warning)' : 'var(--ps-text)'); ?>" class="num">
                    <?php echo e(is_numeric($stat[1]) ? number_format($stat[1], 0, '.', '\'') : $stat[1]); ?>

                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if(!empty($security['suspicious_ips'])): ?>
        <div class="ps-alert ps-alert-danger" style="margin-top:16px;margin-bottom:0;align-items:flex-start;flex-direction:column;gap:8px">
            <strong>IPs suspectes (&gt;100 erreurs 429 aujourd'hui)</strong>
            <?php $__currentLoopData = $security['suspicious_ips']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="display:flex;justify-content:space-between;width:100%;font-size:12.5px">
                <code><?php echo e($ip['ip']); ?></code>
                <strong><?php echo e(number_format($ip['count'], 0, '.', '\'')); ?> tentatives</strong>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.prestashop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>