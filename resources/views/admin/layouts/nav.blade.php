{{--
    Partial : resources/views/admin/layouts/nav.blade.php
    Navigation latérale PrestaShop 8 — sections + sous-menus accordéon.
    L'état actif s'appuie sur les noms de routes (admin.*).
--}}
@php
    $is = fn($pattern) => request()->routeIs($pattern);
@endphp

{{-- ════ TABLEAU DE BORD ════ --}}
<div class="ps-nav-section">Tableau de bord</div>

<div class="ps-nav-item {{ $is('admin.dashboard') ? 'active' : '' }}">
    <a href="{{ route('admin.dashboard') }}" class="ps-nav-link">
        <span class="ps-icon">📊</span>
        <span class="ps-label">Tableau de bord</span>
    </a>
</div>

{{-- ════ VENDRE ════ --}}
<div class="ps-nav-section">Vendre</div>

{{-- Clients / Utilisateurs (avec sous-menu) --}}
<div class="ps-nav-item {{ $is('admin.users.*') ? 'active open' : '' }}"
     :class="{ 'open': openMenu === 'users' }">
    <div class="ps-nav-link" x-on:click="toggle('users')">
        <span class="ps-icon">👥</span>
        <span class="ps-label">Clients</span>
        <span class="ps-caret">▶</span>
    </div>
    <ul class="ps-submenu">
        <li><a href="{{ route('admin.users.index') }}" class="ps-nav-link {{ $is('admin.users.index') || $is('admin.users.show') ? 'active' : '' }}">
            <span class="ps-label">Liste des clients</span>
        </a></li>
        <li><a href="{{ route('admin.users.admins') }}" class="ps-nav-link {{ $is('admin.users.admins') ? 'active' : '' }}">
            <span class="ps-label">Administrateurs</span>
        </a></li>
    </ul>
</div>

{{-- Affiliés --}}
<div class="ps-nav-item {{ $is('admin.affiliates.*') ? 'active' : '' }}">
    <a href="{{ route('admin.affiliates.index') }}" class="ps-nav-link">
        <span class="ps-icon">🤝</span>
        <span class="ps-label">Affiliation</span>
    </a>
</div>

{{-- ════ CONFIGURER ════ --}}
<div class="ps-nav-section">Configurer</div>

{{-- Tarifs / Catalogue (avec sous-menu) --}}
<div class="ps-nav-item {{ $is('admin.pricing.*') ? 'active open' : '' }}"
     :class="{ 'open': openMenu === 'pricing' }">
    <div class="ps-nav-link" x-on:click="toggle('pricing')">
        <span class="ps-icon">💳</span>
        <span class="ps-label">Tarifs &amp; Forfaits</span>
        <span class="ps-caret">▶</span>
    </div>
    <ul class="ps-submenu">
        <li><a href="{{ route('admin.pricing.index') }}" class="ps-nav-link {{ $is('admin.pricing.index') ? 'active' : '' }}">
            <span class="ps-label">Plans d'abonnement</span>
        </a></li>
    </ul>
</div>

{{-- Imports ASTRA (avec sous-menu) --}}
<div class="ps-nav-item {{ $is('admin.import.*') ? 'active open' : '' }}"
     :class="{ 'open': openMenu === 'import' }">
    <div class="ps-nav-link" x-on:click="toggle('import')">
        <span class="ps-icon">⚡</span>
        <span class="ps-label">Données ASTRA</span>
        <span class="ps-caret">▶</span>
    </div>
    <ul class="ps-submenu">
        <li><a href="{{ route('admin.import.index') }}" class="ps-nav-link {{ $is('admin.import.index') ? 'active' : '' }}">
            <span class="ps-label">Imports &amp; historique</span>
        </a></li>
    </ul>
</div>

{{-- ════ PARAMÈTRES ════ --}}
<div class="ps-nav-section">Paramètres</div>

<div class="ps-nav-item {{ $is('admin.settings.*') ? 'active' : '' }}">
    <a href="{{ route('admin.settings.index') }}" class="ps-nav-link">
        <span class="ps-icon">⚙️</span>
        <span class="ps-label">Paramètres</span>
    </a>
</div>

<div class="ps-nav-item">
    <a href="{{ url('/fr') }}" target="_blank" class="ps-nav-link">
        <span class="ps-icon">🌐</span>
        <span class="ps-label">Voir la boutique</span>
    </a>
</div>
<div class="ps-nav-item">
    <form method="POST" action="{{ route('logout') }}" id="ps-logout-form">
        @csrf
        <a href="#" class="ps-nav-link" onclick="event.preventDefault(); document.getElementById('ps-logout-form').submit();">
            <span class="ps-icon">🚪</span>
            <span class="ps-label">Déconnexion</span>
        </a>
    </form>
</div>
