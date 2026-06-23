{{--
    Composant : <x-vehicle-disclaimer>
    resources/views/components/vehicle-disclaimer.blade.php

    Clause de non-responsabilité juridique + bouton "Signaler une anomalie"
    Affiché en bas de chaque fiche technique véhicule.

    Usage :
      <x-vehicle-disclaimer :vehicle="$vehicle" />
--}}
@props(['vehicle'])

<div
    class="mt-10"
    x-data="anomalyModal()"
>
    {{-- ── Disclaimer juridique ─────────────────────────────────────────────── --}}
    <div class="
        flex items-start gap-3 p-4 rounded-xl
        bg-slate-50 dark:bg-white/[0.03]
        border border-slate-200 dark:border-white/5
        text-xs text-slate-400 dark:text-slate-500
        leading-relaxed
    ">
        {{-- Icône info --}}
        <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-slate-300 dark:text-slate-600"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
        </svg>

        <div class="flex-1">
            {{-- Traduction automatique selon la locale active --}}
            <p>
                @switch(app()->getLocale())
                    @case('de')
                        Hinweisdaten basierend auf den offiziellen TARGA-Dateien des ASTRA (ASTRA/OFROU).
                        Keine Haftung für Fehler, Druckfehler oder Schäden durch ungeeignete Teilebestellungen.
                        Bitte konsultieren Sie immer einen Fachmann für technische Entscheidungen.
                        @break
                    @case('it')
                        Dati indicativi basati sui file TARGA ufficiali dell'ASTRA (OFROU).
                        Nessuna responsabilità legale o finanziaria per errori, imprecisioni o danni derivanti
                        dall'ordinazione di parti incompatibili. Consultare sempre un professionista per decisioni tecniche.
                        @break
                    @case('en')
                        Indicative data based on the official ASTRA (OFROU) TARGA files.
                        No legal or financial liability is accepted for errors, inaccuracies, or damage resulting
                        from ordering incompatible parts. Always consult a qualified professional for technical decisions.
                        @break
                    @default {{-- fr --}}
                        Données indicatives basées sur les fichiers officiels TARGA de l'OFROU (ASTRA).
                        Aucune responsabilité juridique ou financière n'est engagée en cas d'erreur, d'imprécision
                        ou de dommage résultant de la commande de pièces incompatibles.
                        Consultez toujours un professionnel qualifié pour toute décision technique.
                @endswitch
            </p>

            {{-- Source et date ─────────────────────────────────────────────── --}}
            <div class="flex items-center gap-3 mt-2 flex-wrap">
                <span class="inline-flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('app.vehicle.imported_at') }} : {{ $vehicle->imported_at?->format('d.m.Y') ?? '—' }}
                </span>
                <span>·</span>
                <a href="https://www.astra.admin.ch" target="_blank" rel="noopener noreferrer"
                   class="hover:text-astra transition-colors duration-150 underline underline-offset-2 decoration-dotted">
                    Source : OFROU/ASTRA
                </a>
                <span>·</span>
                {{-- Bouton "Signaler une anomalie" ────────────────────────── --}}
                <button
                    type="button"
                    x-on:click="openModal()"
                    class="
                        inline-flex items-center gap-1.5
                        text-slate-400 dark:text-slate-500
                        hover:text-amber-500 dark:hover:text-amber-400
                        transition-colors duration-150
                        underline underline-offset-2 decoration-dotted
                    "
                >
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    @switch(app()->getLocale())
                        @case('de') Fehler melden @break
                        @case('it') Segnala errore @break
                        @case('en') Report an error @break
                        @default Signaler une anomalie
                    @endswitch
                </button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- MODAL "Signaler une anomalie"                                          --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}

    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click="closeModal()"
        class="fixed inset-0 z-40 bg-black/50 dark:bg-black/70 backdrop-blur-sm"
        style="display:none;"
        aria-hidden="true"
    ></div>

    {{-- Fenêtre modale --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-title"
        x-trap.noscroll="open"
        class="
            fixed inset-0 z-50 flex items-center justify-center p-4
            pointer-events-none
        "
        style="display:none;"
    >
        <div class="
            pointer-events-auto w-full max-w-md
            bg-white dark:bg-marine
            rounded-2xl shadow-2xl shadow-black/20 dark:shadow-black/50
            border border-slate-200 dark:border-white/10
            overflow-hidden
        ">
            {{-- En-tête modal --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-white/5">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-500/15 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <h2 id="modal-title" class="font-semibold text-sm text-slate-900 dark:text-white">
                        @switch(app()->getLocale())
                            @case('de') Fehler melden @break
                            @case('it') Segnala un'anomalia @break
                            @case('en') Report an Anomaly @break
                            @default Signaler une anomalie
                        @endswitch
                    </h2>
                </div>
                <button
                    type="button"
                    x-on:click="closeModal()"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition-colors duration-150"
                    aria-label="Fermer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Corps du formulaire --}}
            <form
                x-on:submit.prevent="submitReport()"
                class="px-6 py-5 space-y-4"
                novalidate
            >
                @csrf

                {{-- Champ honeypot anti-spam (caché en CSS, jamais en display:none) --}}
                <div class="absolute -left-[9999px] top-0" aria-hidden="true">
                    <input type="text" name="website" tabindex="-1" autocomplete="off" />
                </div>

                {{-- TG affiché en lecture seule --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1.5">
                        N° Réception par type
                    </label>
                    <div class="flex items-center gap-2 px-3 py-2.5 rounded-lg bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <code class="text-xs font-mono font-semibold text-slate-700 dark:text-slate-200">
                            {{ $vehicle->numero_tg }}
                        </code>
                    </div>
                    <input type="hidden" name="numero_tg" value="{{ $vehicle->numero_tg }}"/>
                    <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}"/>
                </div>

                {{-- Champ concerné --}}
                <div>
                    <label for="field_reported" class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1.5">
                        @switch(app()->getLocale())
                            @case('de') Betroffenes Feld @break
                            @case('it') Campo interessato @break
                            @case('en') Affected field @break
                            @default Champ concerné
                        @endswitch
                        <span class="text-slate-400">(optionnel)</span>
                    </label>
                    <select
                        id="field_reported"
                        name="field_reported"
                        x-model="form.field"
                        class="
                            w-full px-3 py-2.5 rounded-lg text-sm
                            bg-white dark:bg-white/5
                            border border-slate-200 dark:border-white/10
                            text-slate-900 dark:text-white
                            focus:outline-none focus:border-astra dark:focus:border-astra
                            transition-colors duration-150
                        "
                    >
                        <option value="">— Sélectionner —</option>
                        @foreach([
                            'marque'            => __('app.vehicle.marque'),
                            'modele'            => __('app.vehicle.modele'),
                            'energie'           => __('app.vehicle.energie'),
                            'puissance_kw'      => __('app.vehicle.puissance_kw'),
                            'cylindree'         => __('app.vehicle.cylindree'),
                            'poids_vide'        => __('app.vehicle.poids_vide'),
                            'poids_total'       => __('app.vehicle.poids_total'),
                            'poids_remorquable' => __('app.vehicle.poids_remorquable'),
                            'co2'               => __('app.vehicle.co2'),
                            'code_emissions'    => __('app.vehicle.code_emissions'),
                            'entraxe'           => __('app.vehicle.entraxe'),
                            'alesage'           => __('app.vehicle.alesage'),
                            'deport_et'         => __('app.vehicle.deport_et'),
                            'pneus_origine'     => __('app.vehicle.pneus_origine'),
                            'autre'             => 'Autre / Sonstiges / Altro / Other',
                        ] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1.5">
                        @switch(app()->getLocale())
                            @case('de') Beschreibung des Fehlers @break
                            @case('it') Descrizione del problema @break
                            @case('en') Error description @break
                            @default Description de l'anomalie
                        @endswitch
                        <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        x-model="form.description"
                        rows="3"
                        maxlength="1000"
                        placeholder="{{ match(app()->getLocale()) {
                            'de' => 'Bitte beschreiben Sie den Fehler genau...',
                            'it' => 'Descrivete l\'anomalia nel dettaglio...',
                            'en' => 'Please describe the issue in detail...',
                            default => 'Décrivez l\'anomalie avec précision (valeur correcte si connue)...',
                        } }}"
                        class="
                            w-full px-3 py-2.5 rounded-lg text-sm resize-none
                            bg-white dark:bg-white/5
                            border border-slate-200 dark:border-white/10
                            text-slate-900 dark:text-white
                            placeholder:text-slate-400
                            focus:outline-none focus:border-astra dark:focus:border-astra
                            transition-colors duration-150
                        "
                    ></textarea>
                    <p class="text-right text-2xs text-slate-400 mt-1">
                        <span x-text="form.description.length"></span>/1000
                    </p>
                </div>

                {{-- E-mail (si non connecté) --}}
                @guest
                <div>
                    <label for="reporter_email" class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1.5">
                        E-mail de contact
                        <span class="text-slate-400">(optionnel)</span>
                    </label>
                    <input
                        type="email"
                        id="reporter_email"
                        name="reporter_email"
                        x-model="form.email"
                        placeholder="vous@exemple.ch"
                        class="
                            w-full px-3 py-2.5 rounded-lg text-sm
                            bg-white dark:bg-white/5
                            border border-slate-200 dark:border-white/10
                            text-slate-900 dark:text-white
                            placeholder:text-slate-400
                            focus:outline-none focus:border-astra dark:focus:border-astra
                            transition-colors duration-150
                        "
                    />
                </div>
                @endguest

                {{-- Message d'état --}}
                <div x-show="statusMessage" class="text-xs rounded-lg px-3 py-2"
                     x-bind:class="{
                         'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-500/20': statusType === 'success',
                         'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20': statusType === 'error'
                     }">
                    <span x-text="statusMessage"></span>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-1">
                    <button
                        type="button"
                        x-on:click="closeModal()"
                        class="
                            px-4 py-2 rounded-lg text-sm text-slate-600 dark:text-slate-400
                            hover:bg-slate-100 dark:hover:bg-white/10
                            transition-colors duration-150
                        "
                    >
                        Annuler
                    </button>
                    <button
                        type="submit"
                        x-bind:disabled="submitting || form.description.length < 10"
                        class="
                            inline-flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-semibold
                            bg-amber-500 hover:bg-amber-600 text-white
                            disabled:opacity-50 disabled:cursor-not-allowed
                            transition-all duration-150 active:scale-95
                        "
                    >
                        <svg x-show="submitting" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="submitting ? '...' : 'Envoyer le signalement'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function anomalyModal() {
    return {
        open:          false,
        submitting:    false,
        statusMessage: '',
        statusType:    'success',
        form: {
            field:       '',
            description: '',
            email:       '',
        },

        openModal() {
            this.open          = true;
            this.statusMessage = '';
            document.body.style.overflow = 'hidden';
        },

        closeModal() {
            this.open = false;
            document.body.style.overflow = '';
        },

        async submitReport() {
            if (this.submitting || this.form.description.length < 10) return;

            this.submitting    = true;
            this.statusMessage = '';

            try {
                const formEl = this.$el.querySelector('form');
                const data   = new FormData(formEl);

                const response = await fetch('{{ route("anomaly.store") }}', {
                    method:  'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: data,
                });

                const result = await response.json();

                if (result.success) {
                    this.statusType    = 'success';
                    this.statusMessage = result.message;
                    this.form          = { field: '', description: '', email: '' };

                    // Fermeture automatique après 3 secondes
                    setTimeout(() => this.closeModal(), 3000);
                } else {
                    this.statusType    = 'error';
                    this.statusMessage = result.message || 'Une erreur est survenue.';
                }
            } catch (err) {
                this.statusType    = 'error';
                this.statusMessage = 'Erreur réseau. Veuillez réessayer.';
            } finally {
                this.submitting = false;
            }
        }
    };
}
</script>
@endpush
