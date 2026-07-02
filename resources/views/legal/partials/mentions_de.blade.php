<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Anbieter</h2>
    <p>
        <strong>Firma:</strong> {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }}<br>
        <strong>Rechtsform:</strong> Gesellschaft mit beschränkter Haftung (GmbH/Sàrl)<br>
        <strong>Adresse:</strong> {{ \App\Models\Setting::get('company_address', 'Rue de Genève 1') }}, {{ \App\Models\Setting::get('company_postal', 'CH-1003') }} {{ \App\Models\Setting::get('company_city', 'Lausanne') }}, Schweiz<br>
        <strong>E-Mail:</strong> <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>
        @if(\App\Models\Setting::get('company_uid'))
        <br><strong>UID:</strong> {{ \App\Models\Setting::get('company_uid') }}
        @endif
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Verantwortliche Person</h2>
    <p>Der Verantwortliche ist der gesetzliche Vertreter von {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }}.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Hosting</h2>
    <p>
        Diese Website wird von einem professionellen Webhoster betrieben.<br>
        Die Server befinden sich in Europa.<br>
        <strong>Hosting-Kontakt:</strong> auf Anfrage unter <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Geistiges Eigentum</h2>
    <p>
        Alle Inhalte dieser Website (Texte, Grafiken, Software, Quellcode, Struktur) sind durch das schweizerische und internationale Urheberrecht geschützt.
        Jede Vervielfältigung, Darstellung, Änderung oder Nutzung, ganz oder teilweise, ohne vorherige schriftliche Genehmigung ist ausdrücklich untersagt.
    </p>
    <p class="mt-2">
        Die technischen Fahrzeugdaten stammen aus dem TARGA-System des Bundesamts für Strassen (ASTRA), bereitgestellt gemäss der Verordnung über die Öffentlichkeit des Fahrzeugregisters. Diese Daten sind Eigentum der Schweizerischen Eidgenossenschaft.
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Haftungsausschluss</h2>
    <p>
        Die auf dieser Website veröffentlichten Informationen stammen aus offiziellen Quellen (ASTRA) und dienen nur zur Information. {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }} ist bemüht, die Richtigkeit und Aktualität der veröffentlichten Informationen zu gewährleisten, kann jedoch deren Vollständigkeit, Richtigkeit oder Eignung für einen bestimmten Zweck nicht garantieren.
    </p>
    <p class="mt-2">
        Bei Abweichungen zwischen den auf dieser Website veröffentlichten Informationen und den Daten in den offiziellen Registern sind letztere massgebend.
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Hyperlinks</h2>
    <p>
        Diese Website kann Links zu Websites Dritter enthalten. {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }} hat keinen Einfluss auf diese Websites und übernimmt keine Verantwortung für deren Inhalt.
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Anwendbares Recht</h2>
    <p>
        Diese Website unterliegt dem schweizerischen Recht. Alle Streitigkeiten im Zusammenhang mit der Nutzung dieser Website fallen in die ausschliessliche Zuständigkeit der Gerichte des Kantons Waadt (Schweiz).
    </p>
</section>
