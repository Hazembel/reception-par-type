<!DOCTYPE html>
<html lang="fr">
{{--
    Facture PDF suisse — reception-par-type.ch
    Rendu via dompdf. Pas de Tailwind : CSS inline uniquement.
    Standards suisses :
     - Numéro séquentiel unique (RPT-YYYY-NNNNN)
     - Coordonnées complètes vendeur + IDE/UID
     - TVA : taux ou mention exonéré (art. 10 LTVA)
     - Montants en CHF avec séparateur apostrophe
     - QR-facture (placeholder — implémentation complète via swissqr lib)
--}}
<head>
<meta charset="UTF-8"/>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: Arial, Helvetica, sans-serif; font-size: 9pt; color: #1a1a1a; background: #fff; }
  .page { padding: 18mm 18mm 20mm 25mm; min-height: 297mm; position: relative; }

  /* En-tête */
  .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12mm; }
  .logo-zone h1 { font-size: 16pt; font-weight: bold; color: #2563EB; letter-spacing: -0.5px; }
  .logo-zone p  { font-size: 8pt; color: #64748B; margin-top: 1mm; }
  .doc-type { text-align: right; }
  .doc-type .title  { font-size: 18pt; font-weight: bold; color: #0A0F1E; }
  .doc-type .number { font-size: 10pt; color: #2563EB; font-weight: bold; margin-top: 1mm; }
  .doc-type .date   { font-size: 8pt; color: #64748B; margin-top: 1mm; }

  /* Adresses */
  .addresses { display: flex; justify-content: space-between; margin-bottom: 12mm; }
  .address-block { width: 48%; }
  .address-block .label { font-size: 7pt; text-transform: uppercase; letter-spacing: 0.8px; color: #94A3B8; margin-bottom: 2mm; font-weight: bold; }
  .address-block p { font-size: 9pt; line-height: 1.6; }
  .address-block .company { font-weight: bold; font-size: 10pt; }

  /* Lignes de facturation */
  .items-table { width: 100%; border-collapse: collapse; margin-bottom: 8mm; }
  .items-table thead tr { background-color: #0A0F1E; color: #fff; }
  .items-table thead th { padding: 2.5mm 3mm; text-align: left; font-size: 8pt; font-weight: normal; }
  .items-table thead th.right { text-align: right; }
  .items-table tbody tr { border-bottom: 0.3mm solid #E2E8F0; }
  .items-table tbody td { padding: 3mm 3mm; font-size: 9pt; vertical-align: top; }
  .items-table tbody td.right { text-align: right; }
  .items-table tbody tr:nth-child(even) { background: #F8FAFC; }

  /* Totaux */
  .totals { display: flex; justify-content: flex-end; margin-bottom: 10mm; }
  .totals-box { width: 75mm; }
  .totals-row { display: flex; justify-content: space-between; padding: 1.5mm 0; font-size: 9pt; border-bottom: 0.2mm solid #E2E8F0; }
  .totals-row.total { font-weight: bold; font-size: 11pt; border-bottom: 0.5mm solid #0A0F1E; border-top: 0.5mm solid #0A0F1E; padding: 2.5mm 0; }
  .totals-row .label { color: #64748B; }
  .totals-row .value { font-family: 'Courier New', monospace; }

  /* Notes et conditions */
  .notes { font-size: 8pt; color: #64748B; line-height: 1.6; margin-bottom: 8mm; padding: 3mm; background: #F8FAFC; border-left: 1mm solid #2563EB; }

  /* Pied de page */
  .footer { position: absolute; bottom: 8mm; left: 25mm; right: 18mm;
            border-top: 0.3mm solid #CBD5E1; padding-top: 3mm;
            display: flex; justify-content: space-between; }
  .footer p { font-size: 7pt; color: #94A3B8; }

  /* Bande décorative */
  .accent-bar { height: 1.5mm; background: linear-gradient(to right, #2563EB, #38BDF8); margin-bottom: 8mm; }

  .paypal-ref { font-family: 'Courier New', monospace; font-size: 8pt; color: #64748B; }
</style>
</head>
<body>
<div class="page">

  {{-- Bande bleue --}}
  <div class="accent-bar"></div>

  {{-- En-tête --}}
  <div class="header">
    <div class="logo-zone">
      <h1>réception-par-type.ch</h1>
      <p>Données techniques automobiles officielles ASTRA</p>
    </div>
    <div class="doc-type">
      <div class="title">FACTURE</div>
      <div class="number">{{ $invoice->invoice_number }}</div>
      <div class="date">Émise le {{ $invoice->issued_at->format('d.m.Y') }}</div>
      @if($invoice->due_at)
      <div class="date">Échéance : {{ $invoice->due_at->format('d.m.Y') }}</div>
      @endif
    </div>
  </div>

  {{-- Adresses --}}
  <div class="addresses">
    {{-- Vendeur --}}
    <div class="address-block">
      <div class="label">Vendeur / Fournisseur</div>
      <p class="company">{{ config('billing.company_name', 'reception-par-type.ch Sàrl') }}</p>
      <p>{{ config('billing.address_line1', 'Rue de la Paix 1') }}</p>
      <p>{{ config('billing.postal', 'CH-1000') }} {{ config('billing.city', 'Lausanne') }}</p>
      <p>Suisse</p>
      @if(config('billing.uid'))
      <p style="margin-top:2mm;font-size:8pt;color:#64748B">
        IDE/UID : {{ config('billing.uid') }}
      </p>
      @endif
      @if(!config('billing.vat_exempt', true) && config('billing.vat_number'))
      <p style="font-size:8pt;color:#64748B">
        N° TVA : {{ config('billing.vat_number') }}
      </p>
      @endif
    </div>

    {{-- Acheteur --}}
    <div class="address-block">
      <div class="label">Acheteur / Destinataire</div>
      @php $addr = $invoice->billing_address; @endphp
      <p class="company">{{ $addr['name'] ?? '—' }}</p>
      @if(!empty($addr['line1']))<p>{{ $addr['line1'] }}</p>@endif
      @if(!empty($addr['line2']))<p>{{ $addr['line2'] }}</p>@endif
      <p>{{ $addr['postal'] ?? '' }} {{ $addr['city'] ?? '' }}</p>
      <p>{{ $addr['country'] ?? 'CH' }}</p>
      @if(!empty($addr['email']))
      <p style="margin-top:1mm;font-size:8pt;color:#64748B">{{ $addr['email'] }}</p>
      @endif
    </div>
  </div>

  {{-- Référence PayPal --}}
  <p style="margin-bottom:6mm;font-size:8pt;color:#64748B">
    Référence paiement PayPal :
    <span class="paypal-ref">{{ $invoice->paypal_order_id }}</span>
  </p>

  {{-- Tableau des prestations --}}
  <table class="items-table">
    <thead>
      <tr>
        <th style="width:55%">Description</th>
        <th class="right" style="width:10%">Qté</th>
        <th class="right" style="width:17%">Prix unit. CHF</th>
        <th class="right" style="width:18%">Total CHF</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->line_items as $item)
      <tr>
        <td>{{ $item['description'] }}</td>
        <td class="right">{{ $item['qty'] }}</td>
        <td class="right">{{ $invoice->formatChf($item['unit_cts']) }}</td>
        <td class="right">{{ $invoice->formatChf($item['total_cts']) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- Totaux --}}
  <div class="totals">
    <div class="totals-box">
      <div class="totals-row">
        <span class="label">Sous-total HT</span>
        <span class="value">CHF {{ $invoice->formatChf($invoice->subtotal_cts) }}</span>
      </div>

      @if($invoice->is_vat_exempt)
        <div class="totals-row">
          <span class="label">TVA (art. 10 LTVA)</span>
          <span class="value" style="color:#64748B;font-size:8pt">Exonéré</span>
        </div>
      @else
        <div class="totals-row">
          <span class="label">TVA {{ $invoice->tax_rate_pct }}%</span>
          <span class="value">CHF {{ $invoice->formatChf($invoice->tax_amount_cts) }}</span>
        </div>
      @endif

      <div class="totals-row total">
        <span>Total dû</span>
        <span class="value">CHF {{ $invoice->formatChf($invoice->total_cts) }}</span>
      </div>
    </div>
  </div>

  {{-- Notes --}}
  <div class="notes">
    @if($invoice->is_vat_exempt)
      <strong>Mention TVA :</strong> Prestation exonérée de TVA conformément à l'art. 10 al. 2 let. a LTVA
      (chiffre d'affaires annuel inférieur à CHF 100'000).
    @else
      TVA suisse au taux normal de {{ $invoice->tax_rate_pct }}% applicable.
    @endif
    <br/>
    <strong>Conditions de paiement :</strong> Paiement effectué via PayPal. Facture payée.
    <br/>
    <strong>Service fourni :</strong> Accès aux données techniques homologuées de l'OFROU (ASTRA) via la
    plateforme reception-par-type.ch. Service immatériel — aucun retour possible après activation.
  </div>

  {{-- Pied de page --}}
  <div class="footer">
    <p>{{ config('billing.company_name', 'reception-par-type.ch Sàrl') }} · {{ config('billing.address_line1') }} · {{ config('billing.postal') }} {{ config('billing.city') }} · Suisse</p>
    <p>{{ $invoice->invoice_number }} · {{ $invoice->issued_at->format('d.m.Y') }}</p>
  </div>

</div>
</body>
</html>
