{{--
    Page FAQ : resources/views/pages/faq.blade.php
    Route : /{locale}/faq

    Fonctionnalités :
    - 4 catégories filtrables via Alpine.js
    - Accordéon animé (height transition via Alpine x-collapse)
    - Recherche temps réel dans les questions
    - JSON-LD FAQPage (Schema.org) pour Rich Snippets Google
--}}
@extends('layouts.app')

@push('head')
{{-- ══ JSON-LD : FAQPage Schema.org (Rich Snippets Google) ══════════════════ --}}
{{--
    Ce script permet à Google d'afficher les Q/R directement dans les SERP
    sous forme de "Featured Snippets" → augmentation du CTR de 20-30%.
    Spécification : https://schema.org/FAQPage
--}}
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "name": "{{ __('help.faq.page_title') }} — reception-par-type.ch",
    "url": "{{ url()->current() }}",
    "inLanguage": "{{ app()->getLocale() }}",
    "mainEntity": [
        @foreach(__('help.faq.questions') as $i => $faq)
        {
            "@type": "Question",
            "name": {{ json_encode($faq['question'], JSON_UNESCAPED_UNICODE) }},
            "acceptedAnswer": {
                "@type": "Answer",
                "text": {{ json_encode(strip_tags($faq['answer']), JSON_UNESCAPED_UNICODE) }}
            }
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ]
}
</script>
@endpush

@section('content')
<div
    class="pt-20 pb-24 max-w-4xl mx-auto px-4 sm:px-6"
    x-data="faqManager()"
    x-init="init()"
>

    {{-- ── En-tête ──────────────────────────────────────────────────────────── --}}
    <div class="text-center mb-12">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium
                    bg-astra/10 text-astra border border-astra/20 mb-4">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            FAQ
        </div>
        <h1 class="font-display text-display text-slate-900 dark:text-white mb-3">
            {{ __('help.faq.page_title') }}
        </h1>
        <p class="text-slate-500 dark:text-slate-400 max-w-lg mx-auto text-sm leading-relaxed">
            {{ __('help.faq.page_description') }}
        </p>
    </div>

    {{-- ── Barre de recherche ───────────────────────────────────────────────── --}}
    <div class="relative mb-8">
        <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
            </svg>
        </div>
        <input
            type="search"
            x-model.debounce.300="searchQuery"
            placeholder="{{ __('help.faq.search_placeholder') }}"
            class="
                w-full pl-11 pr-4 py-3 rounded-xl
                bg-white dark:bg-marine/40
                border border-slate-200 dark:border-white/10
                text-slate-900 dark:text-white text-sm
                placeholder:text-slate-400
                focus:outline-none focus:border-astra dark:focus:border-astra
                focus:ring-2 focus:ring-astra/10
                transition-all duration-200
            "
        />
        {{-- Clear --}}
        <button
            x-show="searchQuery.length > 0"
            x-on:click="searchQuery = ''"
            class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
            aria-label="Effacer la recherche"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ── Filtres par catégorie ─────────────────────────────────────────────── --}}
    <div
        class="flex flex-wrap gap-2 mb-8"
        x-show="searchQuery.length === 0"
        role="tablist"
        aria-label="Catégories FAQ"
    >
        {{-- Bouton "Toutes" --}}
        <button
            role="tab"
            x-on:click="activeCategory = 'all'"
            x-bind:aria-selected="activeCategory === 'all'"
            x-bind:class="{
                'bg-astra text-white border-astra shadow-sm shadow-astra/20': activeCategory === 'all',
                'bg-white dark:bg-marine/30 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-white/10 hover:border-astra/30': activeCategory !== 'all'
            }"
            class="px-4 py-1.5 rounded-lg text-xs font-medium border transition-all duration-150"
        >
            Toutes
        </button>

        @foreach(['general', 'wheels', 'pricing', 'account'] as $cat)
            <button
                role="tab"
                x-on:click="activeCategory = '{{ $cat }}'"
                x-bind:aria-selected="activeCategory === '{{ $cat }}'"
                x-bind:class="{
                    'bg-astra text-white border-astra shadow-sm shadow-astra/20': activeCategory === '{{ $cat }}',
                    'bg-white dark:bg-marine/30 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-white/10 hover:border-astra/30': activeCategory !== '{{ $cat }}'
                }"
                class="px-4 py-1.5 rounded-lg text-xs font-medium border transition-all duration-150"
            >
                {{ __("help.faq.categories.{$cat}") }}
            </button>
        @endforeach
    </div>

    {{-- ── Liste des questions (accordéon) ─────────────────────────────────── --}}
    <div
        class="space-y-2"
        role="list"
        aria-label="{{ __('help.faq.page_title') }}"
    >
        @foreach(__('help.faq.questions') as $i => $faq)
            <div
                x-show="isVisible('{{ $faq['cat'] }}', {{ json_encode($faq['question']) }}, {{ json_encode(strip_tags($faq['answer'])) }})"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-data="{ open: false }"
                class="
                    bg-white dark:bg-marine/30
                    border border-slate-200 dark:border-white/5
                    rounded-xl overflow-hidden
                    transition-all duration-200
                "
                x-bind:class="{ 'border-astra/30 dark:border-astra/20': open }"
                role="listitem"
            >
                {{-- Question (bouton accordéon) --}}
                <button
                    type="button"
                    x-on:click="open = !open"
                    x-bind:aria-expanded="open"
                    class="
                        w-full flex items-center justify-between gap-4
                        px-5 py-4 text-left
                        hover:bg-slate-50 dark:hover:bg-white/[0.02]
                        transition-colors duration-150
                        focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-astra/40 focus-visible:ring-inset
                    "
                >
                    <span class="text-sm font-medium text-slate-900 dark:text-white leading-snug pr-2">
                        {{-- Highlight du terme recherché --}}
                        <span x-html="highlight({{ json_encode($faq['question']) }})"></span>
                    </span>

                    {{-- Icône +/- animée --}}
                    <span class="
                        flex-shrink-0 w-6 h-6 rounded-full
                        flex items-center justify-center
                        bg-slate-100 dark:bg-white/10
                        text-slate-500 dark:text-slate-400
                        transition-all duration-200
                    "
                    x-bind:class="{ 'bg-astra/10 dark:bg-astra/20 text-astra rotate-45': open }"
                    >
                        <svg class="w-3.5 h-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </span>
                </button>

                {{-- Réponse (collapse animé via Alpine x-collapse) --}}
                <div
                    x-show="open"
                    x-collapse
                    class="px-5 pb-5"
                >
                    <div class="
                        text-sm text-slate-600 dark:text-slate-300 leading-relaxed
                        border-t border-slate-100 dark:border-white/5 pt-4
                        [&_strong]:font-semibold [&_strong]:text-slate-900 [&_strong]:dark:text-white
                        [&_em]:text-slate-500 [&_em]:italic
                        [&_code]:font-mono [&_code]:text-xs [&_code]:bg-slate-100 [&_code]:dark:bg-white/10 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded
                        [&_ul]:mt-2 [&_ul]:space-y-1.5 [&_ul]:pl-0
                        [&_li]:flex [&_li]:gap-2 [&_li]:before:content-['→'] [&_li]:before:text-astra [&_li]:before:flex-shrink-0 [&_li]:before:mt-0.5
                        [&_p]:mb-0
                    ">
                        {!! $faq['answer'] !!}
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Message "aucun résultat" --}}
        <div
            x-show="visibleCount === 0"
            class="text-center py-12 text-slate-400 dark:text-slate-500"
        >
            <svg class="w-10 h-10 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm">{{ __('help.faq.no_results') }}</p>
        </div>
    </div>

    {{-- ── CTA Support ──────────────────────────────────────────────────────── --}}
    <div class="mt-12 text-center p-8 rounded-2xl bg-gradient-to-br from-astra/5 to-spark/5 dark:from-astra/10 dark:to-spark/10 border border-astra/10 dark:border-astra/15">
        <p class="text-sm text-slate-600 dark:text-slate-300 mb-4">
            Vous ne trouvez pas votre réponse ?
        </p>
        <a href="mailto:support@reception-par-type.ch"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-astra hover:bg-astra-600 text-white text-sm font-semibold transition-all duration-200 shadow-sm shadow-astra/20">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Contacter le support
        </a>
    </div>
</div>

@push('scripts')
<script>
function faqManager() {
    return {
        activeCategory: 'all',
        searchQuery:    '',
        visibleCount:   {{ count(__('help.faq.questions')) }},

        init() {
            this.$watch('searchQuery',    () => this.updateCount());
            this.$watch('activeCategory', () => this.updateCount());
        },

        /**
         * Détermine si une question doit être affichée selon la catégorie + recherche.
         */
        isVisible(cat, question, answer) {
            const matchesCat = this.activeCategory === 'all' || this.activeCategory === cat;

            if (!matchesCat) return false;

            if (this.searchQuery.trim().length < 2) return true;

            const q   = (question + ' ' + answer).toLowerCase();
            const kws = this.searchQuery.toLowerCase().trim().split(/\s+/);
            return kws.every(kw => q.includes(kw));
        },

        /**
         * Met à jour le compteur de questions visibles.
         * Utilisé pour afficher le message "aucun résultat".
         */
        updateCount() {
            const questions = @json(__('help.faq.questions'));
            this.visibleCount = questions.filter(q =>
                this.isVisible(q.cat, q.question, q.answer.replace(/<[^>]+>/g, ''))
            ).length;
        },

        /**
         * Surligne le terme recherché dans le texte d'une question.
         */
        highlight(text) {
            if (this.searchQuery.trim().length < 2) return text;

            const escaped = this.searchQuery.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex   = new RegExp(`(${escaped})`, 'gi');

            return text.replace(regex,
                '<mark class="bg-spark/30 dark:bg-spark/20 text-inherit rounded-sm px-0.5 not-italic">$1</mark>'
            );
        }
    };
}
</script>
@endpush
@endsection
