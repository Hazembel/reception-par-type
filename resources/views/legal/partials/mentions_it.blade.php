<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Editore del sito</h2>
    <p>
        <strong>Ragione sociale:</strong> {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }}<br>
        <strong>Forma giuridica:</strong> Società a responsabilità limitata (Sàrl/GmbH)<br>
        <strong>Indirizzo:</strong> {{ \App\Models\Setting::get('company_address', 'Rue de Genève 1') }}, {{ \App\Models\Setting::get('company_postal', 'CH-1003') }} {{ \App\Models\Setting::get('company_city', 'Lausanne') }}, Svizzera<br>
        <strong>E-mail:</strong> <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>
        @if(\App\Models\Setting::get('company_uid'))
        <br><strong>IDE/UID:</strong> {{ \App\Models\Setting::get('company_uid') }}
        @endif
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Direttore responsabile</h2>
    <p>Il direttore responsabile è il rappresentante legale di {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }}.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Hosting</h2>
    <p>
        Il sito è ospitato da un fornitore professionale di servizi di hosting web.<br>
        I server si trovano in Europa.<br>
        <strong>Contatto hosting:</strong> disponibile su richiesta all'indirizzo <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Proprietà intellettuale</h2>
    <p>
        Tutti i contenuti di questo sito (testi, grafica, software, codice sorgente, struttura) sono protetti dal diritto d'autore svizzero e internazionale.
        Qualsiasi riproduzione, rappresentazione, modifica o sfruttamento, totale o parziale, senza previa autorizzazione scritta è severamente vietata.
    </p>
    <p class="mt-2">
        I dati tecnici dei veicoli provengono dal sistema TARGA dell'Ufficio federale delle strade (USTRA/ASTRA), messo a disposizione ai sensi dell'ordinanza sulla pubblicità del registro dei veicoli. Questi dati sono di proprietà della Confederazione Svizzera.
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Limitazione di responsabilità</h2>
    <p>
        Le informazioni pubblicate su questo sito provengono da fonti ufficiali (USTRA/ASTRA) e sono fornite a titolo informativo. {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }} si impegna a garantire l'accuratezza e l'aggiornamento delle informazioni diffuse, ma non può garantirne la completezza, l'accuratezza o l'adeguatezza a uno scopo particolare.
    </p>
    <p class="mt-2">
        In caso di discrepanza tra le informazioni pubblicate su questo sito e i dati presenti nei registri ufficiali, questi ultimi prevarranno.
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Link ipertestuali</h2>
    <p>
        Questo sito può contenere link a siti di terze parti. {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }} non ha alcun controllo su questi siti e declina ogni responsabilità per i loro contenuti.
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Diritto applicabile</h2>
    <p>
        Questo sito è soggetto al diritto svizzero. Qualsiasi controversia relativa all'utilizzo di questo sito rientra nella competenza esclusiva dei tribunali del Canton Vaud (Svizzera).
    </p>
</section>
