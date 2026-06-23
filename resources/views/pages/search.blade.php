{{--
    Vue : resources/views/pages/search.blade.php
    Page de recherche publique (thème Nuit Alpine).
    Variables : $results (LengthAwarePaginator|null), $query (string), $queryType (string|null)
--}}
@extends('layouts.app')

@section('content')
<div class="pt-24 pb-16 max-w-5xl mx-auto px-4 sm:px-6">

    <h1 class="font-display text-2xl text-slate-900 dark:text-white mb-6">
        {{ __('app.search.title') }}
    </h1>

    {{-- Barre de recherche --}}
    <form method="POST" action="{{ route('search.results', ['locale' => app()->getLocale()]) }}" class="mb-8">
        @csrf
        <div class="flex gap-3">
            <input type="text" name="query" value="{{ $query ?? '' }}"
                   placeholder="{{ __('app.search.placeholder') }}"
                   class="flex-1 px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-marine/40 text-slate-900 dark:text-white focus:border-astra focus:outline-none"
                   autofocus>
            <button type="submit" class="px-6 py-3 rounded-xl bg-astra text-white font-semibold hover:bg-astra-600 transition-colors">
                {{ __('app.search.button') }}
            </button>
        </div>
    </form>

    {{-- Résultats --}}
    @if($results !== null)
        @if($results->count() > 0)
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                {{ $results->total() }} {{ __('app.search.results_count') }}
            </p>
            <div class="space-y-3">
                @foreach($results as $vehicle)
                <a href="{{ route('vehicle.show', ['locale' => app()->getLocale(), 'vehicle' => $vehicle->slug]) }}"
                   class="block bg-white dark:bg-marine/30 border border-slate-200 dark:border-white/5 rounded-2xl p-5 hover:border-astra/40 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $vehicle->marque }} {{ $vehicle->modele }}</span>
                            @if($vehicle->variante)<span class="text-slate-400 ml-1">{{ $vehicle->variante }}</span>@endif
                            <div class="text-xs text-slate-400 font-mono mt-1">{{ $vehicle->numero_tg }}</div>
                        </div>
                        <span class="text-astra text-sm">→</span>
                    </div>
                </a>
                @endforeach
            </div>
            <div class="mt-6">{{ $results->withQueryString()->links() }}</div>
        @else
            <div class="text-center py-16 text-slate-400">
                <p class="text-lg mb-2">{{ __('app.search.no_results') }}</p>
                <p class="text-sm">{{ __('app.search.no_results_hint') }}</p>
            </div>
        @endif
    @endif

</div>
@endsection
