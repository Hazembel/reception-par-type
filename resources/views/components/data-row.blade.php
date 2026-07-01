{{--
    Composant : <x-data-row>
    resources/views/components/data-row.blade.php

    Usage :
      <x-data-row label="Puissance" value="150 kW (204 CV)" />
      <x-data-row label="Pneus" value="205/55 R16 91H" mono />
      <x-data-row label="Poids à vide" :value="null" />   ← Affiche "—" si null
--}}
@props([
    'label' => '',
    'value' => null,
    'mono'  => false,
])
@php $hasValue = $value !== null && $value !== ''; @endphp

<div class="flex items-center justify-between px-5 py-3 gap-4">
    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 shrink-0">
        {!! $label !!}
    </dt>
    <dd class="text-sm text-right {{ $hasValue ? 'text-slate-900 dark:text-white font-medium' : 'text-slate-300 dark:text-slate-600' }} {{ $mono ? 'font-mono text-xs tracking-wide' : '' }}">
        {{ $hasValue ? $value : '—' }}
    </dd>
</div>
