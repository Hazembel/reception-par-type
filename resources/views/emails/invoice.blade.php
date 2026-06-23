@component('mail::message')
# Votre facture {{ $invoice->invoice_number }}

Bonjour,

Nous vous remercions pour votre achat sur **{{ $companyName }}**.

Vous trouverez votre facture officielle en pièce jointe à cet e-mail (format PDF).

@component('mail::panel')
**Facture :** {{ $invoice->invoice_number }}
**Date :** {{ $invoice->issued_at->format('d.m.Y') }}
**Montant total :** CHF {{ number_format($invoice->total_cts / 100, 2, '.', '\'') }}
@endcomponent

@if($invoice->is_vat_exempt)
*Prestation exonérée de TVA conformément à l'art. 10 al. 2 let. a LTVA.*
@else
*TVA suisse incluse au taux de {{ number_format($invoice->tax_rate_bp / 100, 1) }}%.*
@endif

Vous pouvez également retrouver toutes vos factures dans votre espace personnel, rubrique **Mes factures**.

@component('mail::button', ['url' => config('app.url') . '/fr/account/invoices'])
Accéder à mes factures
@endcomponent

Pour toute question, contactez-nous à {{ $supportEmail }}.

Cordialement,
L'équipe {{ $companyName }}

@component('mail::subcopy')
Ce message est généré automatiquement suite à votre paiement. Merci de ne pas y répondre directement.
@endcomponent
@endcomponent
