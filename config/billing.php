<?php

/**
 * config/billing.php — Coordonnées entreprise et paramètres TVA pour les factures suisses.
 */
return [
    // Informations du vendeur (apparaissent sur chaque facture)
    'company_name' => env('BILLING_COMPANY', 'reception-par-type.ch Sàrl'),
    'address_line1'=> env('BILLING_ADDRESS', 'Route de Genève 1'),
    'address_line2'=> env('BILLING_ADDRESS2', ''),
    'postal'       => env('BILLING_POSTAL', 'CH-1010'),
    'city'         => env('BILLING_CITY', 'Lausanne'),
    'country'      => 'CH',
    'email'        => env('BILLING_EMAIL', 'facturation@reception-par-type.ch'),
    'phone'        => env('BILLING_PHONE', ''),

    // Identifiant entreprise suisse (IDE = Identification des entreprises)
    'uid'          => env('BILLING_UID', ''),       // Format: CHE-XXX.XXX.XXX
    'vat_number'   => env('BILLING_VAT', ''),       // Si assujetti à la TVA

    // TVA : true = exonéré (CA < 100 000 CHF/an)
    // false = assujetti → taux 8.1% appliqué sur les factures
    'vat_exempt'   => env('BILLING_VAT_EXEMPT', true),
    'vat_rate_bp'  => env('BILLING_VAT_RATE', 810), // 810 = 8.10% en points de base

    // Conditions de paiement (jours)
    'payment_terms_days' => 30,

    // Seuil d'archivage des factures (mois)
    'archive_after_months' => 84, // 7 ans (obligation légale suisse)
];


