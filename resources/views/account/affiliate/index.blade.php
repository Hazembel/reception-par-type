{{-- ═══════════════════════════════════════════════════════════════════════
     Vue : resources/views/account/affiliate/index.blade.php
     Tableau de bord affilié
     ═══════════════════════════════════════════════════════════════════════ --}}
@extends('layouts.app')
@section('content')
<div class="pt-20 pb-16 max-w-4xl mx-auto px-4 sm:px-6">

  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="font-display text-2xl text-slate-900 dark:text-white">
        Programme <span class="text-astra">Affilié</span>
      </h1>
      <p class="text-xs text-slate-400 mt-1">Code : <code class="font-mono font-semibold text-astra">{{ $affiliate->affiliate_code }}</code> · Commission : {{ $affiliate->commission_rate_pct }}%</p>
    </div>
    @if($affiliate->status === 'pending')
      <span class="text-xs px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400 font-medium">En attente de validation</span>
    @elseif($affiliate->status === 'active')
      <span class="text-xs px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 font-medium">Actif ✓</span>
    @endif
  </div>

  @if(session('success'))
    <div class="mb-5 p-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm">{{ session('success') }}</div>
  @endif

  {{-- Lien d'affiliation --}}
  <div class="bg-white dark:bg-marine/30 border border-slate-200 dark:border-white/5 rounded-2xl p-5 mb-6"
       x-data="{ copied: false }">
    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">Votre lien d'affiliation</p>
    <div class="flex items-center gap-3">
      <code class="flex-1 text-xs font-mono bg-slate-50 dark:bg-white/5 px-3 py-2 rounded-lg border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 truncate">
        {{ $refUrl }}
      </code>
      <button
        x-on:click="navigator.clipboard.writeText('{{ $refUrl }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
        class="flex-shrink-0 px-3 py-2 text-xs font-medium rounded-lg border border-slate-200 dark:border-white/10 hover:bg-astra/10 hover:text-astra hover:border-astra/30 transition-all"
        x-bind:class="{ 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 border-emerald-200 dark:border-emerald-500/20': copied }"
      >
        <span x-show="!copied">Copier</span>
        <span x-show="copied">✓ Copié !</span>
      </button>
    </div>
  </div>

  {{-- Stats --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    @foreach([
      ['label' => 'Clics ce mois',    'value' => number_format($thisMonthClicks, 0, '.', '\''), 'color' => ''],
      ['label' => 'Conversions',      'value' => $thisMonthConvs, 'color' => 'text-emerald-600 dark:text-emerald-400'],
      ['label' => 'En attente',       'value' => number_format($pendingCts/100, 2, '.', '\'') . ' CHF', 'color' => 'text-astra'],
      ['label' => 'Total versé',      'value' => number_format($totalPaidCts/100, 2, '.', '\'') . ' CHF', 'color' => ''],
    ] as $s)
    <div class="bg-white dark:bg-marine/30 border border-slate-200 dark:border-white/5 rounded-2xl p-5">
      <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">{{ $s['label'] }}</p>
      <p class="font-display text-xl font-normal {{ $s['color'] ?: 'text-slate-900 dark:text-white' }} font-mono">{{ $s['value'] }}</p>
    </div>
    @endforeach
  </div>

  {{-- Tableau des gains --}}
  <div class="bg-white dark:bg-marine/30 border border-slate-200 dark:border-white/5 rounded-2xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 dark:border-white/5">
      <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Historique des commissions</h2>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-white/[0.02] text-xs text-slate-500">
        <tr>
          <th class="px-4 py-3 text-left">Date</th>
          <th class="px-4 py-3 text-right">Vente</th>
          <th class="px-4 py-3 text-right">Commission</th>
          <th class="px-4 py-3 text-left">Statut</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04]">
        @forelse($recentEarnings as $e)
        <tr>
          <td class="px-4 py-3 text-slate-500 text-xs">{{ $e->created_at->format('d.m.Y') }}</td>
          <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($e->sale_amount_chf, 2, '.', '\'') }} CHF</td>
          <td class="px-4 py-3 text-right font-mono font-semibold text-astra">+{{ number_format($e->commission_chf, 2, '.', '\'') }} CHF</td>
          <td class="px-4 py-3">
            <span class="text-2xs px-2 py-0.5 rounded-full font-medium
              {{ $e->status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400' :
                 ($e->status === 'approved' ? 'bg-blue-100 dark:bg-blue-500/15 text-blue-700 dark:text-blue-400' :
                 ($e->status === 'cancelled' ? 'bg-red-100 dark:bg-red-500/15 text-red-600' :
                  'bg-amber-100 dark:bg-amber-500/15 text-amber-700 dark:text-amber-400')) }}">
              {{ match($e->status) { 'paid'=>'Versé', 'approved'=>'Approuvé', 'cancelled'=>'Annulé', default=>'En attente' } }}
            </span>
          </td>
        </tr>
        @empty
        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400 text-sm">Aucune commission enregistrée.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
