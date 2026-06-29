@extends('layouts.app')

@section('content')
<div class="pt-20 pb-16 max-w-2xl mx-auto px-4 sm:px-6">

    <div class="mb-8">
        <h1 class="font-display text-2xl text-slate-900 dark:text-white">Mon profil</h1>
        <p class="text-xs text-slate-400 mt-1">Modifiez vos informations personnelles et votre mot de passe.</p>
    </div>

    {{-- Informations générales --}}
    <div class="bg-white dark:bg-marine/30 border border-slate-200 dark:border-white/5 rounded-2xl p-6 mb-6">
        <h2 class="text-sm font-semibold text-slate-800 dark:text-white mb-5">Informations du compte</h2>

        @if(session('success'))
            <div class="mb-4 text-sm text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-xl px-4 py-3">
                {{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div class="mb-4 text-sm text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-xl px-4 py-3">
                {{ session('info') }}
            </div>
        @endif

        <form method="POST" action="{{ route('account.profile.update', ['locale' => app()->getLocale()]) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Nom complet</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                    class="w-full rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40
                    @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Adresse e-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                    class="w-full rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40
                    @error('email') border-red-400 @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                @if(!$user->hasVerifiedEmail())
                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Adresse non vérifiée.</p>
                @endif
            </div>

            <div class="pt-1">
                <button type="submit"
                    class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 transition-colors">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>

    {{-- Changement de mot de passe --}}
    <div class="bg-white dark:bg-marine/30 border border-slate-200 dark:border-white/5 rounded-2xl p-6">
        <h2 class="text-sm font-semibold text-slate-800 dark:text-white mb-5">Changer le mot de passe</h2>

        @if(session('success_password'))
            <div class="mb-4 text-sm text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-xl px-4 py-3">
                {{ session('success_password') }}
            </div>
        @endif

        <form method="POST" action="{{ route('account.profile.password', ['locale' => app()->getLocale()]) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Mot de passe actuel</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                    class="w-full rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40
                    @error('current_password') border-red-400 @enderror">
                @error('current_password')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Nouveau mot de passe</label>
                <input id="password" name="password" type="password" autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40
                    @error('password') border-red-400 @enderror">
                @error('password')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-slate-400">Minimum 8 caractères, majuscule + chiffre requis.</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Confirmer le nouveau mot de passe</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40">
            </div>

            <div class="pt-1">
                <button type="submit"
                    class="rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-white/10 dark:hover:bg-white/20 text-white text-sm font-medium px-5 py-2.5 transition-colors">
                    Changer le mot de passe
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
