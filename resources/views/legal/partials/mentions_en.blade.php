<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Website Publisher</h2>
    <p>
        <strong>Company name:</strong> {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }}<br>
        <strong>Legal form:</strong> Private limited company (Sàrl/GmbH)<br>
        <strong>Address:</strong> {{ \App\Models\Setting::get('company_address', 'Rue de Genève 1') }}, {{ \App\Models\Setting::get('company_postal', 'CH-1003') }} {{ \App\Models\Setting::get('company_city', 'Lausanne') }}, Switzerland<br>
        <strong>Email:</strong> <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>
        @if(\App\Models\Setting::get('company_uid'))
        <br><strong>Company ID (UID):</strong> {{ \App\Models\Setting::get('company_uid') }}
        @endif
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Publication Director</h2>
    <p>The publication director is the legal representative of {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }}.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Hosting</h2>
    <p>
        This website is hosted by a professional web hosting provider.<br>
        Servers are located in Europe.<br>
        <strong>Hosting contact:</strong> available on request at <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Intellectual Property</h2>
    <p>
        All content on this website (texts, graphics, software, source code, structure) is protected by Swiss and international copyright law.
        Any reproduction, representation, modification or exploitation, in whole or in part, without prior written authorisation is strictly prohibited.
    </p>
    <p class="mt-2">
        Vehicle technical data originates from the TARGA system of the Federal Roads Office (OFROU/ASTRA), made available under the ordinance on the public nature of the vehicle register. This data is the property of the Swiss Confederation.
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Limitation of Liability</h2>
    <p>
        The information published on this website comes from official sources (OFROU/ASTRA) and is provided for information purposes only. {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }} strives to ensure the accuracy and currency of the information provided, but cannot guarantee its completeness, accuracy or suitability for any particular purpose.
    </p>
    <p class="mt-2">
        In the event of discrepancies between the information published on this website and the data in official registers, the latter shall prevail.
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Hyperlinks</h2>
    <p>
        This website may contain links to third-party websites. {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }} has no control over these websites and accepts no responsibility for their content.
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Applicable Law</h2>
    <p>
        This website is governed by Swiss law. Any dispute relating to the use of this website falls under the exclusive jurisdiction of the courts of the canton of Vaud (Switzerland).
    </p>
</section>
