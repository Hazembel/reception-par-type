<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Verantwortlicher</h2>
    <p>
        <strong>{{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }}</strong><br>
        {{ \App\Models\Setting::get('company_address', 'Rue de Genève 1') }}, {{ \App\Models\Setting::get('company_postal', 'CH-1003') }} {{ \App\Models\Setting::get('company_city', 'Lausanne') }}, Schweiz<br>
        E-Mail: <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Erhobene Daten</h2>
    <p>Wir erheben folgende Daten im Rahmen der Nutzung unserer Dienste:</p>
    <ul class="mt-2 space-y-1 list-disc list-inside">
        <li><strong>Kontodaten:</strong> E-Mail-Adresse, verschlüsseltes Passwort (bcrypt)</li>
        <li><strong>Zahlungsdaten:</strong> Betrag, Währung, PayPal-Bestellkennung (keine Kreditkartendaten werden gespeichert)</li>
        <li><strong>Rechnungsdaten:</strong> Name, Adresse (für Rechnungen)</li>
        <li><strong>Nutzungsdaten:</strong> durchgeführte Suchen, aufgerufene Datenblätter, Datum und Uhrzeit der Zugriffe</li>
        <li><strong>Technische Daten:</strong> IP-Adresse, Browsertyp, Betriebssystem (Server-Logs)</li>
        <li><strong>Session-Cookies:</strong> notwendig für die Authentifizierung</li>
        <li><strong>Affiliate-Tracking-Cookie:</strong> Affiliate-Code, gespeichert für 30 Tage</li>
    </ul>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Zwecke und Rechtsgrundlagen der Verarbeitung</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse mt-2">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-800">
                    <th class="text-left p-2 border border-slate-200 dark:border-slate-700">Zweck</th>
                    <th class="text-left p-2 border border-slate-200 dark:border-slate-700">Rechtsgrundlage (DSGVO)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td class="p-2 border border-slate-200 dark:border-slate-700">Erbringung des Dienstes</td><td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(b) — Vertragserfüllung</td></tr>
                <tr class="bg-slate-50 dark:bg-slate-900"><td class="p-2 border border-slate-200 dark:border-slate-700">Zahlungsabwicklung und Rechnungsstellung</td><td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(b) — Vertragserfüllung</td></tr>
                <tr><td class="p-2 border border-slate-200 dark:border-slate-700">Gesetzliche Pflichten (Aufbewahrung von Rechnungen)</td><td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(c) — rechtliche Verpflichtung</td></tr>
                <tr class="bg-slate-50 dark:bg-slate-900"><td class="p-2 border border-slate-200 dark:border-slate-700">Sicherheit und Betrugsvorbeugung</td><td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(f) — berechtigtes Interesse</td></tr>
                <tr><td class="p-2 border border-slate-200 dark:border-slate-700">Verwaltung des Partnerprogramms</td><td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(b) — Vertragserfüllung</td></tr>
            </tbody>
        </table>
    </div>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Speicherfristen</h2>
    <ul class="mt-2 space-y-1 list-disc list-inside">
        <li><strong>Kontodaten:</strong> Dauer der Vertragsbeziehung + 3 Jahre nach Kündigung</li>
        <li><strong>Rechnungen und Buchhaltungsdaten:</strong> 10 Jahre (gesetzliche Pflicht, Art. 958f OR)</li>
        <li><strong>Server-Zugriffsprotokolle:</strong> 90 Tage</li>
        <li><strong>Session-Cookies:</strong> Dauer der Sitzung oder bis zur Abmeldung</li>
        <li><strong>Affiliate-Cookies:</strong> 30 Tage</li>
    </ul>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Empfänger der Daten</h2>
    <p>Ihre Daten können an folgende Auftragsverarbeiter weitergegeben werden:</p>
    <ul class="mt-2 space-y-2 list-disc list-inside">
        <li><strong>PayPal</strong> — Zahlungsabwicklung. <a href="https://www.paypal.com/de/webapps/mpp/ua/privacy-full" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400">PayPal-Datenschutzerklärung</a></li>
        <li><strong>Hosting-Anbieter</strong> — Serverhosting (Europa)</li>
        <li><strong>ASTRA</strong> — Quelle der technischen Fahrzeugdaten (keine Übermittlung von Nutzerdaten)</li>
    </ul>
    <p class="mt-2">Wir verkaufen oder vermieten Ihre persönlichen Daten nicht an Dritte.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Übermittlung in Drittländer</h2>
    <p>Unsere Server befinden sich in Europa. Zahlungen werden von PayPal (mit Sitz in Luxemburg, EU) verarbeitet. Abgesehen von PayPal werden Ihre Daten nicht ausserhalb der Schweiz oder des Europäischen Wirtschaftsraums übertragen.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Cookies</h2>
    <p>Wir verwenden ausschliesslich technisch notwendige Cookies:</p>
    <ul class="mt-2 space-y-1 list-disc list-inside">
        <li><strong>Session-Cookie (XSRF-TOKEN, session)</strong>: Authentifizierung und CSRF-Schutz</li>
        <li><strong>Affiliate-Cookie (rpt_ref)</strong>: Partnerschaftsverfolgung, 30 Tage</li>
    </ul>
    <p class="mt-2">Wir verwenden keine Werbe-Tracking- oder Verhaltensanalyse-Cookies.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Ihre Rechte</h2>
    <p>Gemäss DSGVO (EU 2016/679) und dem schweizerischen Datenschutzgesetz (DSG) haben Sie folgende Rechte:</p>
    <ul class="mt-2 space-y-1 list-disc list-inside">
        <li><strong>Auskunftsrecht:</strong> Kopie Ihrer personenbezogenen Daten erhalten</li>
        <li><strong>Recht auf Berichtigung:</strong> unrichtige Daten korrigieren</li>
        <li><strong>Recht auf Löschung:</strong> Löschung Ihrer Daten beantragen</li>
        <li><strong>Recht auf Einschränkung:</strong> Verarbeitung in bestimmten Fällen einschränken</li>
        <li><strong>Recht auf Datenübertragbarkeit:</strong> Daten in strukturierter Form erhalten</li>
        <li><strong>Widerspruchsrecht:</strong> der Verarbeitung auf der Grundlage berechtigter Interessen widersprechen</li>
    </ul>
    <p class="mt-3">
        Zur Ausübung Ihrer Rechte: <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a><br>
        Wir antworten innerhalb von 30 Tagen. Bei Streitigkeiten können Sie sich an den <a href="https://www.edoeb.admin.ch" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400">Eidgenössischen Datenschutzbeauftragten (EDÖB)</a> wenden.
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Sicherheit</h2>
    <p>Wir wenden angemessene technische und organisatorische Massnahmen an: HTTPS-Verschlüsselung (TLS), Passwort-Hashing (bcrypt), CSRF-Schutz, rollenbasierte Zugriffskontrolle. Bitte melden Sie Anomalien an <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Änderungen</h2>
    <p>Wir behalten uns das Recht vor, diese Datenschutzerklärung jederzeit zu ändern. Bei wesentlichen Änderungen werden registrierte Benutzer per E-Mail informiert. Die jeweils gültige Version ist stets unter dieser Adresse abrufbar.</p>
</section>
