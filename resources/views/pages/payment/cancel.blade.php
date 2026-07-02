@extends('layouts.app')

@section('content')
<section class="min-h-[80vh] flex items-center justify-center py-24 px-4">
    <div class="max-w-lg w-full text-center">

        <div class="mx-auto mb-8 w-24 h-24 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
            <svg class="w-12 h-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>

        <h1 class="font-display font-bold text-3xl sm:text-4xl text-slate-900 dark:text-white mb-4">
            @lang('payment.cancel_title')
        </h1>

        <p class="text-lg text-slate-500 dark:text-slate-400 mb-10">
            @lang('payment.cancel_desc')
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('pricing', ['locale' => app()->getLocale()]) }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-astra text-white font-semibold hover:bg-astra/90 transition-colors">
                @lang('payment.back_to_pricing')
            </a>
            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                @lang('payment.goto_home')
            </a>
        </div>

    </div>
</section>
@endsection
