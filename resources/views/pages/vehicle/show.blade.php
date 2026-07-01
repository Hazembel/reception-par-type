{{--
    Fiche technique véhicule : resources/views/pages/vehicle/show.blade.php

    Variables Blade reçues du VehicleController :
      $vehicle  : modèle Vehicle (ou null si non trouvé)
      $canViewAdvanced : bool — l'utilisateur peut voir les données complètes
      $meta_*   : variables SEO du BaseController
--}}
@extends('layouts.app')

@section('content')
<div class="pt-20 pb-16 max-w-5xl mx-auto px-4 sm:px-6">

    {{-- ── Fil d'Ariane ─────────────────────────────────────────────────────── --}}
    <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6" aria-label="Fil d'Ariane">
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}"
           class="hover:text-astra transition-colors">Accueil</a>
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('search', ['locale' => app()->getLocale()]) }}"
           class="hover:text-astra transition-colors">Recherche</a>
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-600 dark:text-slate-300">{{ $vehicle->marque }} {{ $vehicle->modele }}</span>
    </nav>

    {{-- ── En-tête de la fiche ──────────────────────────────────────────────── --}}
    <div class="mb-8">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="font-display text-display text-slate-900 dark:text-white mb-1">
                    {{ $vehicle->marque }}
                    <span class="italic text-astra">{{ $vehicle->modele }}</span>
                </h1>
                @if($vehicle->variante)
                    <p class="text-slate-500 dark:text-slate-400 text-sm">{{ $vehicle->variante }}</p>
                @endif
            </div>

            {{-- Badge numéro TG --}}
            <div class="flex-shrink-0">
                <div class="
                    px-4 py-2 rounded-xl
                    bg-white dark:bg-marine/40
                    border border-slate-200 dark:border-white/10
                    text-center
                ">
                    <div class="text-2xs text-slate-400 uppercase tracking-wider mb-0.5">
                        N° Réception par type
                    </div>
                    <div class="font-mono text-sm font-semibold text-slate-900 dark:text-white tracking-widest">
                        {{ $vehicle->numero_tg }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Indicateur de statut --}}
        <div class="flex items-center gap-3 mt-4">
            @if($vehicle->is_active)
                <span class="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Homologation active
                </span>
            @else
                <span class="flex items-center gap-1.5 text-xs text-slate-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    Homologation archivée
                </span>
            @endif
            <span class="text-slate-200 dark:text-white/10">|</span>
            <span class="text-xs text-slate-400">
                Mise à jour {{ $vehicle->imported_at?->diffForHumans() ?? 'inconnue' }}
            </span>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- GRILLE DE CARTES TECHNIQUES                                            --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- ── CARTE 1 : Motorisation (toujours visible) ───────────────────── --}}
        <x-vehicle-card title="Motorisation" icon="⚡" :public="true">
            <x-slot name="rows">
                <x-data-row label="{{ __('app.vehicle.energie') }}"
                             :value="$vehicle->energie ? App\Models\VehicleTranslation::translate('energie', $vehicle->energie, app()->getLocale()) : null" />
                <x-data-row label="{{ __('app.vehicle.puissance_kw') }}"
                             :value="$vehicle->puissance_kw ? $vehicle->puissance_kw . ' kW (' . $vehicle->puissance_cv . ' CV)' : null" />
                <x-data-row label="{{ __('app.vehicle.cylindree') }}"
                             :value="$vehicle->cylindree ? number_format($vehicle->cylindree, 0, '.', chr(39)) . ' cm³' : null" />
                <x-data-row label="{{ __('app.vehicle.boite_vitesse') }}"
                             :value="$vehicle->boite_vitesse ? App\Models\VehicleTranslation::translate('boite_vitesse', $vehicle->boite_vitesse, app()->getLocale()) : null" />
            </x-slot>
        </x-vehicle-card>

        {{-- ── CARTE 2 : Masses (visible niveau 1 partiel → blur si > 1) ────── --}}
        @if($canViewAdvanced)
            <x-vehicle-card title="Masses & Charges" icon="⚖️" :public="true">
                <x-slot name="rows">
                    <x-data-row label="{{ __('app.vehicle.poids_vide') }}"
                                 :value="$vehicle->poids_vide ? number_format($vehicle->poids_vide, 0, '.', chr(39)) . ' kg' : null" />
                    <x-data-row label="{{ __('app.vehicle.poids_total') }}"
                                 :value="$vehicle->poids_total ? number_format($vehicle->poids_total, 0, '.', chr(39)) . ' kg' : null" />
                    <x-data-row label="{{ __('app.vehicle.poids_remorquable') }}"
                                 :value="$vehicle->poids_remorquable ? number_format($vehicle->poids_remorquable, 0, '.', chr(39)) . ' kg' : null" />
                </x-slot>
            </x-vehicle-card>
        @else
            {{-- BLOC PAYANT : Glassmorphism blur — les données ne sont PAS dans le DOM --}}
            <x-locked-card
                title="Masses & Charges"
                icon="⚖️"
                :price="2"
                :vehicle-id="$vehicle->id"
            />
        @endif

        {{-- ── CARTE 3 : Émissions & Consommation (toujours visible) ────────── --}}
        <x-vehicle-card title="Émissions & Consommation" icon="🌿" :public="true">
            <x-slot name="rows">
                <x-data-row label="{{ __('app.vehicle.co2') }}"
                             :value="$vehicle->co2 !== null && $vehicle->co2 > 0
                                 ? $vehicle->co2 . ' g/km'
                                 : ($vehicle->co2 === 0 && $vehicle->isElectric() ? '0 g/km (ZEV)' : null)" />
                @if($vehicle->co2_wltp)
                    <x-data-row label="CO₂ WLTP"
                                 :value="$vehicle->co2_wltp . ' g/km'" />
                @endif
                <x-data-row label="{{ __('app.vehicle.code_emissions') }}"
                             :value="$vehicle->pollution_norm
                                 ?: ($vehicle->code_emissions
                                     ? (App\Models\VehicleTranslation::translate('code_emissions', $vehicle->code_emissions, app()->getLocale()) ?? $vehicle->code_emissions)
                                     : null)" />
                @if($vehicle->consommation_mixte)
                    <x-data-row label="Consommation NE"
                                 :value="number_format($vehicle->consommation_mixte, 2, '.', '') . ' l/100km'" />
                @endif
                @if($vehicle->consommation_wltp)
                    <x-data-row label="Consommation WLTP"
                                 :value="number_format($vehicle->consommation_wltp, 2, '.', '') . ' l/100km'" />
                @endif
                @if($vehicle->consommation_el)
                    <x-data-row label="Consommation élec."
                                 :value="number_format($vehicle->consommation_el, 2, '.', '') . ' kWh/100km'" />
                @endif
                @if($vehicle->autonomie_min || $vehicle->autonomie_max)
                    @php
                        $autonomie = $vehicle->autonomie_min && $vehicle->autonomie_max && $vehicle->autonomie_min !== $vehicle->autonomie_max
                            ? $vehicle->autonomie_min . ' – ' . $vehicle->autonomie_max . ' km'
                            : ($vehicle->autonomie_max ?? $vehicle->autonomie_min) . ' km';
                    @endphp
                    <x-data-row label="Autonomie électrique"
                                 :value="$autonomie" />
                @endif
                @if($vehicle->energie_label)
                    <x-data-row label="Label énergétique"
                                 :value="$vehicle->energie_label" />
                @endif
            </x-slot>
        </x-vehicle-card>

        {{-- ── CARTE 4 : Jantes & Pneumatiques (bloquée si niveau 1) ─────── --}}
        @if($canViewAdvanced)
            <x-vehicle-card title="Jantes & Pneumatiques" icon="🔩" :public="true">
                <x-slot name="rows">
                    <x-data-row label="{{ __('app.vehicle.nb_trous') }}"
                                 :value="$vehicle->nb_trous ? $vehicle->nb_trous . ' trous' : null" />
                    <x-data-row label="{{ __('app.vehicle.entraxe') }}"
                                 :value="$vehicle->entraxe ? $vehicle->entraxe . ' mm' : null" />
                    <x-data-row label="{{ __('app.vehicle.alesage') }}"
                                 :value="$vehicle->alesage ? $vehicle->alesage . ' mm' : null" />
                    <x-data-row label="{{ __('app.vehicle.deport_et') }}"
                                 :value="$vehicle->deport_et !== null ? 'ET ' . ($vehicle->deport_et >= 0 ? '+' : '') . $vehicle->deport_et : null" />
                    <x-data-row label="{{ __('app.vehicle.pneus_origine') }}"
                                 :value="$vehicle->pneus_origine" mono />
                </x-slot>
            </x-vehicle-card>
        @else
            <x-locked-card
                title="Jantes & Pneumatiques"
                icon="🔩"
                :price="2"
                :vehicle-id="$vehicle->id"
            />
        @endif
    </div>

    {{-- ── Partager la fiche ────────────────────────────────────────────────── --}}
    <div class="mt-8 flex items-center justify-end gap-3">
        <span class="text-xs text-slate-400">Partager :</span>
        <button
            onclick="navigator.clipboard.writeText(window.location.href).then(() => alert('URL copiée !'))"
            class="
                flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs
                bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400
                hover:bg-astra/10 hover:text-astra transition-colors duration-150
            "
        >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Copier le lien
        </button>
    </div>
</div>
@endsection
