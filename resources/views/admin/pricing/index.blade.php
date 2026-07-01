{{-- Vue : admin/pricing/index — Plans tarifaires (PrestaShop, accordéon) --}}
@extends('admin.layouts.prestashop')

@section('page_title', 'Tarifs & Forfaits')
@section('title_icon', '💳')
@section('breadcrumb')<span>Tarifs &amp; Forfaits</span>@endsection

@section('content')

@if(session('success'))
<div class="ps-alert ps-alert-success"><span>✅</span> {{ session('success') }}</div>
@endif
@if($errors->any())
<div class="ps-alert ps-alert-danger"><span>⚠️</span> {{ $errors->first() }}</div>
@endif

<div class="ps-alert ps-alert-info">
    <span>ℹ️</span> Les modifications sont appliquées immédiatement et le cache est invalidé automatiquement.
</div>

{{-- ── Créer un nouveau plan ──────────────────────────────────────────────── --}}
<div x-data="{ open: false }" class="ps-panel" style="border:2px dashed var(--ps-border);margin-bottom:8px">
    <div class="ps-panel-header" style="cursor:pointer" x-on:click="open = !open">
        <span class="ps-badge ps-badge-success">+</span>
        <strong>Créer un nouveau plan</strong>
        <div class="ps-panel-tools">
            <span x-text="open ? '▲' : '▼'" style="color:var(--ps-text-muted)"></span>
        </div>
    </div>
    <div class="ps-panel-body" x-show="open" x-cloak>
        <form method="POST" action="{{ route('admin.pricing.store') }}">
            @csrf
            <div class="ps-grid-3">
                <div class="ps-form-group">
                    <label class="ps-label">Niveau <span class="ps-help">entier unique ex: 5</span></label>
                    <input type="number" name="level" value="{{ old('level') }}" min="1" max="99" required class="ps-input num" placeholder="5">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Nom (FR) <span class="ps-help">requis</span></label>
                    <input type="text" name="name_fr" value="{{ old('name_fr') }}" required class="ps-input" placeholder="Pro+">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Ordre d'affichage</label>
                    <input type="number" name="display_order" value="{{ old('display_order') }}" min="0" class="ps-input num" placeholder="auto">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Nom (DE)</label>
                    <input type="text" name="name_de" value="{{ old('name_de') }}" class="ps-input">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Nom (IT)</label>
                    <input type="text" name="name_it" value="{{ old('name_it') }}" class="ps-input">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Nom (EN)</label>
                    <input type="text" name="name_en" value="{{ old('name_en') }}" class="ps-input">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Prix mensuel <span class="ps-help">centimes</span></label>
                    <input type="number" name="price_monthly_chf" value="{{ old('price_monthly_chf', 0) }}" min="0" step="100" class="ps-input num">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Prix annuel <span class="ps-help">centimes</span></label>
                    <input type="number" name="price_yearly_chf" value="{{ old('price_yearly_chf', 0) }}" min="0" step="100" class="ps-input num">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Prix jeton <span class="ps-help">centimes</span></label>
                    <input type="number" name="token_price_chf" value="{{ old('token_price_chf', 0) }}" min="0" max="10000" class="ps-input num">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Limite web/mois <span class="ps-help">-1 = illimité</span></label>
                    <input type="number" name="web_monthly_limit" value="{{ old('web_monthly_limit', 10) }}" min="-1" class="ps-input num">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Limite API/mois</label>
                    <input type="number" name="api_monthly_limit" value="{{ old('api_monthly_limit', 0) }}" min="-1" class="ps-input num">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">API req/min</label>
                    <input type="number" name="api_rate_per_minute" value="{{ old('api_rate_per_minute', 0) }}" min="0" max="1000" class="ps-input num">
                </div>
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:16px;margin:8px 0 18px">
                @foreach([
                    'can_export_pdf' => 'Export PDF', 'can_export_csv' => 'Export CSV',
                    'can_use_api' => 'Accès API', 'can_compare' => 'Comparateur',
                    'can_use_tax_calc' => 'Simulateur fiscal', 'is_public' => 'Visible /pricing', 'is_active' => 'Plan actif',
                ] as $f => $lbl)
                <label class="ps-check">
                    <input type="hidden" name="{{ $f }}" value="0">
                    <input type="checkbox" name="{{ $f }}" value="1" {{ old($f) ? 'checked' : '' }}>
                    {{ $lbl }}
                </label>
                @endforeach
            </div>

            <div class="ps-form-group" style="max-width:480px">
                <label class="ps-label">Description (FR)</label>
                <input type="text" name="description_fr" value="{{ old('description_fr') }}" class="ps-input" placeholder="Pour les professionnels exigeants">
            </div>
            <div class="ps-form-group" style="max-width:380px">
                <label class="ps-label">Stripe Price ID <span class="ps-help">optionnel — price_xxx</span></label>
                <input type="text" name="stripe_price_id" value="{{ old('stripe_price_id') }}" class="ps-input" placeholder="price_1ABC...">
            </div>

            <button type="submit" class="ps-btn ps-btn-primary">Créer le plan</button>
        </form>
    </div>
</div>

{{-- ── Plans existants ─────────────────────────────────────────────────────── --}}
<div x-data="{ open: null }">
@foreach($plans as $plan)
<div class="ps-panel">
    <div class="ps-panel-header" style="cursor:pointer" x-on:click="open === {{ $plan->level }} ? open = null : open = {{ $plan->level }}">
        <span class="ps-badge ps-badge-{{ $plan->level >= 6 ? 'info' : 'muted' }}" style="min-width:28px;justify-content:center">{{ $plan->level }}</span>
        {{ $plan->name_fr }}
        @unless($plan->is_active)<span class="ps-badge ps-badge-muted">DÉSACTIVÉ</span>@endunless
        <div class="ps-panel-tools" style="align-items:center;gap:14px">
            <span style="font-weight:700">{{ $plan->level === 8 ? 'Sur devis' : number_format($plan->price_monthly_chf / 100, 2, '.', '\'') . ' CHF' }}<span class="ps-help" style="font-weight:400">/mois</span></span>
            <span class="ps-help">{{ $plan->web_monthly_limit === -1 ? '∞' : number_format($plan->web_monthly_limit, 0, '.', '\'') }} fiches</span>
            @foreach(['can_export_pdf' => 'PDF', 'can_export_csv' => 'CSV', 'can_use_api' => 'API'] as $f => $lbl)
                <span class="ps-badge ps-badge-{{ $plan->$f ? 'success' : 'muted' }}">{{ $lbl }}</span>
            @endforeach
            {{-- Delete button (stops accordion toggle) --}}
            <form method="POST" action="{{ route('admin.pricing.destroy', $plan) }}"
                  x-on:click.stop
                  x-on:submit.prevent="if(confirm('Supprimer le plan « {{ addslashes($plan->name_fr) }} » (niveau {{ $plan->level }}) ? Les utilisateurs sur ce plan resteront à ce niveau mais le plan ne sera plus gérable.')) $el.submit()">
                @csrf
                @method('DELETE')
                <button type="submit" class="ps-btn ps-btn-danger" style="padding:2px 10px;font-size:12px" title="Supprimer ce plan">🗑️</button>
            </form>
            <span x-text="open === {{ $plan->level }} ? '▲' : '▼'" style="color:var(--ps-text-muted)"></span>
        </div>
    </div>

    <div class="ps-panel-body" x-show="open === {{ $plan->level }}" x-cloak>
        <form method="POST" action="{{ route('admin.pricing.update', $plan) }}">
            @csrf
            <div class="ps-grid-3">
                <div class="ps-form-group">
                    <label class="ps-label">Nom (FR)</label>
                    <input type="text" name="name_fr" value="{{ $plan->name_fr }}" required class="ps-input">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Nom (DE)</label>
                    <input type="text" name="name_de" value="{{ $plan->name_de }}" class="ps-input">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Nom (IT)</label>
                    <input type="text" name="name_it" value="{{ $plan->name_it }}" class="ps-input">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Prix mensuel <span class="ps-help">centimes — 4900 = 49.00</span></label>
                    <input type="number" name="price_monthly_chf" value="{{ $plan->price_monthly_chf }}" min="0" step="100" class="ps-input num" {{ $plan->level === 1 ? 'disabled' : '' }}>
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Prix annuel <span class="ps-help">centimes</span></label>
                    <input type="number" name="price_yearly_chf" value="{{ $plan->price_yearly_chf }}" min="0" step="100" class="ps-input num">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Prix jeton <span class="ps-help">centimes</span></label>
                    <input type="number" name="token_price_chf" value="{{ $plan->token_price_chf }}" min="0" max="10000" class="ps-input num">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Limite web/mois <span class="ps-help">-1 = illimité</span></label>
                    <input type="number" name="web_monthly_limit" value="{{ $plan->web_monthly_limit }}" min="-1" class="ps-input num">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Limite API/mois</label>
                    <input type="number" name="api_monthly_limit" value="{{ $plan->api_monthly_limit }}" min="-1" class="ps-input num">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">API req/min</label>
                    <input type="number" name="api_rate_per_minute" value="{{ $plan->api_rate_per_minute }}" min="0" max="1000" class="ps-input num">
                </div>
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:16px;margin:8px 0 18px">
                @foreach([
                    'can_export_pdf' => 'Export PDF', 'can_export_csv' => 'Export CSV',
                    'can_use_api' => 'Accès API', 'can_compare' => 'Comparateur',
                    'can_use_tax_calc' => 'Simulateur fiscal', 'is_public' => 'Visible /pricing', 'is_active' => 'Plan actif',
                ] as $f => $lbl)
                <label class="ps-check">
                    <input type="hidden" name="{{ $f }}" value="0">
                    <input type="checkbox" name="{{ $f }}" value="1" {{ $plan->$f ? 'checked' : '' }} {{ ($plan->level === 8 && $f === 'is_active') ? 'disabled' : '' }}>
                    {{ $lbl }}
                </label>
                @endforeach
            </div>

            <div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:18px">
                <div class="ps-form-group" style="flex:1;min-width:220px">
                    <label class="ps-label">Description (FR)</label>
                    <input type="text" name="description_fr" value="{{ $plan->description_fr }}" class="ps-input">
                </div>
                <div class="ps-form-group" style="flex:1;min-width:220px">
                    <label class="ps-label">Stripe Price ID</label>
                    <input type="text" name="stripe_price_id" value="{{ $plan->stripe_price_id }}" class="ps-input" placeholder="price_1ABC...">
                </div>
                <div class="ps-form-group" style="max-width:100px">
                    <label class="ps-label">Ordre</label>
                    <input type="number" name="display_order" value="{{ $plan->display_order }}" min="0" class="ps-input num">
                </div>
            </div>

            <button type="submit" class="ps-btn ps-btn-primary">Enregistrer « {{ $plan->name_fr }} »</button>
        </form>
    </div>
</div>
@endforeach
</div>

<style>[x-cloak]{display:none!important}</style>
@endsection
