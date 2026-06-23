{{-- Vue : admin/affiliates/index — Gestion affiliés (PrestaShop) --}}
@extends('admin.layouts.prestashop')

@section('page_title', 'Affiliation')
@section('title_icon', '🤝')
@section('breadcrumb')<span>Affiliation</span>@endsection

@section('content')

<div class="ps-kpi-row">
    <div class="ps-kpi">
        <span class="ps-kpi-label">Commissions en attente</span>
        <span class="ps-kpi-value" style="color:var(--ps-warning)">{{ number_format($pendingTotal/100, 2, '.', '\'') }}<span style="font-size:14px"> CHF</span></span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Commissions approuvées</span>
        <span class="ps-kpi-value cyan">{{ number_format($approvedTotal/100, 2, '.', '\'') }}<span style="font-size:14px"> CHF</span></span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Affiliés</span>
        <span class="ps-kpi-value">{{ $affiliates->total() }}</span>
    </div>
</div>

<div x-data="{ open: null }">
@forelse($affiliates as $affiliate)
@php
    $pendingCts  = $affiliate->earnings()->pending()->sum('commission_cts');
    $approvedCts = $affiliate->earnings()->approved()->sum('commission_cts');
@endphp
<div class="ps-panel">
    <div class="ps-panel-header" style="cursor:pointer" x-on:click="open === {{ $affiliate->id }} ? open = null : open = {{ $affiliate->id }}">
        <code style="font-weight:700;color:var(--ps-primary)">{{ $affiliate->affiliate_code }}</code>
        <span style="font-weight:400">{{ $affiliate->user?->name }}</span>
        <div class="ps-panel-tools" style="align-items:center;gap:14px">
            <span class="ps-help">{{ $affiliate->commission_rate_pct }}%</span>
            <span class="ps-help">{{ number_format($affiliate->total_clicks, 0, '.', '\'') }} clics</span>
            <span style="font-weight:700;color:var(--ps-primary)">{{ number_format($affiliate->total_earned_cts/100, 2, '.', '\'') }} CHF</span>
            <span class="ps-badge ps-badge-{{ $affiliate->status === 'active' ? 'success' : ($affiliate->status === 'pending' ? 'warning' : 'danger') }}">{{ strtoupper($affiliate->status) }}</span>
            <span x-text="open === {{ $affiliate->id }} ? '▲' : '▼'" style="color:var(--ps-text-muted)"></span>
        </div>
    </div>

    <div class="ps-panel-body" x-show="open === {{ $affiliate->id }}" x-cloak>
        <div class="ps-grid-3" style="margin-bottom:16px">
            <div style="padding:12px;background:#fafbfc;border:1px solid var(--ps-border);border-radius:4px;text-align:center">
                <div style="font-size:18px;font-weight:700;color:var(--ps-warning)" class="num">{{ number_format($pendingCts/100, 2, '.', '\'') }} CHF</div>
                <div class="ps-help">En attente</div>
            </div>
            <div style="padding:12px;background:#fafbfc;border:1px solid var(--ps-border);border-radius:4px;text-align:center">
                <div style="font-size:18px;font-weight:700;color:var(--ps-primary)" class="num">{{ number_format($approvedCts/100, 2, '.', '\'') }} CHF</div>
                <div class="ps-help">Approuvé</div>
            </div>
            <div style="padding:12px;background:#fafbfc;border:1px solid var(--ps-border);border-radius:4px;text-align:center">
                <div style="font-size:15px;color:var(--ps-text-muted)" class="num">{{ $affiliate->last_paid_at?->format('d.m.Y') ?? '—' }}</div>
                <div class="ps-help">Dernier versement</div>
            </div>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            @if($pendingCts > 0)
            <form method="POST" action="{{ route('admin.affiliates.approve', $affiliate) }}">
                @csrf
                <button type="submit" class="ps-btn ps-btn-primary ps-btn-sm">Approuver {{ number_format($pendingCts/100, 2, '.', '\'') }} CHF</button>
            </form>
            @endif

            @if($approvedCts > 0)
            <form method="POST" action="{{ route('admin.affiliates.pay', $affiliate) }}" style="display:flex;gap:8px;align-items:center">
                @csrf
                <input type="text" name="payment_reference" placeholder="Réf. virement" class="ps-input ps-btn-sm" style="height:30px;width:160px" required>
                <button type="submit" class="ps-btn ps-btn-success ps-btn-sm">Payer {{ number_format($approvedCts/100, 2, '.', '\'') }} CHF</button>
            </form>
            @endif

            <form method="POST" action="{{ route('admin.affiliates.toggle', $affiliate) }}">
                @csrf
                <button type="submit" class="ps-btn ps-btn-sm">{{ $affiliate->status === 'active' ? 'Suspendre' : 'Activer' }}</button>
            </form>
        </div>
    </div>
</div>
@empty
<div class="ps-panel"><div class="ps-panel-body" style="text-align:center;color:var(--ps-text-muted);padding:32px">Aucun affilié enregistré.</div></div>
@endforelse
</div>

@if($affiliates->hasPages())
<div style="margin-top:8px">{{ $affiliates->links() }}</div>
@endif

<style>[x-cloak]{display:none!important}</style>
@endsection
