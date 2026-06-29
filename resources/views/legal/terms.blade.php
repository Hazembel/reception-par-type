@extends('layouts.app')
@section('content')
<div class="pt-20 pb-16 max-w-3xl mx-auto px-4 sm:px-6">
    <h1 class="font-display text-2xl text-slate-900 dark:text-white mb-8">{{ $meta_title ?? __('app.legal.terms_title') }}</h1>

    <div class="prose dark:prose-invert text-slate-600 dark:text-slate-300 text-sm space-y-8">

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">1. Objet et champ d'application</h2>
            <p>
                Les présentes Conditions Générales d'Utilisation (CGU) régissent l'accès et l'utilisation de la plateforme <strong>reception-par-type.ch</strong>, exploitée par {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }}, {{ \App\Models\Setting::get('company_address', 'Rue de Genève 1') }}, {{ \App\Models\Setting::get('company_postal', 'CH-1003') }} {{ \App\Models\Setting::get('company_city', 'Lausanne') }}, Suisse (ci-après « l'Éditeur »).
            </p>
            <p class="mt-2">
                La plateforme permet de consulter les données de réception par type (homologations) des véhicules enregistrés en Suisse, issues du système TARGA de l'Office fédéral des routes (OFROU/ASTRA). L'utilisation de la plateforme implique l'acceptation sans réserve des présentes CGU.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">2. Accès au service</h2>
            <p>La plateforme propose plusieurs niveaux d'accès :</p>
            <ul class="mt-2 space-y-1 list-disc list-inside">
                <li><strong>Accès libre :</strong> consultation limitée (résultats de recherche, aperçu partiel des fiches techniques)</li>
                <li><strong>Accès par jeton :</strong> déverrouillage individuel d'une fiche technique pour une durée limitée</li>
                <li><strong>Abonnement :</strong> accès complet selon le niveau souscrit (mensuel ou annuel, en CHF)</li>
                <li><strong>Accès API :</strong> intégration des données via l'API v1, réservée aux plans professionnels</li>
            </ul>
            <p class="mt-2">
                L'Éditeur se réserve le droit de modifier les fonctionnalités disponibles à chaque niveau d'accès, notamment en cas d'évolution des données sources ou du cadre légal.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">3. Création de compte</h2>
            <p>
                L'accès aux fonctionnalités payantes requiert la création d'un compte avec une adresse e-mail valide. L'utilisateur s'engage à fournir des informations exactes et à les maintenir à jour. Toute activité réalisée depuis un compte relève de la responsabilité du titulaire.
            </p>
            <p class="mt-2">
                Les comptes sont à usage strictement personnel et non transférable. L'Éditeur peut suspendre ou supprimer tout compte en cas de violation des présentes CGU, sans préavis ni indemnité.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">4. Conditions financières</h2>
            <p>
                Les tarifs sont affichés en francs suisses (CHF) toutes taxes comprises. Les paiements sont traités par <strong>PayPal</strong>. La facturation est immédiate.
            </p>
            <p class="mt-2">
                <strong>Abonnements :</strong> L'abonnement est renouvelé automatiquement à la fin de chaque période (mensuelle ou annuelle) sauf résiliation avant l'échéance. La résiliation prend effet à la fin de la période en cours.
            </p>
            <p class="mt-2">
                <strong>Jetons :</strong> Les jetons sont à usage unique et non remboursables après activation. Ils n'expirent pas et restent associés au compte de l'utilisateur.
            </p>
            <p class="mt-2">
                <strong>Droit de rétractation :</strong> Conformément à l'art. 40e CO (Suisse) et à l'art. 16(m) de la directive 2011/83/UE, le droit de rétractation est exclu pour les contenus numériques dont l'exécution a commencé avant l'expiration du délai de rétractation, avec l'accord exprès du consommateur.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">5. Exactitude des données</h2>
            <p>
                Les données techniques des véhicules (masses, motorisation, pneumatiques, émissions, etc.) proviennent exclusivement du système TARGA de l'OFROU, mis à jour mensuellement. L'Éditeur reproduit ces données de bonne foi mais ne garantit pas leur exactitude, leur exhaustivité ni leur adéquation à un usage particulier (expertise technique, contrôle technique, immatriculation, etc.).
            </p>
            <p class="mt-2">
                <strong>En cas de divergence entre les informations publiées et les registres officiels (OFROU, canton, assurance), les registres officiels font foi.</strong> La plateforme ne se substitue à aucun document officiel (permis de circulation, certification d'homologation).
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">6. Utilisation acceptable</h2>
            <p>Il est interdit :</p>
            <ul class="mt-2 space-y-1 list-disc list-inside">
                <li>D'automatiser les requêtes (scraping, crawling) sans autorisation écrite de l'Éditeur</li>
                <li>De revendre ou redistribuer les données extraites de la plateforme</li>
                <li>D'utiliser le service à des fins illégales ou contraires à l'ordre public</li>
                <li>De tenter de contourner les mécanismes de contrôle d'accès</li>
                <li>De partager ses identifiants avec des tiers</li>
            </ul>
            <p class="mt-2">
                L'accès API est soumis à des quotas d'utilisation définis par niveau d'abonnement. Tout dépassement abusif peut entraîner la suspension du compte.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">7. Propriété intellectuelle</h2>
            <p>
                La plateforme, son code, son design, ses algorithmes et l'organisation des données sont la propriété exclusive de l'Éditeur. Les données TARGA sont la propriété de la Confédération suisse (OFROU) et sont réutilisées dans le cadre de la politique de données ouvertes de la Confédération.
            </p>
            <p class="mt-2">
                L'utilisateur se voit accorder un droit d'utilisation personnel, non exclusif et non transférable, limité à la consultation des données dans le cadre du niveau d'accès souscrit.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">8. Limitation de responsabilité</h2>
            <p>
                Dans les limites autorisées par le droit suisse, l'Éditeur ne saurait être tenu responsable de :
            </p>
            <ul class="mt-2 space-y-1 list-disc list-inside">
                <li>Décisions prises sur la base des données publiées (expertises, achats, réparations)</li>
                <li>Indisponibilités temporaires du service (maintenance, incidents techniques)</li>
                <li>Erreurs ou omissions dans les données sources publiées par l'OFROU</li>
                <li>Dommages indirects, pertes d'exploitation ou manques à gagner</li>
            </ul>
            <p class="mt-2">
                La responsabilité de l'Éditeur est en tout état de cause limitée au montant payé par l'utilisateur au cours des 12 derniers mois.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">9. Disponibilité du service</h2>
            <p>
                L'Éditeur s'efforce d'assurer la disponibilité du service 24h/24, 7j/7, sans toutefois s'engager sur un niveau de service (SLA). Des interruptions ponctuelles peuvent survenir pour maintenance ou mise à jour. L'Éditeur informera les utilisateurs des maintenances planifiées dans la mesure du possible.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">10. Protection des données</h2>
            <p>
                Le traitement des données personnelles est régi par notre
                <a href="{{ route('privacy', ['locale' => app()->getLocale()]) }}" class="text-blue-600 dark:text-blue-400">Politique de confidentialité</a>,
                qui fait partie intégrante des présentes CGU.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">11. Programme d'affiliation</h2>
            <p>
                L'Éditeur propose un programme d'affiliation permettant à des partenaires (affiliés) de percevoir une commission sur les ventes générées par leur lien de parrainage. Les conditions spécifiques du programme (taux de commission, durée de validité des cookies, conditions de paiement) sont définies dans l'espace affilié. L'Éditeur se réserve le droit de modifier ou de suspendre le programme à tout moment.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">12. Modification des CGU</h2>
            <p>
                L'Éditeur peut modifier les présentes CGU à tout moment. Les utilisateurs disposant d'un compte seront informés de toute modification substantielle par e-mail au moins 15 jours avant son entrée en vigueur. La poursuite de l'utilisation du service après notification vaut acceptation des nouvelles CGU.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">13. Droit applicable et for juridique</h2>
            <p>
                Les présentes CGU sont soumises au droit suisse. En cas de litige, les parties s'efforceront de trouver une solution amiable. À défaut, les tribunaux ordinaires du canton de Vaud (Suisse) seront exclusivement compétents.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">14. Contact</h2>
            <p>
                Pour toute question relative aux présentes CGU :<br>
                <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a><br>
                {{ \App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl') }}, {{ \App\Models\Setting::get('company_address', 'Rue de Genève 1') }}, {{ \App\Models\Setting::get('company_postal', 'CH-1003') }} {{ \App\Models\Setting::get('company_city', 'Lausanne') }}, Suisse
            </p>
        </section>

        <p class="text-xs text-slate-400 dark:text-slate-500 mt-8">Dernière mise à jour : {{ now()->format('d.m.Y') }}</p>
    </div>
</div>
@endsection
