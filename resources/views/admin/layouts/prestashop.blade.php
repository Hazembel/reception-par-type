{{--
    Layout : resources/views/admin/layouts/prestashop.blade.php
    ──────────────────────────────────────────────────────────────────────────
    Back-office façon PrestaShop 8.
    - Sidebar verticale sombre (#363a41) repliable, avec sous-menus accordéon
    - Header clair avec fil d'Ariane + recherche + menu profil
    - Zone de contenu gris clair (#f1f1f1) accueillant des "panels" blancs

    Utilisation dans une vue admin :
        @extends('admin.layouts.prestashop')
        @section('page_title', 'Tableau de bord')
        @section('breadcrumb') ... @endsection
        @section('content') ... @endsection
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="ps-admin">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Administration') · reception-par-type.ch</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <style>
        /* ═══════════════════════════════════════════════════════════════════
           PrestaShop 8 — Design tokens
           ═══════════════════════════════════════════════════════════════════ */
        :root {
            --ps-sidebar:        #363a41;   /* fond sidebar */
            --ps-sidebar-hover:  #2b2e34;   /* item survolé / actif */
            --ps-sidebar-active: #25b9d7;   /* barre accent cyan */
            --ps-sidebar-text:   #bbbecc;   /* texte item */
            --ps-sidebar-muted:  #7e8088;   /* libellés de section */
            --ps-primary:        #25b9d7;   /* cyan PrestaShop */
            --ps-primary-hover:  #1ba8c4;
            --ps-success:        #00a98f;
            --ps-danger:         #ff4c4c;
            --ps-warning:        #ff9a52;
            --ps-info:           #25b9d7;
            --ps-bg:             #f1f1f1;   /* fond zone contenu */
            --ps-panel:          #ffffff;
            --ps-border:         #dfdfdf;
            --ps-text:           #363a41;
            --ps-text-muted:     #6c868e;
            --ps-header-h:       53px;
            --ps-sidebar-w:      230px;
            --ps-sidebar-w-collapsed: 52px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 13px;
            color: var(--ps-text);
            background: var(--ps-bg);
            -webkit-font-smoothing: antialiased;
        }

        a { text-decoration: none; color: var(--ps-primary); }
        a:hover { color: var(--ps-primary-hover); }

        /* ── Layout shell ──────────────────────────────────────────────────── */
        .ps-layout { display: flex; min-height: 100vh; }

        /* ═══════════════════════════════════════════════════════════════════
           SIDEBAR
           ═══════════════════════════════════════════════════════════════════ */
        .ps-sidebar {
            width: var(--ps-sidebar-w);
            background: var(--ps-sidebar);
            color: var(--ps-sidebar-text);
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; bottom: 0; left: 0;
            z-index: 40;
            transition: width .2s ease;
            overflow-x: hidden;
        }
        .ps-sidebar.collapsed { width: var(--ps-sidebar-w-collapsed); }

        /* Logo */
        .ps-logo {
            height: var(--ps-header-h);
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 16px;
            background: #2b2e34;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .ps-logo .mark {
            width: 24px; height: 24px;
            background: var(--ps-primary);
            border-radius: 4px;
            display: grid; place-items: center;
            color: #fff; font-size: 13px; flex-shrink: 0;
        }
        .ps-sidebar.collapsed .ps-logo .label { display: none; }

        /* Navigation */
        .ps-nav { flex: 1; overflow-y: auto; padding: 8px 0 24px; }
        .ps-nav::-webkit-scrollbar { width: 6px; }
        .ps-nav::-webkit-scrollbar-thumb { background: #4a4e56; border-radius: 3px; }

        .ps-nav-section {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--ps-sidebar-muted);
            padding: 16px 16px 6px;
            white-space: nowrap;
        }
        .ps-sidebar.collapsed .ps-nav-section { text-align: center; padding: 16px 0 6px; font-size: 0; }
        .ps-sidebar.collapsed .ps-nav-section::after { content: '•'; font-size: 11px; }

        /* Item de menu */
        .ps-nav-item { position: relative; }
        .ps-nav-link {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 16px;
            color: var(--ps-sidebar-text);
            font-size: 13px;
            white-space: nowrap;
            cursor: pointer;
            border-left: 3px solid transparent;
            transition: background .12s, color .12s;
        }
        .ps-nav-link:hover { background: var(--ps-sidebar-hover); color: #fff; }
        .ps-nav-link .ps-icon { width: 18px; text-align: center; font-size: 15px; flex-shrink: 0; }
        .ps-nav-link .ps-label { flex: 1; }
        .ps-nav-link .ps-caret { font-size: 10px; transition: transform .2s; opacity: .6; }
        .ps-nav-item.open > .ps-nav-link .ps-caret { transform: rotate(90deg); }

        /* Item actif */
        .ps-nav-item.active > .ps-nav-link,
        .ps-nav-link.active {
            background: var(--ps-sidebar-hover);
            color: #fff;
            border-left-color: var(--ps-sidebar-active);
        }

        .ps-sidebar.collapsed .ps-label,
        .ps-sidebar.collapsed .ps-caret { display: none; }

        /* Sous-menu */
        .ps-submenu {
            list-style: none;
            background: #303339;
            overflow: hidden;
            max-height: 0;
            transition: max-height .25s ease;
        }
        .ps-nav-item.open > .ps-submenu { max-height: 500px; }
        .ps-submenu .ps-nav-link {
            padding-left: 46px;
            font-size: 12.5px;
            color: #9ea1aa;
        }
        .ps-submenu .ps-nav-link:hover { color: #fff; }
        .ps-submenu .ps-nav-link.active { color: #fff; background: #25282d; border-left-color: var(--ps-sidebar-active); }
        .ps-sidebar.collapsed .ps-submenu { display: none; }

        /* ═══════════════════════════════════════════════════════════════════
           MAIN (header + contenu)
           ═══════════════════════════════════════════════════════════════════ */
        .ps-main {
            flex: 1;
            margin-left: var(--ps-sidebar-w);
            transition: margin-left .2s ease;
            min-width: 0;
        }
        .ps-sidebar.collapsed ~ .ps-main { margin-left: var(--ps-sidebar-w-collapsed); }

        /* Header */
        .ps-header {
            height: var(--ps-header-h);
            background: #fff;
            border-bottom: 1px solid var(--ps-border);
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0 18px;
            position: sticky;
            top: 0;
            z-index: 30;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        .ps-burger {
            background: none; border: none; cursor: pointer;
            font-size: 18px; color: var(--ps-text-muted);
            width: 30px; height: 30px; border-radius: 4px;
        }
        .ps-burger:hover { background: var(--ps-bg); color: var(--ps-text); }

        .ps-search {
            flex: 1; max-width: 420px; position: relative;
        }
        .ps-search input {
            width: 100%;
            height: 34px;
            border: 1px solid var(--ps-border);
            border-radius: 4px;
            padding: 0 12px 0 34px;
            font-size: 13px;
            font-family: inherit;
            background: #fafbfc;
            outline: none;
        }
        .ps-search input:focus { border-color: var(--ps-primary); background: #fff; }
        .ps-search .icon {
            position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
            color: var(--ps-text-muted); font-size: 14px;
        }

        .ps-header-actions { margin-left: auto; display: flex; align-items: center; gap: 6px; }
        .ps-header-btn {
            display: flex; align-items: center; gap: 7px;
            height: 34px; padding: 0 11px;
            border: 1px solid var(--ps-border);
            border-radius: 4px;
            background: #fff;
            color: var(--ps-text);
            font-size: 13px;
            cursor: pointer;
            font-family: inherit;
        }
        .ps-header-btn:hover { background: var(--ps-bg); }
        .ps-header-btn.primary { background: var(--ps-primary); color: #fff; border-color: var(--ps-primary); }
        .ps-header-btn.primary:hover { background: var(--ps-primary-hover); }
        .ps-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--ps-primary); color: #fff;
            display: grid; place-items: center; font-weight: 600; font-size: 13px;
        }

        /* ── Barre de titre + fil d'Ariane ─────────────────────────────────── */
        .ps-pagebar { padding: 18px 22px 0; }
        .ps-breadcrumb {
            font-size: 12px; color: var(--ps-text-muted);
            display: flex; align-items: center; gap: 6px; margin-bottom: 6px;
        }
        .ps-breadcrumb a { color: var(--ps-text-muted); }
        .ps-breadcrumb a:hover { color: var(--ps-primary); }
        .ps-breadcrumb .sep { opacity: .5; }
        .ps-page-title {
            font-size: 22px; font-weight: 600; color: var(--ps-text);
            display: flex; align-items: center; gap: 10px;
        }
        .ps-page-title .ps-icon { color: var(--ps-text-muted); }

        /* ── Contenu ───────────────────────────────────────────────────────── */
        .ps-content { padding: 18px 22px 40px; }

        /* ═══════════════════════════════════════════════════════════════════
           COMPOSANTS — Panels, boutons, tables, badges, alertes
           ═══════════════════════════════════════════════════════════════════ */
        .ps-panel {
            background: var(--ps-panel);
            border: 1px solid var(--ps-border);
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,.06);
            margin-bottom: 18px;
        }
        .ps-panel-header {
            padding: 13px 18px;
            border-bottom: 1px solid var(--ps-border);
            font-size: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .ps-panel-header .ps-icon { color: var(--ps-text-muted); font-size: 16px; }
        .ps-panel-header .ps-panel-tools { margin-left: auto; display: flex; gap: 6px; }
        .ps-panel-body { padding: 18px; }
        .ps-panel-body.flush { padding: 0; }

        /* Boutons */
        .ps-btn {
            display: inline-flex; align-items: center; gap: 7px;
            height: 36px; padding: 0 15px;
            border: 1px solid var(--ps-border);
            border-radius: 4px;
            background: #fff; color: var(--ps-text);
            font-size: 13px; font-weight: 600; cursor: pointer;
            font-family: inherit; white-space: nowrap;
            transition: all .12s;
        }
        .ps-btn:hover { background: var(--ps-bg); }
        .ps-btn-primary { background: var(--ps-primary); color: #fff; border-color: var(--ps-primary); }
        .ps-btn-primary:hover { background: var(--ps-primary-hover); color: #fff; }
        .ps-btn-success { background: var(--ps-success); color: #fff; border-color: var(--ps-success); }
        .ps-btn-success:hover { filter: brightness(.94); color: #fff; }
        .ps-btn-danger  { background: var(--ps-danger); color: #fff; border-color: var(--ps-danger); }
        .ps-btn-sm { height: 30px; padding: 0 11px; font-size: 12px; }

        /* Tables (Helper List) */
        .ps-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .ps-table thead th {
            background: #fafbfc;
            text-align: left;
            padding: 11px 14px;
            font-weight: 600;
            font-size: 12px;
            color: var(--ps-text-muted);
            border-bottom: 1px solid var(--ps-border);
            white-space: nowrap;
        }
        .ps-table tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        .ps-table tbody tr:hover { background: #fafdff; }
        .ps-table .num { font-variant-numeric: tabular-nums; }

        /* Badges */
        .ps-badge {
            display: inline-flex; align-items: center;
            padding: 3px 9px; border-radius: 3px;
            font-size: 11.5px; font-weight: 600; white-space: nowrap;
        }
        .ps-badge-success { background: #e0f5f0; color: #00876f; }
        .ps-badge-danger  { background: #ffe6e6; color: #d63333; }
        .ps-badge-warning { background: #fff0e3; color: #d97b2e; }
        .ps-badge-info    { background: #e0f4f9; color: #1592ad; }
        .ps-badge-muted   { background: #eef0f2; color: #6c868e; }

        /* Alertes */
        .ps-alert {
            border-radius: 4px; padding: 13px 16px; margin-bottom: 18px;
            font-size: 13px; display: flex; align-items: center; gap: 10px;
            border-left: 4px solid;
        }
        .ps-alert-success { background: #e8f8f4; border-color: var(--ps-success); color: #00876f; }
        .ps-alert-danger  { background: #ffecec; border-color: var(--ps-danger); color: #d63333; }
        .ps-alert-info    { background: #e7f6fa; border-color: var(--ps-info); color: #1592ad; }

        /* KPI cards (dashboard PrestaShop) */
        .ps-kpi-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 0;
            background: #fff;
            border: 1px solid var(--ps-border);
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,.06);
            margin-bottom: 18px;
            overflow: hidden;
        }
        .ps-kpi {
            padding: 16px 18px;
            border-right: 1px solid var(--ps-border);
            display: flex; flex-direction: column; gap: 4px;
        }
        .ps-kpi:last-child { border-right: none; }
        .ps-kpi .ps-kpi-label { font-size: 11.5px; color: var(--ps-text-muted); text-transform: uppercase; letter-spacing: .03em; }
        .ps-kpi .ps-kpi-value { font-size: 26px; font-weight: 700; color: var(--ps-text); line-height: 1.1; }
        .ps-kpi .ps-kpi-value.cyan { color: var(--ps-primary); }
        .ps-kpi .ps-kpi-value.green { color: var(--ps-success); }
        .ps-kpi .ps-kpi-sub { font-size: 11.5px; color: var(--ps-text-muted); }

        /* Forms */
        .ps-form-group { margin-bottom: 15px; }
        .ps-label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        .ps-help { font-size: 11.5px; color: var(--ps-text-muted); font-weight: 400; }
        .ps-input, .ps-select {
            width: 100%; height: 36px;
            border: 1px solid var(--ps-border); border-radius: 4px;
            padding: 0 11px; font-size: 13px; font-family: inherit;
            background: #fff; outline: none;
        }
        .ps-input:focus, .ps-select:focus { border-color: var(--ps-primary); }
        .ps-input.num { font-variant-numeric: tabular-nums; }
        .ps-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .ps-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .ps-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }

        /* Switch / checkbox PS */
        .ps-check { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; }

        /* Responsive */
        @media (max-width: 900px) {
            .ps-sidebar { transform: translateX(-100%); transition: transform .2s; width: var(--ps-sidebar-w); }
            .ps-sidebar.mobile-open { transform: translateX(0); }
            .ps-main { margin-left: 0 !important; }
            .ps-grid-2, .ps-grid-3, .ps-grid-4 { grid-template-columns: 1fr; }
            .ps-search { display: none; }
        }
    </style>
    @stack('head')
</head>
<body>
<div class="ps-layout"
     x-data="{
        collapsed: false,
        mobileOpen: false,
        openMenu: '{{ $activeMenu ?? '' }}',
        toggle(m){ this.openMenu = this.openMenu === m ? '' : m }
     }">

    {{-- ════════════════ SIDEBAR ════════════════ --}}
    <aside class="ps-sidebar" :class="{ 'collapsed': collapsed, 'mobile-open': mobileOpen }">
        <div class="ps-logo">
            <span class="mark">R</span>
            <span class="label">reception-par-type</span>
        </div>

        <nav class="ps-nav">
            @include('admin.layouts.nav')
        </nav>
    </aside>

    {{-- ════════════════ MAIN ════════════════ --}}
    <div class="ps-main">

        {{-- Header --}}
        <header class="ps-header">
            <button class="ps-burger" x-on:click="window.innerWidth <= 900 ? mobileOpen = !mobileOpen : collapsed = !collapsed">☰</button>

            <div class="ps-search">
                <span class="icon">🔍</span>
                <form action="{{ route('admin.users.index') }}" method="GET">
                    <input type="text" name="q" placeholder="Rechercher un utilisateur, une fiche…">
                </form>
            </div>

            <div class="ps-header-actions">
                <a href="{{ url('/fr') }}" target="_blank" class="ps-header-btn">
                    <span>🔗</span> Voir le site
                </a>
                <div class="ps-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
            </div>
        </header>

        {{-- Titre + fil d'Ariane --}}
        <div class="ps-pagebar">
            <div class="ps-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Accueil</a>
                @hasSection('breadcrumb')
                    <span class="sep">›</span>
                    @yield('breadcrumb')
                @endif
            </div>
            <h1 class="ps-page-title">
                @hasSection('title_icon')<span class="ps-icon">@yield('title_icon')</span>@endif
                @yield('page_title', 'Tableau de bord')
            </h1>
        </div>

        {{-- Contenu --}}
        <main class="ps-content">
            @if(session('success'))
                <div class="ps-alert ps-alert-success"><span>✓</span> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="ps-alert ps-alert-danger"><span>✕</span> {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="ps-alert ps-alert-danger">
                    <span>✕</span>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="//unpkg.com/alpinejs" defer></script>
@stack('scripts')
</body>
</html>
