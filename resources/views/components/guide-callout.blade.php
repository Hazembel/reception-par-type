{{--
    Composant : <x-guide-callout>
    resources/views/components/guide-callout.blade.php

    Bloc d'information coloré pour le guide (astuce, note, avertissement).

    Usage :
      <x-guide-callout type="tip">Votre texte d'astuce ici.</x-guide-callout>
      <x-guide-callout type="info">Note informative.</x-guide-callout>
      <x-guide-callout type="warning">Avertissement important.</x-guide-callout>
--}}
@props(['type' => 'tip'])

@php
    $config = match($type) {
        'tip'     => [
            'bg'     => 'bg-astra/5 dark:bg-astra/8 border-astra/20 dark:border-astra/15',
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>',
            'color'  => 'text-astra',
            'prefix' => 'Astuce',
        ],
        'warning' => [
            'bg'     => 'bg-amber-50 dark:bg-amber-500/8 border-amber-200 dark:border-amber-500/20',
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
            'color'  => 'text-amber-600 dark:text-amber-400',
            'prefix' => 'Important',
        ],
        default   => [
            'bg'     => 'bg-slate-50 dark:bg-white/[0.03] border-slate-200 dark:border-white/10',
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'color'  => 'text-slate-500 dark:text-slate-400',
            'prefix' => 'Note',
        ],
    };
@endphp

<div class="flex gap-3 rounded-xl border px-4 py-3 {{ $config['bg'] }} {{ $attributes->get('class') }}">
    <svg class="w-4 h-4 flex-shrink-0 mt-0.5 {{ $config['color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        {!! $config['icon'] !!}
    </svg>
    <p class="text-xs leading-relaxed text-slate-600 dark:text-slate-300">
        <strong class="font-semibold {{ $config['color'] }}">{{ $config['prefix'] }} :</strong>
        {{ $slot }}
    </p>
</div>
