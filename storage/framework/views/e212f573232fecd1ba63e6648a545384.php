<?php $__env->startSection('page_title', 'Affiliation'); ?>
<?php $__env->startSection('title_icon', '🤝'); ?>
<?php $__env->startSection('breadcrumb'); ?><span>Affiliation</span><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="ps-kpi-row">
    <div class="ps-kpi">
        <span class="ps-kpi-label">Commissions en attente</span>
        <span class="ps-kpi-value" style="color:var(--ps-warning)"><?php echo e(number_format($pendingTotal/100, 2, '.', '\'')); ?><span style="font-size:14px"> CHF</span></span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Commissions approuvées</span>
        <span class="ps-kpi-value cyan"><?php echo e(number_format($approvedTotal/100, 2, '.', '\'')); ?><span style="font-size:14px"> CHF</span></span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Affiliés</span>
        <span class="ps-kpi-value"><?php echo e($affiliates->total()); ?></span>
    </div>
</div>

<div x-data="{ open: null }">
<?php $__empty_1 = true; $__currentLoopData = $affiliates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $affiliate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<?php
    $pendingCts  = $affiliate->earnings()->pending()->sum('commission_cts');
    $approvedCts = $affiliate->earnings()->approved()->sum('commission_cts');
?>
<div class="ps-panel">
    <div class="ps-panel-header" style="cursor:pointer" x-on:click="open === <?php echo e($affiliate->id); ?> ? open = null : open = <?php echo e($affiliate->id); ?>">
        <code style="font-weight:700;color:var(--ps-primary)"><?php echo e($affiliate->affiliate_code); ?></code>
        <span style="font-weight:400"><?php echo e($affiliate->user?->name); ?></span>
        <div class="ps-panel-tools" style="align-items:center;gap:14px">
            <span class="ps-help"><?php echo e($affiliate->commission_rate_pct); ?>%</span>
            <span class="ps-help"><?php echo e(number_format($affiliate->total_clicks, 0, '.', '\'')); ?> clics</span>
            <span style="font-weight:700;color:var(--ps-primary)"><?php echo e(number_format($affiliate->total_earned_cts/100, 2, '.', '\'')); ?> CHF</span>
            <span class="ps-badge ps-badge-<?php echo e($affiliate->status === 'active' ? 'success' : ($affiliate->status === 'pending' ? 'warning' : 'danger')); ?>"><?php echo e(strtoupper($affiliate->status)); ?></span>
            <span x-text="open === <?php echo e($affiliate->id); ?> ? '▲' : '▼'" style="color:var(--ps-text-muted)"></span>
        </div>
    </div>

    <div class="ps-panel-body" x-show="open === <?php echo e($affiliate->id); ?>" x-cloak>
        <div class="ps-grid-3" style="margin-bottom:16px">
            <div style="padding:12px;background:#fafbfc;border:1px solid var(--ps-border);border-radius:4px;text-align:center">
                <div style="font-size:18px;font-weight:700;color:var(--ps-warning)" class="num"><?php echo e(number_format($pendingCts/100, 2, '.', '\'')); ?> CHF</div>
                <div class="ps-help">En attente</div>
            </div>
            <div style="padding:12px;background:#fafbfc;border:1px solid var(--ps-border);border-radius:4px;text-align:center">
                <div style="font-size:18px;font-weight:700;color:var(--ps-primary)" class="num"><?php echo e(number_format($approvedCts/100, 2, '.', '\'')); ?> CHF</div>
                <div class="ps-help">Approuvé</div>
            </div>
            <div style="padding:12px;background:#fafbfc;border:1px solid var(--ps-border);border-radius:4px;text-align:center">
                <div style="font-size:15px;color:var(--ps-text-muted)" class="num"><?php echo e($affiliate->last_paid_at?->format('d.m.Y') ?? '—'); ?></div>
                <div class="ps-help">Dernier versement</div>
            </div>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <?php if($pendingCts > 0): ?>
            <form method="POST" action="<?php echo e(route('admin.affiliates.approve', $affiliate)); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="ps-btn ps-btn-primary ps-btn-sm">Approuver <?php echo e(number_format($pendingCts/100, 2, '.', '\'')); ?> CHF</button>
            </form>
            <?php endif; ?>

            <?php if($approvedCts > 0): ?>
            <form method="POST" action="<?php echo e(route('admin.affiliates.pay', $affiliate)); ?>" style="display:flex;gap:8px;align-items:center">
                <?php echo csrf_field(); ?>
                <input type="text" name="payment_reference" placeholder="Réf. virement" class="ps-input ps-btn-sm" style="height:30px;width:160px" required>
                <button type="submit" class="ps-btn ps-btn-success ps-btn-sm">Payer <?php echo e(number_format($approvedCts/100, 2, '.', '\'')); ?> CHF</button>
            </form>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('admin.affiliates.toggle', $affiliate)); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="ps-btn ps-btn-sm"><?php echo e($affiliate->status === 'active' ? 'Suspendre' : 'Activer'); ?></button>
            </form>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<div class="ps-panel"><div class="ps-panel-body" style="text-align:center;color:var(--ps-text-muted);padding:32px">Aucun affilié enregistré.</div></div>
<?php endif; ?>
</div>

<?php if($affiliates->hasPages()): ?>
<div style="margin-top:8px"><?php echo e($affiliates->links()); ?></div>
<?php endif; ?>

<style>[x-cloak]{display:none!important}</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.prestashop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/admin/affiliates/index.blade.php ENDPATH**/ ?>