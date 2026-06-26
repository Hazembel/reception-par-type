<?php $__env->startSection('page_title', 'Données ASTRA'); ?>
<?php $__env->startSection('title_icon', '⚡'); ?>
<?php $__env->startSection('breadcrumb'); ?><span>Données ASTRA</span><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="ps-kpi-row">
    <div class="ps-kpi">
        <span class="ps-kpi-label">Imports 2000 (mensuel)</span>
        <span class="ps-kpi-value"><?php echo e($stats['total_2000']); ?></span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Imports 5000 (newsletter)</span>
        <span class="ps-kpi-value"><?php echo e($stats['total_5000']); ?></span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">En cours</span>
        <span class="ps-kpi-value cyan"><?php echo e($stats['running_jobs']); ?></span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Échoués (7j)</span>
        <span class="ps-kpi-value" style="color:<?php echo e($stats['failed_recent'] > 0 ? 'var(--ps-danger)' : 'var(--ps-success)'); ?>"><?php echo e($stats['failed_recent']); ?></span>
    </div>
</div>

<div class="ps-grid-2">
    
    <div class="ps-panel">
        <div class="ps-panel-header"><span class="ps-icon">▶️</span> Lancer un import</div>
        <div class="ps-panel-body">
            <form method="POST" action="<?php echo e(route('admin.import.trigger')); ?>">
                <?php echo csrf_field(); ?>
                <div class="ps-form-group">
                    <label class="ps-label">Type d'import</label>
                    <select name="import_type" class="ps-select">
                        <option value="5000">Newsletter 5000 (hebdomadaire, rapide)</option>
                        <option value="2000">Fichier principal 2000 (mensuel, 1-3 h)</option>
                    </select>
                </div>
                <label class="ps-check" style="margin-bottom:14px">
                    <input type="hidden" name="force" value="0">
                    <input type="checkbox" name="force" value="1"> Forcer le retraitement (ignore le hash)
                </label>
                <button type="submit" class="ps-btn ps-btn-primary">Démarrer l'import</button>
            </form>
        </div>
    </div>

    
    <div class="ps-panel">
        <div class="ps-panel-header"><span class="ps-icon">💾</span> État des fichiers sur disque</div>
        <div class="ps-panel-body flush">
            <table class="ps-table">
                <tbody>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Dossier 2000</td>
                        <td style="text-align:right"><span class="ps-badge ps-badge-<?php echo e($diskStatus['dir_2000_exists'] ? 'success' : 'danger'); ?>"><?php echo e($diskStatus['dir_2000_exists'] ? 'OK' : 'Absent'); ?></span></td>
                    </tr>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Dossier 5000</td>
                        <td style="text-align:right"><span class="ps-badge ps-badge-<?php echo e($diskStatus['dir_5000_exists'] ? 'success' : 'danger'); ?>"><?php echo e($diskStatus['dir_5000_exists'] ? 'OK' : 'Absent'); ?></span></td>
                    </tr>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Fichier principal</td>
                        <td style="text-align:right">
                            <?php if($diskStatus['main_file_exists']): ?>
                                <span class="ps-badge ps-badge-success"><?php echo e($diskStatus['main_file_size']); ?></span>
                                <div class="ps-help" style="margin-top:3px"><?php echo e($diskStatus['main_file_modified']); ?></div>
                            <?php else: ?>
                                <span class="ps-badge ps-badge-warning">Absent</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Fichiers newsletter</td>
                        <td style="text-align:right" class="num"><strong><?php echo e($diskStatus['newsletter_files']); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="ps-panel">
    <div class="ps-panel-header"><span class="ps-icon">📜</span> Historique des imports</div>
    <div class="ps-panel-body flush">
        <table class="ps-table">
            <thead>
                <tr><th>#</th><th>Type</th><th>Fichier</th><th>Statut</th><th>Insérés</th><th>MAJ</th><th>Date</th><th style="text-align:right">Actions</th></tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $recentImports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="num"><?php echo e($log->id); ?></td>
                    <td><span class="ps-badge ps-badge-muted"><?php echo e($log->import_type); ?></span></td>
                    <td style="color:var(--ps-text-muted)"><code><?php echo e(\Illuminate\Support\Str::limit($log->filename, 28)); ?></code></td>
                    <td>
                        <span class="ps-badge ps-badge-<?php echo e($log->status === 'completed' ? 'success' : ($log->status === 'running' ? 'info' : ($log->status === 'partial' ? 'warning' : 'danger'))); ?>"><?php echo e(strtoupper($log->status)); ?></span>
                    </td>
                    <td class="num"><?php echo e(number_format($log->lines_inserted, 0, '.', '\'')); ?></td>
                    <td class="num"><?php echo e(number_format($log->lines_updated, 0, '.', '\'')); ?></td>
                    <td style="color:var(--ps-text-muted)"><?php echo e($log->created_at->format('d.m.Y H:i')); ?></td>
                    <td style="text-align:right">
                        <?php if(in_array($log->status, ['failed', 'partial'])): ?>
                        <form method="POST" action="<?php echo e(route('admin.import.retry', $log)); ?>" style="display:inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="ps-btn ps-btn-sm">Rejouer</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" style="text-align:center;padding:32px;color:var(--ps-text-muted)">Aucun import enregistré.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($recentImports->hasPages()): ?>
    <div style="padding:14px 18px;border-top:1px solid var(--ps-border)"><?php echo e($recentImports->links()); ?></div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.prestashop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/admin/import/index.blade.php ENDPATH**/ ?>