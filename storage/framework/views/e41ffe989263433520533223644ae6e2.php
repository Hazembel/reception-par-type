
<?php
    $is = fn($pattern) => request()->routeIs($pattern);
?>


<div class="ps-nav-section">Tableau de bord</div>

<div class="ps-nav-item <?php echo e($is('admin.dashboard') ? 'active' : ''); ?>">
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="ps-nav-link">
        <span class="ps-icon">📊</span>
        <span class="ps-label">Tableau de bord</span>
    </a>
</div>


<div class="ps-nav-section">Vendre</div>


<div class="ps-nav-item <?php echo e($is('admin.users.*') ? 'active open' : ''); ?>"
     :class="{ 'open': openMenu === 'users' }">
    <div class="ps-nav-link" x-on:click="toggle('users')">
        <span class="ps-icon">👥</span>
        <span class="ps-label">Clients</span>
        <span class="ps-caret">▶</span>
    </div>
    <ul class="ps-submenu">
        <li><a href="<?php echo e(route('admin.users.index')); ?>" class="ps-nav-link <?php echo e($is('admin.users.index') || $is('admin.users.show') ? 'active' : ''); ?>">
            <span class="ps-label">Liste des clients</span>
        </a></li>
        <li><a href="<?php echo e(route('admin.users.admins')); ?>" class="ps-nav-link <?php echo e($is('admin.users.admins') ? 'active' : ''); ?>">
            <span class="ps-label">Administrateurs</span>
        </a></li>
    </ul>
</div>


<div class="ps-nav-item <?php echo e($is('admin.affiliates.*') ? 'active' : ''); ?>">
    <a href="<?php echo e(route('admin.affiliates.index')); ?>" class="ps-nav-link">
        <span class="ps-icon">🤝</span>
        <span class="ps-label">Affiliation</span>
    </a>
</div>


<div class="ps-nav-section">Configurer</div>


<div class="ps-nav-item <?php echo e($is('admin.pricing.*') ? 'active open' : ''); ?>"
     :class="{ 'open': openMenu === 'pricing' }">
    <div class="ps-nav-link" x-on:click="toggle('pricing')">
        <span class="ps-icon">💳</span>
        <span class="ps-label">Tarifs &amp; Forfaits</span>
        <span class="ps-caret">▶</span>
    </div>
    <ul class="ps-submenu">
        <li><a href="<?php echo e(route('admin.pricing.index')); ?>" class="ps-nav-link <?php echo e($is('admin.pricing.index') ? 'active' : ''); ?>">
            <span class="ps-label">Plans d'abonnement</span>
        </a></li>
    </ul>
</div>


<div class="ps-nav-item <?php echo e($is('admin.import.*') ? 'active open' : ''); ?>"
     :class="{ 'open': openMenu === 'import' }">
    <div class="ps-nav-link" x-on:click="toggle('import')">
        <span class="ps-icon">⚡</span>
        <span class="ps-label">Données ASTRA</span>
        <span class="ps-caret">▶</span>
    </div>
    <ul class="ps-submenu">
        <li><a href="<?php echo e(route('admin.import.index')); ?>" class="ps-nav-link <?php echo e($is('admin.import.index') ? 'active' : ''); ?>">
            <span class="ps-label">Imports &amp; historique</span>
        </a></li>
    </ul>
</div>


<div class="ps-nav-section">Paramètres</div>

<div class="ps-nav-item <?php echo e($is('admin.settings.*') ? 'active' : ''); ?>">
    <a href="<?php echo e(route('admin.settings.index')); ?>" class="ps-nav-link">
        <span class="ps-icon">⚙️</span>
        <span class="ps-label">Paramètres</span>
    </a>
</div>

<div class="ps-nav-item">
    <a href="<?php echo e(url('/fr')); ?>" target="_blank" class="ps-nav-link">
        <span class="ps-icon">🌐</span>
        <span class="ps-label">Voir la boutique</span>
    </a>
</div>
<div class="ps-nav-item">
    <form method="POST" action="<?php echo e(route('logout')); ?>" id="ps-logout-form">
        <?php echo csrf_field(); ?>
        <a href="#" class="ps-nav-link" onclick="event.preventDefault(); document.getElementById('ps-logout-form').submit();">
            <span class="ps-icon">🚪</span>
            <span class="ps-label">Déconnexion</span>
        </a>
    </form>
</div>
<?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/admin/layouts/nav.blade.php ENDPATH**/ ?>