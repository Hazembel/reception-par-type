@extends('layouts.app')
@section('content')
<div class="min-h-screen flex items-center justify-center py-16 px-4
            bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100
            dark:from-night dark:via-marine/20 dark:to-night">
    <div class="w-full max-w-md mx-auto">
        <div class="overflow-hidden rounded-[2rem] bg-white/95 dark:bg-marine-900/80
                    border border-slate-200/70 dark:border-white/10
                    shadow-[0_30px_80px_-40px_rgba(15,23,42,.35)]
                    ring-1 ring-slate-200/70 dark:ring-white/10
                    p-8 sm:p-10">

            <div class="flex flex-col items-center text-center space-y-2 mb-8">
                <div class="flex items-center justify-center w-11 h-11 rounded-full bg-blue-50 dark:bg-astra/10 mb-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-astra" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V8.844a2.25 2.25 0 011.183-1.98l7.5-4.04a2.25 2.25 0 012.134 0l7.5 4.04a2.25 2.25 0 011.183 1.98V19.5z"/>
                    </svg>
                </div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white">Mot de passe oublié</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs">
                    Entrez votre adresse e-mail pour recevoir un lien de réinitialisation.
                </p>
            </div>

            @if (session('status'))
                <div class="mb-5 px-3.5 py-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20
                            border border-emerald-200 dark:border-emerald-800/40
                            text-sm text-emerald-700 dark:text-emerald-400 text-center">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 px-3.5 py-2.5 rounded-lg bg-red-50 dark:bg-red-900/20
                            border border-red-200 dark:border-red-800/40
                            text-sm text-red-600 dark:text-red-400 text-center">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email', ['locale' => app()->getLocale()]) }}" novalidate>
                @csrf
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Adresse e-mail</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                            </svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                            placeholder="vous@exemple.ch"
                            class="block w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm font-medium
                                   text-slate-900 placeholder:text-slate-400
                                   dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder-slate-500
                                   focus:border-astra focus:outline-none focus:ring-2 focus:ring-astra/20 transition duration-150
                                   @error('email') border-red-400 @enderror">
                    </div>
                    @error('email')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700
                           text-white text-sm font-semibold tracking-wide
                           transition-all duration-150 shadow-md hover:shadow-lg">
                    Envoyer le lien de réinitialisation
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400">
                <a href="{{ route('login', ['locale' => app()->getLocale()]) }}" class="font-medium text-blue-600 dark:text-astra hover:underline">Retour à la connexion</a>
            </p>
        </div>
    </div>
</div>
@endsection
