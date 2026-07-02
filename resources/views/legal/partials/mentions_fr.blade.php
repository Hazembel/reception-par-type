<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Éditeur du site</h2>
    <p>
        <strong>Raison sociale :</strong> {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }}<br>
        <strong>Forme juridique :</strong> Société à responsabilité limitée (Sàrl)<br>
        <strong>Adresse :</strong> {{ \App\Models\Setting::get('company_address', 'Rue de Genève 1') }}, {{ \App\Models\Setting::get('company_postal', 'CH-1003') }} {{ \App\Models\Setting::get('company_city', 'Lausanne') }}, Suisse<br>
        <strong>E-mail :</strong> <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>
        @if(\App\Models\Setting::get('company_uid'))
        <br><strong>IDE/UID :</strong> {{ \App\Models\Setting::get('company_uid') }}
        @endif
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Directeur de la publication</h2>
    <p>Le directeur de la publication est le représentant légal de {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }}.</p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Hébergement</h2>
    <p>
        Le site est hébergé par un prestataire d'hébergement web professionnel.<br>
        Les serveurs sont situés en Europe.<br>
        <strong>Contact hébergeur :</strong> disponible sur demande à <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Propriété intellectuelle</h2>
    <p>
        L'ensemble du contenu de ce site (textes, graphismes, logiciels, code source, structure) est protégé par le droit d'auteur suisse et international.
        Toute reproduction, représentation, modification ou exploitation, totale ou partielle, sans autorisation écrite préalable est strictement interdite.
    </p>
    <p class="mt-2">
        Les données techniques des véhicules proviennent du système TARGA de l'Office fédéral des routes (OFROU / ASTRA), mis à disposition en vertu de l'ordonnance sur la publicité du registre des véhicules. Ces données sont la propriété de la Confédération suisse.
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Limitation de responsabilité</h2>
    <p>
        Les informations publiées sur ce site sont issues de sources officielles (OFROU/ASTRA) et sont fournies à titre indicatif. {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }} s'efforce d'assurer l'exactitude et la mise à jour des informations diffusées, mais ne peut garantir l'exhaustivité, l'exactitude ou l'adéquation à un usage particulier.
    </p>
    <p class="mt-2">
        En cas de divergence entre les informations publiées sur ce site et les données figurant dans les registres officiels, ces derniers font foi.
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Liens hypertextes</h2>
    <p>
        Ce site peut contenir des liens vers des sites tiers. {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }} n'exerce aucun contrôle sur ces sites et décline toute responsabilité quant à leur contenu.
    </p>
</section>

<section>
    <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Droit applicable</h2>
    <p>
        Le présent site est soumis au droit suisse. Tout litige relatif à l'utilisation de ce site relève de la compétence exclusive des tribunaux du canton de Vaud (Suisse).
    </p>
</section>
