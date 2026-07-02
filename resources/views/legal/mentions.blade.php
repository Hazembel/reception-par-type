@extends('layouts.app')
@section('content')
<div class="pt-20 pb-16 max-w-3xl mx-auto px-4 sm:px-6">
    <h1 class="font-display text-2xl text-slate-900 dark:text-white mb-8">{{ $meta_title ?? __('app.legal.mentions_title') }}</h1>
    <div class="prose dark:prose-invert text-slate-600 dark:text-slate-300 text-sm space-y-8">
        @include('legal.partials.mentions_' . app()->getLocale())
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-8">{{ __('app.legal.last_updated') }} {{ now()->format('d.m.Y') }}</p>
    </div>
</div>
@endsection
