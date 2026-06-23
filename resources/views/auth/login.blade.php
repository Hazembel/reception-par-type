@extends('layouts.app')

@section('content')
{{-- Page wrapper: full-height, soft gradient, centers the card --}}
<div class="min-h-screen flex flex-col items-center justify-center pt-16 pb-12
            bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100
            dark:from-night dark:via-marine/20 dark:to-night">

    {{-- Card container: max-w-sm = 384px, centered, side padding for mobile --}}
    <div class="w-full max-w-sm px-4">
        <div class="bg-white dark:bg-marine/40
                    rounded-2xl shadow-xl
                    border border-slate-200/60 dark:border-white/10
                    p-8">

            {{-- ── Heading ──────────────────────────────────────────────────── --}}
            <div class="flex flex-col items-center mb-7">
                <div class="flex items-center justify-center w-11 h-11 rounded-full bg-blue-50 dark:bg-astra/10 mb-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-astra" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h1 class="text-lg font-semibold text-slate-900 dark:text-white">
                    {{ __('app.nav.login') }}
                </h1>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    réception<span class="text-blue-600 dark:text-astra font-medium">-par-type</span>.ch
                </p>
            </div>

            {{-- ── Error banner ─────────────────────────────────────────────── --}}
            @if ($errors->any())
                <div class="mb-5 px-3.5 py-2.5 rounded-lg
                            bg-red-50 dark:bg-red-900/20
                            border border-red-200 dark:border-red-800/40
                            text-sm text-red-600 dark:text-red-400 text-center">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- ── Form ────────────────────────────────────────────────────── --}}
            <form method="POST" action="{{ route('login', ['locale' => app()->getLocale()]) }}" novalidate>
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ __('app.auth.email') }}
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                            </svg>
                        </span>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            placeholder="vous@exemple.ch"
                            class="block w-full rounded-lg border py-2.5 pl-10 pr-4 text-sm
                                   border-gray-300 bg-white text-slate-900 placeholder-gray-400
                                   dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder-slate-500
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                   dark:focus:ring-astra dark:focus:border-astra
                                   transition-colors duration-150
                                   @error('email') border-red-400 focus:ring-red-400 focus:border-red-400 @enderror"
                        >
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-5" x-data="{ show: false }">
                    <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ __('app.auth.password') }}
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                            </svg>
                        </span>
                        <input
                            id="password"
                            x-bind:type="show ? 'text' : 'password'"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="block w-full rounded-lg border py-2.5 pl-10 pr-10 text-sm
                                   border-gray-300 bg-white text-slate-900 placeholder-gray-400
                                   dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder-slate-500
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                   dark:focus:ring-astra dark:focus:border-astra
                                   transition-colors duration-150"
                        >
                        <button
                            type="button"
                            x-on:click="show = !show"
                            x-bind:aria-label="show ? 'Masquer' : 'Afficher'"
                            class="absolute inset-y-0 right-0 flex items-center pr-3
                                   text-gray-400 hover:text-slate-600 dark:hover:text-slate-300
                                   transition-colors duration-150"
                        >
                            <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <svg x-show="show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center mb-6">
                    <input
                        id="remember"
                        type="checkbox"
                        name="remember"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600
                               focus:ring-2 focus:ring-blue-500
                               dark:border-white/20 dark:bg-white/5"
                    >
                    <label for="remember" class="ml-2 text-sm text-slate-600 dark:text-slate-400 cursor-pointer select-none">
                        {{ __('app.nav.remember_me') }}
                    </label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="flex w-full items-center justify-center
                           rounded-lg py-2.5 px-4
                           bg-blue-600 hover:bg-blue-700 active:bg-blue-800
                           dark:bg-astra dark:hover:bg-blue-600
                           text-white text-sm font-semibold
                           shadow-sm shadow-blue-500/20 hover:shadow-md hover:shadow-blue-500/30
                           transition-all duration-200
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                           dark:focus:ring-offset-marine"
                >
                    {{ __('app.nav.login') }}
                </button>
            </form>

        </div>
    </div>
</div>
@endsection
