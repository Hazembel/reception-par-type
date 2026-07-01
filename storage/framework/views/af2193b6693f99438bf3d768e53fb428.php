<?php $__env->startSection('content'); ?>
<div class="pt-20 pb-16 max-w-3xl mx-auto px-4 sm:px-6">
    <h1 class="font-display text-2xl text-slate-900 dark:text-white mb-8"><?php echo e($meta_title ?? __('app.legal.privacy_title')); ?></h1>

    <div class="prose dark:prose-invert text-slate-600 dark:text-slate-300 text-sm space-y-8">

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Responsable du traitement</h2>
            <p>
                <strong><?php echo e(\App\Models\Setting::get('company_name', 'reception-par-type.ch Sàrl')); ?></strong><br>
                <?php echo e(\App\Models\Setting::get('company_address', 'Rue de Genève 1')); ?>, <?php echo e(\App\Models\Setting::get('company_postal', 'CH-1003')); ?> <?php echo e(\App\Models\Setting::get('company_city', 'Lausanne')); ?>, Suisse<br>
                E-mail : <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Données collectées</h2>
            <p>Nous collectons les données suivantes dans le cadre de l'utilisation de nos services :</p>
            <ul class="mt-2 space-y-1 list-disc list-inside">
                <li><strong>Données de compte :</strong> adresse e-mail, mot de passe chiffré (bcrypt)</li>
                <li><strong>Données de paiement :</strong> montant, devise, identifiant d'ordre PayPal (nous ne stockons jamais vos données de carte bancaire)</li>
                <li><strong>Données de facturation :</strong> nom, adresse (pour les factures)</li>
                <li><strong>Données d'utilisation :</strong> recherches effectuées, fiches consultées, date et heure des accès</li>
                <li><strong>Données techniques :</strong> adresse IP, type de navigateur, système d'exploitation (journaux serveur)</li>
                <li><strong>Cookies de session :</strong> nécessaires au fonctionnement de l'authentification</li>
                <li><strong>Cookies de suivi d'affiliation :</strong> code affilié, conservé 30 jours</li>
            </ul>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Finalités et bases légales du traitement</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse mt-2">
                    <thead>
                        <tr class="bg-slate-100 dark:bg-slate-800">
                            <th class="text-left p-2 border border-slate-200 dark:border-slate-700">Finalité</th>
                            <th class="text-left p-2 border border-slate-200 dark:border-slate-700">Base légale (RGPD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-2 border border-slate-200 dark:border-slate-700">Fourniture du service (accès aux fiches techniques)</td>
                            <td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(b) — exécution du contrat</td>
                        </tr>
                        <tr class="bg-slate-50 dark:bg-slate-900">
                            <td class="p-2 border border-slate-200 dark:border-slate-700">Traitement des paiements et facturation</td>
                            <td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(b) — exécution du contrat</td>
                        </tr>
                        <tr>
                            <td class="p-2 border border-slate-200 dark:border-slate-700">Obligations légales (conservation des factures)</td>
                            <td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(c) — obligation légale</td>
                        </tr>
                        <tr class="bg-slate-50 dark:bg-slate-900">
                            <td class="p-2 border border-slate-200 dark:border-slate-700">Sécurité et prévention de la fraude</td>
                            <td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(f) — intérêt légitime</td>
                        </tr>
                        <tr>
                            <td class="p-2 border border-slate-200 dark:border-slate-700">Gestion du programme d'affiliation</td>
                            <td class="p-2 border border-slate-200 dark:border-slate-700">Art. 6(1)(b) — exécution du contrat</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Durées de conservation</h2>
            <ul class="mt-2 space-y-1 list-disc list-inside">
                <li><strong>Données de compte :</strong> durée de la relation contractuelle + 3 ans après résiliation</li>
                <li><strong>Factures et données comptables :</strong> 10 ans (obligation légale, art. 958f CO)</li>
                <li><strong>Journaux d'accès serveur :</strong> 90 jours</li>
                <li><strong>Cookies de session :</strong> durée de la session ou jusqu'à déconnexion</li>
                <li><strong>Cookies d'affiliation :</strong> 30 jours</li>
            </ul>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Destinataires des données</h2>
            <p>Vos données peuvent être transmises aux sous-traitants suivants :</p>
            <ul class="mt-2 space-y-2 list-disc list-inside">
                <li>
                    <strong>PayPal (PayPal Europe S.à r.l. et Cie, S.C.A.)</strong> — traitement des paiements.
                    <a href="https://www.paypal.com/fr/webapps/mpp/ua/privacy-full" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400">Politique de confidentialité PayPal</a>
                </li>
                <li>
                    <strong>Hébergeur</strong> — prestataire d'hébergement des serveurs (Europe)
                </li>
                <li>
                    <strong>Office fédéral des routes (OFROU/ASTRA)</strong> — source des données techniques des véhicules (aucun transfert de données utilisateurs)
                </li>
            </ul>
            <p class="mt-2">Nous ne vendons ni ne louons vos données personnelles à des tiers.</p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Transferts hors Suisse / UE</h2>
            <p>
                Nos serveurs sont situés en Europe. Les paiements sont traités par PayPal (établi au Luxembourg, UE). En dehors de PayPal, vos données ne sont pas transférées hors de la Suisse ou de l'Espace économique européen.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Cookies</h2>
            <p>Nous utilisons uniquement les cookies strictement nécessaires au fonctionnement du site :</p>
            <ul class="mt-2 space-y-1 list-disc list-inside">
                <li><strong>Cookie de session (XSRF-TOKEN, session)</strong> : authentification et sécurité CSRF</li>
                <li><strong>Cookie d'affiliation (rpt_ref)</strong> : suivi du parrainage, durée 30 jours</li>
            </ul>
            <p class="mt-2">Nous n'utilisons pas de cookies de traçage publicitaire ou d'analyse comportementale.</p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Vos droits</h2>
            <p>Conformément au RGPD (UE 2016/679) et à la loi fédérale suisse sur la protection des données (LPD), vous disposez des droits suivants :</p>
            <ul class="mt-2 space-y-1 list-disc list-inside">
                <li><strong>Droit d'accès :</strong> obtenir une copie de vos données personnelles</li>
                <li><strong>Droit de rectification :</strong> corriger des données inexactes</li>
                <li><strong>Droit à l'effacement :</strong> demander la suppression de vos données (sous réserve des obligations légales de conservation)</li>
                <li><strong>Droit à la limitation :</strong> restreindre le traitement dans certains cas</li>
                <li><strong>Droit à la portabilité :</strong> recevoir vos données dans un format structuré</li>
                <li><strong>Droit d'opposition :</strong> vous opposer au traitement fondé sur l'intérêt légitime</li>
            </ul>
            <p class="mt-3">
                Pour exercer vos droits, contactez-nous à :
                <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a><br>
                Nous répondons dans un délai de 30 jours. En cas de litige, vous pouvez déposer une plainte auprès du
                <a href="https://www.edoeb.admin.ch" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400">Préposé fédéral à la protection des données et à la transparence (PFPDT)</a>.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Sécurité</h2>
            <p>
                Nous appliquons des mesures techniques et organisationnelles appropriées : chiffrement HTTPS (TLS), hachage des mots de passe (bcrypt), protection CSRF, contrôle d'accès par rôle. Aucune mesure ne garantissant une sécurité absolue, nous vous invitons à signaler toute anomalie à <a href="mailto:admin@reception-par-type.ch" class="text-blue-600 dark:text-blue-400">admin@reception-par-type.ch</a>.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-200 mb-3">Modifications</h2>
            <p>
                Nous nous réservons le droit de modifier cette politique à tout moment. En cas de modification substantielle, les utilisateurs disposant d'un compte seront informés par e-mail. La version en vigueur est toujours accessible à cette adresse.
            </p>
        </section>

        <p class="text-xs text-slate-400 dark:text-slate-500 mt-8">Dernière mise à jour : <?php echo e(now()->format('d.m.Y')); ?></p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/legal/privacy.blade.php ENDPATH**/ ?>