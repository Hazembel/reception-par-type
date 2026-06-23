{{--
    Vue : resources/views/account/affiliate/join.blade.php
    Formulaire d'adhésion au programme d'affiliation (thème Nuit Alpine).
--}}
@extends('layouts.app')

@section('content')
<div class="pt-24 pb-16 max-w-2xl mx-auto px-4 sm:px-6">

    <div class="text-center mb-8">
        <h1 class="font-display text-2xl text-slate-900 dark:text-white mb-2">
            Programme <span class="text-astra">Affilié</span>
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Gagnez une commission sur chaque abonnement souscrit via votre lien de parrainage.
        </p>
    </div>

    {{-- Avantages --}}
    <div class="grid grid-cols-3 gap-4 mb-8">
        @foreach([
            ['10%', 'de commission sur chaque vente'],
            ['30 jours', 'de durée du cookie de suivi'],
            ['CHF', 'versés par virement bancaire'],
        ] as $perk)
        <div class="bg-white dark:bg-marine/30 border border-slate-200 dark:border-white/5 rounded-2xl p-5 text-center">
            <div class="font-display text-xl text-astra">{{ $perk[0] }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ $perk[1] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Formulaire --}}
    <div class="bg-white dark:bg-marine/30 border border-slate-200 dark:border-white/5 rounded-2xl p-6">
        <form method="POST" action="{{ route('account.affiliate.join', ['locale' => app()->getLocale()]) }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">
                    Code d'affiliation souhaité
                    <span class="text-slate-400 font-normal">(majuscules, chiffres, tirets)</span>
                </label>
                <input type="text" name="code" value="{{ old('code') }}" required
                       placeholder="GARAGE10"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-900 dark:text-white font-mono uppercase focus:border-astra focus:outline-none">
                @error('code')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">
                    Nom du bénéficiaire
                </label>
                <input type="text" name="beneficiary_name" value="{{ old('beneficiary_name') }}" required
                       placeholder="Garage Martin Sàrl"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-900 dark:text-white focus:border-astra focus:outline-none">
                @error('beneficiary_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1.5">
                    IBAN <span class="text-slate-400 font-normal">(pour le versement des commissions)</span>
                </label>
                <input type="text" name="iban" value="{{ old('iban') }}" required
                       placeholder="CH93 0076 2011 6238 5295 7"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 text-slate-900 dark:text-white font-mono focus:border-astra focus:outline-none">
                @error('iban')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                <p class="text-xs text-slate-400 mt-1">Votre IBAN est chiffré (AES-256) avant stockage.</p>
            </div>

            <button type="submit" class="w-full px-6 py-3 rounded-xl bg-astra text-white font-semibold hover:bg-astra-600 transition-colors">
                Rejoindre le programme
            </button>
            <p class="text-xs text-slate-400 text-center mt-3">
                Votre demande sera examinée sous 48 h avant activation.
            </p>
        </form>
    </div>

</div>
@endsection
