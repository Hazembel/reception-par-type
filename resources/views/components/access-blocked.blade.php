{{--
    Composant : <x-access-blocked>
    resources/views/components/access-blocked.blade.php

    Affiché à la place des cartes "Masses & Charges" et "Jantes & Pneumatiques"
    quand l'utilisateur n'a pas les droits suffisants.

    Variantes selon $reason :
      - level_1_free         → Tableau comparatif + CTA 2 CHF / abonnement
      - insufficient_tokens  → Recharger les jetons
      - pro_quota_exceeded   → Upgrade vers Pro+
      - subscription_expired → Renouveler l'abonnement
--}}
@props([
    'reason'       => 'level_1_free',
    'context'      => [],
    'vehicleId'    => null,
])

@php $locale = app()->getLocale(); @endphp

<div class="
    col-span-2 rounded-2xl border border-slate-200 dark:border-white/10
    bg-white dark:bg-marine/30
    overflow-hidden
">

    {{-- ── CAS 1 : Niveau 1 — Tableau comparatif ───────────────────────────── --}}
    @if($reason === 'level_1_free')
        <div class="p-6">
            <div class="text-center mb-6">
                <div class="w-10 h-10 rounded-full bg-astra/10 dark:bg-astra/15 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-astra" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-sm text-slate-900 dark:text-white mb-1">
                    {{ __('access.comparison.title') }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    @switch($locale)
                        @case('de') Daten zu Gewichten, Felgen und Bereifung freischalten @break
                        @case('it') Sblocca dati su pesi, cerchi e pneumatici @break
                        @case('en') Unlock weight, wheel and tyre data @break
                        @default Débloquez les données de masse, jantes et pneumatiques
                    @endswitch
                </p>
            </div>

            {{-- Tableau comparatif 3 colonnes --}}
            <div class="grid grid-cols-3 gap-3 mb-6">
                {{-- Accès à l'unité --}}
                <div class="rounded-xl border-2 border-astra bg-astra/5 dark:bg-astra/8 p-4 text-center relative">
                    <div class="absolute -top-2.5 left-1/2 -translate-x-1/2">
                        <span class="text-2xs bg-astra text-white px-2 py-0.5 rounded-full font-semibold whitespace-nowrap">
                            @switch($locale)
                                @case('de') Empfohlen @break
                                @case('it') Consigliato @break
                                @case('en') Recommended @break
                                @default Recommandé
                            @endswitch
                        </span>
                    </div>
                    <div class="font-display text-xl text-slate-900 dark:text-white font-normal mt-1">
                        {{ $context['token_price'] ?? 2 }} <span class="text-sm text-slate-500">CHF</span>
                    </div>
                    <div class="text-xs font-medium text-astra mb-2">{{ __('access.comparison.one_time') }}</div>
                    <p class="text-2xs text-slate-500 dark:text-slate-400 leading-relaxed mb-3">
                        {{ __('access.comparison.one_time_desc') }}
                    </p>
                    <a href="{{ url('/' . $locale . '/pricing') }}?unlock={{ $vehicleId }}"
                       class="block w-full text-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-astra text-white hover:bg-astra-600 transition-colors duration-150">
                        {{ __('access.comparison.cta_one_time') }}
                    </a>
                </div>

                {{-- Starter --}}
                <div class="rounded-xl border border-slate-200 dark:border-white/10 p-4 text-center">
                    <div class="font-display text-xl text-slate-900 dark:text-white font-normal mt-1">
                        9 <span class="text-sm text-slate-500">CHF</span>
                    </div>
                    <div class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-2">{{ __('access.comparison.starter') }}</div>
                    <p class="text-2xs text-slate-500 dark:text-slate-400 leading-relaxed mb-3">
                        {{ __('access.comparison.starter_desc') }}
                    </p>
                    <a href="{{ url('/' . $locale . '/pricing') }}"
                       class="block w-full text-center px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-white/5 transition-colors duration-150 text-slate-700 dark:text-slate-300">
                        {{ __('access.comparison.cta_subscribe') }}
                    </a>
                </div>

                {{-- Pro --}}
                <div class="rounded-xl border border-slate-200 dark:border-white/10 p-4 text-center">
                    <div class="font-display text-xl text-slate-900 dark:text-white font-normal mt-1">
                        49 <span class="text-sm text-slate-500">CHF</span>
                    </div>
                    <div class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-2">{{ __('access.comparison.pro') }}</div>
                    <p class="text-2xs text-slate-500 dark:text-slate-400 leading-relaxed mb-3">
                        {{ __('access.comparison.pro_desc') }}
                    </p>
                    <a href="{{ url('/' . $locale . '/pricing') }}"
                       class="block w-full text-center px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-200 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-white/5 transition-colors duration-150 text-slate-700 dark:text-slate-300">
                        {{ __('access.comparison.cta_subscribe') }}
                    </a>
                </div>
            </div>
        </div>

    {{-- ── CAS 2 : Jetons insuffisants ─────────────────────────────────────── --}}
    @elseif($reason === 'insufficient_tokens')
        <div class="p-6 text-center">
            <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-500/10 flex items-center justify-center mx-auto mb-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75M3.75 13.875v3.75"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-900 dark:text-white mb-1">
                {{ __('access.no_tokens', ['balance' => $context['tokens_balance'] ?? 0]) }}
            </p>
            <a href="{{ url('/' . $locale . '/pricing') }}"
               class="mt-3 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold transition-all duration-200 shadow-sm">
                {{ __('access.top_up_tokens') }}
            </a>
        </div>

    {{-- ── CAS 3 : Quota Pro dépassé ────────────────────────────────────────── --}}
    @elseif($reason === 'pro_quota_exceeded')
        <div class="p-6 text-center">
            <div class="w-10 h-10 rounded-full bg-astra/10 flex items-center justify-center mx-auto mb-3">
                <svg class="w-5 h-5 text-astra" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-900 dark:text-white mb-1">
                {{ __('access.quota_exceeded', ['limit' => $context['monthly_limit'] ?? 500]) }}
            </p>
            <a href="{{ url('/' . $locale . '/pricing') }}"
               class="mt-3 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-astra hover:bg-astra-600 text-white text-sm font-semibold transition-all duration-200 shadow-sm shadow-astra/20">
                {{ __('access.upgrade_to') }}
            </a>
        </div>
    @endif
</div>
