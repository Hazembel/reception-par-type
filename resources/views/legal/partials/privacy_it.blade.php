<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Titolare del trattamento</h2>
    <p>
        <strong>{{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }}</strong><br>
        {{ \App\Models\Setting::get('company_address', 'Rue de Genève 1') }}, {{ \App\Models\Setting::get('company_postal', 'CH-1003') }} {{ \App\Models\Setting::get('company_city', 'Lausanne') }}, Svizzera<br>
        E-mail: <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Dati raccolti</h2>
    <p>Raccogliamo i seguenti dati nell'ambito dell'utilizzo dei nostri servizi:</p>
    <ul class="mt-2 space-y-1 list-disc list-inside">
        <li><strong>Dati dell'account:</strong> indirizzo e-mail, password cifrata (bcrypt)</li>
        <li><strong>Dati di pagamento:</strong> importo, valuta, ID ordine PayPal (non conserviamo mai i dati della carta)</li>
        <li><strong>Dati di fatturazione:</strong> nome, indirizzo (per le fatture)</li>
        <li><strong>Dati di utilizzo:</strong> ricerche effettuate, schede consultate, data e ora degli accessi</li>
        <li><strong>Dati tecnici:</strong> indirizzo IP, tipo di browser, sistema operativo (log del server)</li>
        <li><strong>Cookie di sessione:</strong> necessari per l'autenticazione</li>
        <li><strong>Cookie di affiliazione:</strong> codice affiliato, conservato per 30 giorni</li>
    </ul>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Finalità e basi giuridiche del trattamento</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse mt-2">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-800">
                    <th class="text-left p-2 border border-slate-200 dark:border-slate-700">Finalità</th>
                    <th class="text-left p-2 border border-slate-200 dark:border-slate-700">Base giuridica (GDPR)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td class="p-2 border border-slate-200 dark:border-slate-700">Fornitura del servizio</td><td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(b) — esecuzione del contratto</td></tr>
                <tr class="bg-slate-50 dark:bg-slate-900"><td class="p-2 border border-slate-200 dark:border-slate-700">Gestione dei pagamenti e fatturazione</td><td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(b) — esecuzione del contratto</td></tr>
                <tr><td class="p-2 border border-slate-200 dark:border-slate-700">Obblighi legali (conservazione fatture)</td><td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(c) — obbligo legale</td></tr>
                <tr class="bg-slate-50 dark:bg-slate-900"><td class="p-2 border border-slate-200 dark:border-slate-700">Sicurezza e prevenzione delle frodi</td><td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(f) — interesse legittimo</td></tr>
                <tr><td class="p-2 border border-slate-200 dark:border-slate-700">Gestione del programma di affiliazione</td><td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(b) — esecuzione del contratto</td></tr>
            </tbody>
        </table>
    </div>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Periodi di conservazione</h2>
    <ul class="mt-2 space-y-1 list-disc list-inside">
        <li><strong>Dati dell'account:</strong> durata del rapporto contrattuale + 3 anni dopo la cessazione</li>
        <li><strong>Fatture e dati contabili:</strong> 10 anni (obbligo legale, art. 958f CO)</li>
        <li><strong>Log di accesso al server:</strong> 90 giorni</li>
        <li><strong>Cookie di sessione:</strong> durata della sessione o fino al logout</li>
        <li><strong>Cookie di affiliazione:</strong> 30 giorni</li>
    </ul>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Destinatari dei dati</h2>
    <p>I tuoi dati possono essere trasmessi ai seguenti responsabili del trattamento:</p>
    <ul class="mt-2 space-y-2 list-disc list-inside">
        <li><strong>PayPal</strong> — elaborazione dei pagamenti. <a href="https://www.paypal.com/it/webapps/mpp/ua/privacy-full" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400">Informativa sulla privacy di PayPal</a></li>
        <li><strong>Fornitore di hosting</strong> — hosting del server (Europa)</li>
        <li><strong>USTRA/ASTRA</strong> — fonte dei dati tecnici dei veicoli (nessun trasferimento di dati utenti)</li>
    </ul>
    <p class="mt-2">Non vendiamo né affittiamo i tuoi dati personali a terzi.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Trasferimenti fuori dalla Svizzera / UE</h2>
    <p>I nostri server si trovano in Europa. I pagamenti sono elaborati da PayPal (con sede in Lussemburgo, UE). A parte PayPal, i tuoi dati non vengono trasferiti al di fuori della Svizzera o dello Spazio economico europeo.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Cookie</h2>
    <p>Utilizziamo solo i cookie strettamente necessari al funzionamento del sito:</p>
    <ul class="mt-2 space-y-1 list-disc list-inside">
        <li><strong>Cookie di sessione (XSRF-TOKEN, session)</strong>: autenticazione e protezione CSRF</li>
        <li><strong>Cookie di affiliazione (rpt_ref)</strong>: monitoraggio del referral, 30 giorni</li>
    </ul>
    <p class="mt-2">Non utilizziamo cookie di tracciamento pubblicitario o di analisi comportamentale.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">I tuoi diritti</h2>
    <p>Ai sensi del GDPR (UE 2016/679) e della Legge federale svizzera sulla protezione dei dati (LPD), hai i seguenti diritti:</p>
    <ul class="mt-2 space-y-1 list-disc list-inside">
        <li><strong>Diritto di accesso:</strong> ottenere una copia dei tuoi dati personali</li>
        <li><strong>Diritto di rettifica:</strong> correggere dati inesatti</li>
        <li><strong>Diritto alla cancellazione:</strong> richiedere la cancellazione dei tuoi dati</li>
        <li><strong>Diritto alla limitazione:</strong> limitare il trattamento in determinati casi</li>
        <li><strong>Diritto alla portabilità:</strong> ricevere i tuoi dati in formato strutturato</li>
        <li><strong>Diritto di opposizione:</strong> opporti al trattamento basato su interessi legittimi</li>
    </ul>
    <p class="mt-3">
        Per esercitare i tuoi diritti: <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a><br>
        Rispondiamo entro 30 giorni. In caso di controversia, puoi presentare un reclamo all'<a href="https://www.edoeb.admin.ch" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400">Incaricato federale della protezione dei dati (IFPDT)</a>.
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Sicurezza</h2>
    <p>Applichiamo misure tecniche e organizzative appropriate: cifratura HTTPS (TLS), hashing delle password (bcrypt), protezione CSRF, controllo degli accessi basato sui ruoli. Si prega di segnalare qualsiasi anomalia a <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Modifiche</h2>
    <p>Ci riserviamo il diritto di modificare questa informativa in qualsiasi momento. In caso di modifica sostanziale, gli utenti registrati saranno informati per e-mail. La versione in vigore è sempre accessibile a questo indirizzo.</p>
</section>
