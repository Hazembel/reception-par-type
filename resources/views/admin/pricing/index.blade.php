{{-- Vue : admin/pricing/index — Plans tarifaires (PrestaShop, accordéon) --}}
@extends('admin.layouts.prestashop')

@section('page_title', 'Tarifs & Forfaits')
@section('title_icon', '💳')
@section('breadcrumb')<span>Tarifs &amp; Forfaits</span>@endsection

@section('content')

<div class="ps-alert ps-alert-info">
    <span>ℹ️</span> Les modifications sont appliquées immédiatement et le cache est invalidé automatiquement.
</div>

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
            <span x-text="open === {{ $plan->level }} ? '▲' : '▼'" style="color:var(--ps-text-muted)"></span>
        </div>
    </div>

    <div class="ps-panel-body" x-show="open === {{ $plan->level }}" x-cloak>
        <form method="POST" action="{{ route('admin.pricing.update', $plan) }}">
            @csrf
            <div class="ps-grid-3">
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

            <button type="submit" class="ps-btn ps-btn-primary">Enregistrer « {{ $plan->name_fr }} »</button>
        </form>
    </div>
</div>
@endforeach
</div>

<style>[x-cloak]{display:none!important}</style>
@endsection
