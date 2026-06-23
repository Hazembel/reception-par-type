@extends('layouts.app')
@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4">
    <div class="text-center max-w-md">
        <p class="font-display text-6xl text-astra mb-4">400</p>
        <h1 class="text-xl font-semibold text-slate-900 dark:text-white mb-2">Requête invalide</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">{{ $message ?? 'La langue demandée n\'est pas valide.' }}</p>
        <a href="{{ url('/fr') }}" class="inline-block px-5 py-2.5 text-sm font-semibold bg-astra text-white rounded-xl hover:bg-astra-600 transition-colors">Retour à l'accueil</a>
    </div>
</div>
@endsection
