{{--
    Composant : <x-skeleton-loader>
    resources/views/components/skeleton-loader.blade.php

    Composant universel de Skeleton Loading avec pulsation Tailwind animate-pulse.
    Simule le chargement fluide sans spinner brutal.

    Variantes disponibles :
      type="vehicle-card"    → Squelette d'une fiche technique (défaut)
      type="search-results"  → Squelette d'une liste de résultats
      type="vehicle-header"  → Squelette de l'en-tête d'une fiche
      type="stats"           → Squelette des compteurs de la page d'accueil

    Usage Blade :
      <x-skeleton-loader />                            ← vehicle-card
      <x-skeleton-loader type="search-results" :count="5" />
      <x-skeleton-loader type="vehicle-header" />
--}}
@props([
    'type'  => 'vehicle-card',
    'count' => 3,
])

{{-- Classe de base commune à tous les squelettes --}}
@php
    $pulse = 'animate-pulse';
    $bg    = 'bg-slate-200 dark:bg-white/[0.06]';
    $bgLt  = 'bg-slate-100 dark:bg-white/[0.03]';
@endphp

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- VARIANTE : vehicle-card                                                   --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if($type === 'vehicle-card')
    <div class="
        {{ $pulse }}
        bg-white dark:bg-marine/30
        border border-slate-200 dark:border-white/5
        rounded-2xl overflow-hidden
    ">
        {{-- En-tête --}}
        <div class="flex items-center gap-2.5 px-5 py-4 border-b border-slate-100 dark:border-white/5">
            <div class="w-6 h-6 rounded-md {{ $bg }}"></div>
            <div class="h-3 w-28 rounded-full {{ $bg }}"></div>
        </div>

        {{-- Lignes de données --}}
        <div class="divide-y divide-slate-100 dark:divide-white/[0.04]">
            @foreach([['w-20', 'w-16'], ['w-16', 'w-24'], ['w-24', 'w-12'], ['w-14', 'w-20']] as $i => $row)
                <div class="flex items-center justify-between px-5 py-3 gap-4">
                    <div class="h-3 {{ $row[0] }} rounded-full {{ $bg }}"
                         style="animation-delay: {{ $i * 80 }}ms"></div>
                    <div class="h-3 {{ $row[1] }} rounded-full {{ $bgLt }}"
                         style="animation-delay: {{ $i * 80 + 40 }}ms"></div>
                </div>
            @endforeach
        </div>
    </div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- VARIANTE : vehicle-header                                                 --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@elseif($type === 'vehicle-header')
    <div class="{{ $pulse }} mb-8">
        {{-- Fil d'Ariane --}}
        <div class="flex items-center gap-2 mb-6">
            <div class="h-2.5 w-14 rounded-full {{ $bg }}"></div>
            <div class="h-2.5 w-2 rounded-full {{ $bgLt }}"></div>
            <div class="h-2.5 w-20 rounded-full {{ $bg }}"></div>
            <div class="h-2.5 w-2 rounded-full {{ $bgLt }}"></div>
            <div class="h-2.5 w-32 rounded-full {{ $bg }}"></div>
        </div>

        {{-- Titre de la fiche --}}
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div>
                {{-- Ligne marque + modèle --}}
                <div class="h-8 w-72 rounded-xl {{ $bg }} mb-2"></div>
                {{-- Variante --}}
                <div class="h-4 w-48 rounded-full {{ $bgLt }}"></div>
            </div>
            {{-- Badge TG --}}
            <div class="h-16 w-44 rounded-xl {{ $bgLt }}"></div>
        </div>

        {{-- Indicateurs de statut --}}
        <div class="flex items-center gap-3 mt-4">
            <div class="h-3 w-28 rounded-full {{ $bg }}"></div>
            <div class="h-3 w-1 rounded-full {{ $bgLt }}"></div>
            <div class="h-3 w-36 rounded-full {{ $bgLt }}"></div>
        </div>
    </div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- VARIANTE : search-results                                                 --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@elseif($type === 'search-results')
    <div class="space-y-3 {{ $pulse }}">
        @for($i = 0; $i < $count; $i++)
            <div class="
                flex items-center gap-4 p-4 rounded-2xl
                bg-white dark:bg-marine/30
                border border-slate-200 dark:border-white/5
            ">
                {{-- Icône marque (simulée) --}}
                <div class="w-10 h-10 rounded-xl {{ $bg }} shrink-0"
                     style="animation-delay: {{ $i * 60 }}ms"></div>

                {{-- Infos principales --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="h-4 w-40 rounded-full {{ $bg }}"
                             style="animation-delay: {{ $i * 60 + 30 }}ms"></div>
                        <div class="h-4 w-24 rounded-full {{ $bgLt }}"
                             style="animation-delay: {{ $i * 60 + 60 }}ms"></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-28 rounded-full {{ $bgLt }}"
                             style="animation-delay: {{ $i * 60 + 90 }}ms"></div>
                        <div class="h-3 w-3 rounded-full {{ $bgLt }}"></div>
                        <div class="h-3 w-20 rounded-full {{ $bgLt }}"
                             style="animation-delay: {{ $i * 60 + 120 }}ms"></div>
                    </div>
                </div>

                {{-- Badge TG --}}
                <div class="h-8 w-36 rounded-lg {{ $bgLt }} shrink-0"
                     style="animation-delay: {{ $i * 60 + 150 }}ms"></div>

                {{-- Chevron --}}
                <div class="w-4 h-4 rounded {{ $bgLt }} shrink-0"></div>
            </div>
        @endfor
    </div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- VARIANTE : stats (compteurs Hero)                                         --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@elseif($type === 'stats')
    <div class="grid grid-cols-3 gap-4 sm:gap-8 {{ $pulse }}">
        @foreach([0, 1, 2] as $i)
            <div class="text-center">
                <div class="h-9 w-24 rounded-xl {{ $bg }} mx-auto mb-2"
                     style="animation-delay: {{ $i * 100 }}ms"></div>
                <div class="h-2.5 w-14 rounded-full {{ $bgLt }} mx-auto"
                     style="animation-delay: {{ $i * 100 + 50 }}ms"></div>
            </div>
        @endforeach
    </div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- VARIANTE : grille de fiches complète (vehicle-card × 4)                  --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@elseif($type === 'vehicle-grid')
    {{-- En-tête de la fiche --}}
    <x-skeleton-loader type="vehicle-header" />

    {{-- Grille 2×2 de cartes --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @for($i = 0; $i < 4; $i++)
            <div style="animation-delay: {{ $i * 80 }}ms">
                <x-skeleton-loader type="vehicle-card" />
            </div>
        @endfor
    </div>
@endif
