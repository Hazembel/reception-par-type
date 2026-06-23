{{--
    Composant : <x-vehicle-card>
    resources/views/components/vehicle-card.blade.php

    Usage :
      <x-vehicle-card title="Motorisation" icon="⚡" :public="true">
          <x-slot name="rows"> ... </x-slot>
      </x-vehicle-card>
--}}
@props([
    'title'  => '',
    'icon'   => '',
    'public' => true,
])

<div class="
    group relative
    bg-white dark:bg-marine/30
    border border-slate-200 dark:border-white/5
    rounded-2xl overflow-hidden
    hover:border-slate-300 dark:hover:border-white/10
    hover:shadow-lg hover:shadow-black/5 dark:hover:shadow-black/20
    transition-all duration-300
">
    {{-- En-tête de carte --}}
    <div class="
        flex items-center gap-2.5 px-5 py-4
        border-b border-slate-100 dark:border-white/5
        bg-slate-50/50 dark:bg-white/[0.02]
    ">
        <span class="text-base leading-none" aria-hidden="true">{{ $icon }}</span>
        <h2 class="font-semibold text-sm text-slate-700 dark:text-slate-200 tracking-wide">
            {{ $title }}
        </h2>
        @if(!$public)
            <span class="ml-auto">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </span>
        @endif
    </div>

    {{-- Lignes de données --}}
    <div class="divide-y divide-slate-100 dark:divide-white/[0.04]">
        {{ $rows }}
    </div>
</div>
