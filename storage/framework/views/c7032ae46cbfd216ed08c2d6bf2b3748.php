<?php $__env->startSection('page_title', 'Tarifs & Forfaits'); ?>
<?php $__env->startSection('title_icon', '💳'); ?>
<?php $__env->startSection('breadcrumb'); ?><span>Tarifs &amp; Forfaits</span><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="ps-alert ps-alert-info">
    <span>ℹ️</span> Les modifications sont appliquées immédiatement et le cache est invalidé automatiquement.
</div>

<div x-data="{ open: null }">
<?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="ps-panel">
    <div class="ps-panel-header" style="cursor:pointer" x-on:click="open === <?php echo e($plan->level); ?> ? open = null : open = <?php echo e($plan->level); ?>">
        <span class="ps-badge ps-badge-<?php echo e($plan->level >= 6 ? 'info' : 'muted'); ?>" style="min-width:28px;justify-content:center"><?php echo e($plan->level); ?></span>
        <?php echo e($plan->name_fr); ?>

        <?php if (! ($plan->is_active)): ?><span class="ps-badge ps-badge-muted">DÉSACTIVÉ</span><?php endif; ?>
        <div class="ps-panel-tools" style="align-items:center;gap:14px">
            <span style="font-weight:700"><?php echo e($plan->level === 8 ? 'Sur devis' : number_format($plan->price_monthly_chf / 100, 2, '.', '\'') . ' CHF'); ?><span class="ps-help" style="font-weight:400">/mois</span></span>
            <span class="ps-help"><?php echo e($plan->web_monthly_limit === -1 ? '∞' : number_format($plan->web_monthly_limit, 0, '.', '\'')); ?> fiches</span>
            <?php $__currentLoopData = ['can_export_pdf' => 'PDF', 'can_export_csv' => 'CSV', 'can_use_api' => 'API']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="ps-badge ps-badge-<?php echo e($plan->$f ? 'success' : 'muted'); ?>"><?php echo e($lbl); ?></span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <span x-text="open === <?php echo e($plan->level); ?> ? '▲' : '▼'" style="color:var(--ps-text-muted)"></span>
        </div>
    </div>

    <div class="ps-panel-body" x-show="open === <?php echo e($plan->level); ?>" x-cloak>
        <form method="POST" action="<?php echo e(route('admin.pricing.update', $plan)); ?>">
            <?php echo csrf_field(); ?>
            <div class="ps-grid-3">
                <div class="ps-form-group">
                    <label class="ps-label">Prix mensuel <span class="ps-help">centimes — 4900 = 49.00</span></label>
                    <input type="number" name="price_monthly_chf" value="<?php echo e($plan->price_monthly_chf); ?>" min="0" step="100" class="ps-input num" <?php echo e($plan->level === 1 ? 'disabled' : ''); ?>>
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Prix annuel <span class="ps-help">centimes</span></label>
                    <input type="number" name="price_yearly_chf" value="<?php echo e($plan->price_yearly_chf); ?>" min="0" step="100" class="ps-input num">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Prix jeton <span class="ps-help">centimes</span></label>
                    <input type="number" name="token_price_chf" value="<?php echo e($plan->token_price_chf); ?>" min="0" max="10000" class="ps-input num">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Limite web/mois <span class="ps-help">-1 = illimité</span></label>
                    <input type="number" name="web_monthly_limit" value="<?php echo e($plan->web_monthly_limit); ?>" min="-1" class="ps-input num">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Limite API/mois</label>
                    <input type="number" name="api_monthly_limit" value="<?php echo e($plan->api_monthly_limit); ?>" min="-1" class="ps-input num">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">API req/min</label>
                    <input type="number" name="api_rate_per_minute" value="<?php echo e($plan->api_rate_per_minute); ?>" min="0" max="1000" class="ps-input num">
                </div>
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:16px;margin:8px 0 18px">
                <?php $__currentLoopData = [
                    'can_export_pdf' => 'Export PDF', 'can_export_csv' => 'Export CSV',
                    'can_use_api' => 'Accès API', 'can_compare' => 'Comparateur',
                    'can_use_tax_calc' => 'Simulateur fiscal', 'is_public' => 'Visible /pricing', 'is_active' => 'Plan actif',
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="ps-check">
                    <input type="hidden" name="<?php echo e($f); ?>" value="0">
                    <input type="checkbox" name="<?php echo e($f); ?>" value="1" <?php echo e($plan->$f ? 'checked' : ''); ?> <?php echo e(($plan->level === 8 && $f === 'is_active') ? 'disabled' : ''); ?>>
                    <?php echo e($lbl); ?>

                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <button type="submit" class="ps-btn ps-btn-primary">Enregistrer « <?php echo e($plan->name_fr); ?> »</button>
        </form>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<style>[x-cloak]{display:none!important}</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.prestashop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/admin/pricing/index.blade.php ENDPATH**/ ?>