@extends('layouts.app')

@section('content')
<section class="min-h-[80vh] flex items-center justify-center py-24 px-4">
    <div class="max-w-lg w-full text-center">

        {{-- Animated checkmark --}}
        <div class="mx-auto mb-8 w-24 h-24 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
            <svg class="w-12 h-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="font-display font-bold text-3xl sm:text-4xl text-slate-900 dark:text-white mb-4">
            @lang('payment.success_title')
        </h1>

        <p class="text-lg text-slate-500 dark:text-slate-400 mb-6">
            @lang('payment.success_desc')
        </p>

        {{-- Activation notice --}}
        <div class="rounded-2xl border border-amber-200 dark:border-amber-700/50 bg-amber-50 dark:bg-amber-900/20 p-4 mb-8 text-sm text-amber-700 dark:text-amber-300 text-left">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>@lang('payment.success_pending')</span>
            </div>
        </div>

        @if($order_id)
        <p class="text-xs text-slate-400 dark:text-slate-600 mb-8">
            @lang('payment.order_ref') : <code class="font-mono">{{ $order_id }}</code>
        </p>
        @endif

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('account.profile.show', ['locale' => app()->getLocale()]) }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-astra text-white font-semibold hover:bg-astra/90 transition-colors">
                @lang('payment.goto_account')
            </a>
            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                @lang('payment.goto_home')
            </a>
        </div>

    </div>
</section>
@endsection
