{{--
    Vue : resources/views/account/invoices/index.blade.php
    Liste des factures de l'utilisateur connecté (espace Mon Compte).
--}}
@extends('layouts.app')

@section('content')
<div class="pt-20 pb-16 max-w-4xl mx-auto px-4 sm:px-6">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="font-display text-2xl text-slate-900 dark:text-white">Mes factures</h1>
            <p class="text-xs text-slate-400 mt-1">Historique de vos achats et téléchargement des factures PDF.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-marine/30 border border-slate-200 dark:border-white/5 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-white/[0.02] text-xs text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-left">N° de facture</th>
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-right">Montant</th>
                    <th class="px-5 py-3 text-left">Statut</th>
                    <th class="px-5 py-3 text-right">PDF</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04]">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-5 py-3 font-mono text-xs text-slate-700 dark:text-slate-200">{{ $invoice->invoice_number }}</td>
                    <td class="px-5 py-3 text-slate-500 text-xs">{{ $invoice->issued_at->format('d.m.Y') }}</td>
                    <td class="px-5 py-3 text-right font-mono font-semibold text-slate-900 dark:text-white">
                        CHF {{ number_format($invoice->total_cts / 100, 2, '.', '\'') }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-2xs px-2 py-0.5 rounded-full font-medium
                            {{ $invoice->status === 'paid' ? 'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400' :
                               ($invoice->status === 'sent' ? 'bg-blue-100 dark:bg-blue-500/15 text-blue-700 dark:text-blue-400' :
                               ($invoice->status === 'cancelled' ? 'bg-red-100 dark:bg-red-500/15 text-red-600' :
                                'bg-slate-100 dark:bg-white/10 text-slate-500')) }}">
                            {{ match($invoice->status) { 'paid' => 'Payée', 'sent' => 'Envoyée', 'cancelled' => 'Annulée', default => 'Brouillon' } }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        @if($invoice->pdf_path)
                        <a href="{{ route('account.invoices.download', ['locale' => app()->getLocale(), 'invoice' => $invoice]) }}"
                           class="text-xs text-astra hover:underline font-medium">
                            Télécharger ↓
                        </a>
                        @else
                        <span class="text-2xs text-slate-400">En préparation</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 text-sm">Aucune facture pour le moment.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if($invoices->hasPages())
        <div class="px-5 py-3 border-t border-slate-100 dark:border-white/5">
            {{ $invoices->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
