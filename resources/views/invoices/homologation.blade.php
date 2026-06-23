<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  *{box-sizing:border-box;margin:0;padding:0;}
  body{font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:10pt;color:#1A2230;background:#FCFCFA;}
  .topstripe{height:5px;background:linear-gradient(90deg,#0B2A4A 0%,#143a63 55%,#C8102E 100%);}

  /* Masthead */
  .masthead{display:flex;align-items:center;justify-content:space-between;padding:10mm 14mm 5mm 14mm;border-bottom:2px solid #0B2A4A;}
  .brand{display:flex;align-items:center;gap:10px;}
  .cross-logo{width:42px;height:42px;background:#C8102E;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
  .cross-logo svg{width:28px;height:28px;}
  .brand-name{font-size:16pt;font-weight:700;color:#0B2A4A;}
  .brand-name .tld{color:#C8102E;}
  .brand-tag{font-size:7pt;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:#5B6675;margin-top:3px;}
  .docref{text-align:right;font-size:8pt;}
  .docref .kind{font-size:11pt;font-weight:700;color:#0B2A4A;margin-bottom:4px;}
  .docref table{border-collapse:collapse;margin-left:auto;}
  .docref td{padding:2px 6px;border:0.5px solid #B7C0CD;}
  .docref td:first-child{color:#5B6675;font-size:7.5pt;}

  /* Titre */
  .titleband{padding:5mm 14mm 4mm;border-bottom:1px solid #B7C0CD;display:flex;align-items:baseline;justify-content:space-between;}
  .titleband h1{font-size:14pt;font-weight:700;color:#0B2A4A;}
  .titleband .subtitle{font-size:7.5pt;color:#5B6675;}

  /* Identité */
  .identity{margin:5mm 14mm;display:flex;justify-content:space-between;align-items:center;background:#f2f5fb;border:1px solid #B7C0CD;border-left:4px solid #0B2A4A;border-radius:3px;padding:8px 12px;}
  .identity .reflabel{font-size:7pt;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#C8102E;}
  .identity .tg{font-family:monospace;font-size:22pt;font-weight:700;color:#0B2A4A;letter-spacing:.06em;margin:2px 0;}
  .identity .vehline{font-size:11pt;font-weight:600;color:#1A2230;}
  .identity .vehline span{color:#5B6675;font-weight:400;}

  /* Sceau */
  .seal-area{text-align:center;padding:4px;}
  .seal-area .seal-text{font-size:6pt;font-weight:700;color:#0B2A4A;letter-spacing:.08em;text-transform:uppercase;margin-top:3px;}

  /* Corps */
  .body{padding:0 14mm;}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:0 9mm;margin-top:6mm;}

  .section-title{font-size:7.5pt;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#0B2A4A;border-bottom:1.5px solid #0B2A4A;padding-bottom:3px;margin-bottom:5px;}
  .section-title::before{content:'◆ ';}

  table.data{width:100%;border-collapse:collapse;font-size:8.5pt;}
  table.data tr{border-bottom:0.5px solid #DCE2EA;}
  table.data tr:last-child{border-bottom:none;}
  table.data td{padding:4px 0;vertical-align:baseline;}
  .fld-cell{width:44px;}
  .badge{display:inline-flex;flex-direction:column;align-items:center;}
  .ch{font-family:monospace;font-size:6.5pt;font-weight:700;color:#fff;background:#143a63;border-radius:2px 2px 0 0;padding:1px 3px;min-width:20px;text-align:center;display:block;}
  .eu{font-family:monospace;font-size:5.5pt;font-weight:700;color:#C8102E;background:#fdf0f1;border:0.5px solid #f5c0c4;border-top:none;border-radius:0 0 2px 2px;padding:0.5px 3px;display:block;min-width:20px;text-align:center;}
  .lbl{color:#5B6675;font-weight:500;padding-right:6px;}
  .val{text-align:right;font-weight:700;color:#1A2230;}
  .val .unit{color:#5B6675;font-weight:400;font-size:7.5pt;margin-left:2px;}
  .val .mono{font-family:monospace;font-size:8pt;}

  /* Publicité discrète */
  .ad-row td{padding:3px 0;border-top:0.5px dashed #DCE2EA !important;}
  .ad-pill{display:inline-block;border:0.5px solid #B7C0CD;border-radius:20px;padding:1px 7px;font-size:6.5pt;color:#5B6675;font-weight:500;}
  .ad-pill .ad-brand{font-weight:700;color:#0B2A4A;}
  .ad-pill .ad-tld{color:#0E7C86;}

  /* Pneumatiques */
  .tyres{margin-top:6mm;page-break-inside:avoid;}
  .tyre-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:5px;margin-top:5px;}
  .tyre{border:0.5px solid #B7C0CD;border-radius:3px;padding:5px 8px;display:flex;align-items:center;gap:7px;}
  .tyre .n{font-family:monospace;font-size:7pt;font-weight:700;color:#0B2A4A;border:0.5px solid #0B2A4A;border-radius:50%;width:14px;height:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
  .tyre .spec{font-family:monospace;font-size:9pt;font-weight:500;color:#0B2A4A;}
  .tyre.empty{background:repeating-linear-gradient(45deg,#fafbfc,#fafbfc 4px,#f1f3f6 4px,#f1f3f6 8px);}
  .tyre.empty .spec{color:#aeb6c2;font-style:italic;font-family:Helvetica,Arial,sans-serif;font-size:7.5pt;}
  .tyre.empty .n{color:#c2c9d3;border-color:#d4dae2;}
  .tyre-note{margin-top:4px;font-size:7pt;color:#5B6675;font-style:italic;}

  /* Pied de page */
  .footer{position:fixed;bottom:0;left:0;right:0;border-top:1.5px solid #0B2A4A;padding:5px 14mm 6px;display:flex;justify-content:space-between;align-items:center;background:#fff;}
  .footer .verify{font-size:6.5pt;color:#5B6675;line-height:1.5;}
  .footer .verify b{color:#0B2A4A;}
  .footer .verify .code{font-family:monospace;color:#C8102E;font-weight:600;}
  .footer .legal{font-size:6pt;color:#5B6675;text-align:right;max-width:260px;line-height:1.4;}
  .page-tag{font-family:monospace;font-size:6pt;color:#5B6675;letter-spacing:.06em;}
</style>
</head>
<body>

<div class="topstripe"></div>

<div class="masthead">
  <div class="brand">
    <div class="cross-logo">
      <svg viewBox="0 0 40 40" aria-hidden="true">
        <rect x="15" y="6"  width="10" height="28" rx="1.5" fill="white"/>
        <rect x="6"  y="15" width="28" height="10" rx="1.5" fill="white"/>
      </svg>
    </div>
    <div>
      <div class="brand-name">reception-par-type<span class="tld">.ch</span></div>
      <div class="brand-tag">Données techniques de réception par type · OFROU</div>
    </div>
  </div>
  <div class="docref">
    <div class="kind">Fiche d'homologation<br><span style="font-size:8pt;font-weight:400">Type Approval Data Sheet</span></div>
    <table>
      <tr><td>Document</td><td>RPT-{{ strtoupper(preg_replace('/[^a-z0-9]/i', '', $vehicle->numero_tg)) }}-{{ date('Y') }}</td></tr>
      <tr><td>Édition</td><td>{{ date('d.m.Y') }}</td></tr>
      <tr><td>Source</td><td>ASTRA / OFROU</td></tr>
    </table>
  </div>
</div>

<div class="titleband">
  <h1>Caractéristiques techniques du véhicule</h1>
  <span class="subtitle">Technische Fahrzeugdaten · Dati tecnici del veicolo</span>
</div>

<div class="identity">
  <div>
    <div class="reflabel">N° de réception par type</div>
    <div class="tg">{{ $vehicle->numero_tg }}</div>
    <div class="vehline">
      {{ $vehicle->marque }} {{ $vehicle->modele }}
      @if($vehicle->variante)<span> — {{ $vehicle->variante }}</span>@endif
      <span> · Genre : {{ $vehicle->vehicle_type === 'motorcycle' ? 'Motocycle' : ($vehicle->vehicle_type === 'trailer' ? 'Remorque' : 'Voiture de tourisme') }}</span>
    </div>
  </div>
  <div class="seal-area">
    <svg width="80" height="80" viewBox="0 0 120 120">
      <circle cx="60" cy="60" r="57" fill="none" stroke="#0B2A4A" stroke-width="2"/>
      <circle cx="60" cy="60" r="49" fill="none" stroke="#A9842E" stroke-width="1"/>
      <circle cx="60" cy="60" r="33" fill="#0B2A4A"/>
      <rect x="53" y="43" width="14" height="34" rx="2" fill="#fff"/>
      <rect x="43" y="53" width="34" height="14" rx="2" fill="#fff"/>
      <defs><path id="r" d="M60 60 m-43,0 a43,43 0 1,1 86,0 a43,43 0 1,1 -86,0"/></defs>
      <text font-size="6.5" font-weight="600" letter-spacing="2" fill="#0B2A4A" font-family="Helvetica,Arial">
        <textPath href="#r" startOffset="2%">DONNÉES VÉRIFIÉES · RECEPTION-PAR-TYPE.CH ·</textPath>
      </text>
      <text x="60" y="85" text-anchor="middle" font-size="6" fill="#A9842E" font-family="monospace" letter-spacing="1">CH · OFROU</text>
    </svg>
    <div class="seal-text">Données vérifiées</div>
  </div>
</div>

<div class="body">
  <div class="grid2">

    {{-- Identification --}}
    <div>
      <div class="section-title">Identification</div>
      <table class="data">
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">04</span><span class="eu">D.1</span></div></td>
          <td class="lbl">Marque</td>
          <td class="val">{{ $vehicle->marque }}</td>
        </tr>
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">04</span><span class="eu">D.2</span></div></td>
          <td class="lbl">Type</td>
          <td class="val">{{ $vehicle->modele }}{{ $vehicle->variante ? ' ' . $vehicle->variante : '' }}</td>
        </tr>
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">—</span><span class="eu">J</span></div></td>
          <td class="lbl">Genre</td>
          <td class="val">{{ $vehicle->vehicle_type === 'motorcycle' ? 'Motocycle' : ($vehicle->vehicle_type === 'trailer' ? 'Remorque' : 'Voiture de tourisme') }}</td>
        </tr>
        @if($vehicle->vin_prefix)
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">06</span><span class="eu">E</span></div></td>
          <td class="lbl">Préfixe VIN</td>
          <td class="val"><span class="mono">{{ $vehicle->vin_prefix }}</span></td>
        </tr>
        @endif
        @if($vehicle->eu_type_approval)
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">09</span><span class="eu">K</span></div></td>
          <td class="lbl">Réception CE</td>
          <td class="val"><span class="mono">{{ $vehicle->eu_type_approval }}</span></td>
        </tr>
        @endif
        {{-- Publicité discrète homologations.eu --}}
        <tr class="ad-row">
          <td class="fld-cell"><div class="badge"><span class="ch" style="background:#e8ecf0;color:#5B6675">↗</span></div></td>
          <td class="lbl" style="font-size:7pt;">Vérifier &amp; COC</td>
          <td class="val">
            <span class="ad-pill"><span class="ad-brand">homologations<span class="ad-tld">.eu</span></span> · Commandez vos COC</span>
          </td>
        </tr>
      </table>
    </div>

    {{-- Motorisation --}}
    <div>
      <div class="section-title">Motorisation</div>
      <table class="data">
        @if($vehicle->energie)
        @php
          // Codes énergie ASTRA → libellés lisibles
          $energieLabels = [
            '01' => 'Essence', '02' => 'Diesel', '03' => 'Gaz naturel',
            '04' => 'GPL',     '05' => 'Éthanol', '06' => 'Biodiesel',
            '07' => 'Hydrogène', '08' => 'Hybride essence', '09' => 'Hybride diesel',
            '10' => 'Hybride gaz', '11' => 'Autre', '12' => 'Multi-carburant',
            '13' => 'Hybride rechargeable', '14' => 'Électrique',
            '15' => 'Pile à combustible', '16' => 'Hybride électrique',
          ];
          $energieLabel = $energieLabels[$vehicle->energie] ?? 'Code ' . $vehicle->energie;
        @endphp
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">26</span><span class="eu">P.3</span></div></td>
          <td class="lbl">Carburant / Énergie</td>
          <td class="val">{{ $energieLabel }}</td>
        </tr>
        @endif
        @if($vehicle->cylindree !== null)
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">27</span><span class="eu">P.1</span></div></td>
          <td class="lbl">Cylindrée</td>
          <td class="val">{{ number_format($vehicle->cylindree, 0, '.', ' ') }}<span class="unit">cm³</span></td>
        </tr>
        @endif
        @if($vehicle->puissance_kw !== null)
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">28</span><span class="eu">P.2</span></div></td>
          <td class="lbl">Puissance</td>
          <td class="val">{{ $vehicle->puissance_kw }}<span class="unit">kW</span></td>
        </tr>
        @if($vehicle->puissance_cv)
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">≈</span><span class="eu">P.6</span></div></td>
          <td class="lbl">Puissance DIN</td>
          <td class="val">{{ $vehicle->puissance_cv }}<span class="unit">ch</span></td>
        </tr>
        @endif
        @endif
      </table>
    </div>

    {{-- Masses --}}
    <div style="margin-top:5mm;">
      <div class="section-title">Masses</div>
      <table class="data">
        @if($vehicle->poids_vide !== null)
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">52</span><span class="eu">G</span></div></td>
          <td class="lbl">Poids à vide — de</td>
          <td class="val">{{ number_format($vehicle->poids_vide, 0, '.', ' ') }}<span class="unit">kg</span></td>
        </tr>
        @endif
        @if($vehicle->poids_total !== null)
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">53</span><span class="eu">F.1</span></div></td>
          <td class="lbl">Poids garanti — de</td>
          <td class="val">{{ number_format($vehicle->poids_total, 0, '.', ' ') }}<span class="unit">kg</span></td>
        </tr>
        @endif
        @if($vehicle->poids_remorquable !== null)
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">—</span></div></td>
          <td class="lbl">Charge remorquable</td>
          <td class="val">{{ number_format($vehicle->poids_remorquable, 0, '.', ' ') }}<span class="unit">kg</span></td>
        </tr>
        @endif
        @if($vehicle->nb_trous && $vehicle->entraxe)
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">—</span></div></td>
          <td class="lbl">Jantes (trous × PCD)</td>
          <td class="val">{{ $vehicle->nb_trous }} × {{ $vehicle->entraxe }}<span class="unit">mm</span></td>
        </tr>
        @endif
        @if($vehicle->deport_et !== null)
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">—</span></div></td>
          <td class="lbl">Déport (ET)</td>
          <td class="val">{{ $vehicle->deport_et }}<span class="unit">mm</span></td>
        </tr>
        @endif
      </table>
    </div>

    {{-- Émissions --}}
    <div style="margin-top:5mm;">
      <div class="section-title">Émissions &amp; bruit</div>
      <table class="data">
        @if($vehicle->code_emissions)
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">EC</span><span class="eu">V.9</span></div></td>
          <td class="lbl">Code émission</td>
          <td class="val"><span class="mono">{{ $vehicle->code_emissions }}</span></td>
        </tr>
        @endif
        @if($vehicle->pollution_norm)
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">—</span></div></td>
          <td class="lbl">Norme antipollution</td>
          <td class="val"><span class="mono" style="font-size:7.5pt">{{ $vehicle->pollution_norm }}</span></td>
        </tr>
        @endif
        @if($vehicle->co2 !== null)
        <tr>
          <td class="fld-cell"><div class="badge"><span class="ch">CO₂</span><span class="eu">V.7</span></div></td>
          <td class="lbl">Émissions CO₂</td>
          <td class="val">{{ $vehicle->co2 }}<span class="unit">g/km</span></td>
        </tr>
        @endif
      </table>
    </div>

  </div>

  {{-- Pneumatiques --}}
  <div class="tyres">
    <div class="section-title">Pneumatiques — montes autorisées</div>
    <div class="tyre-grid">
      @php
        // Le modèle Vehicle stocke les pneus dans un seul champ pneus_origine.
        // On tente de séparer les montes multiples (séparateurs ASTRA courants).
        $rawPneus = $vehicle->pneus_origine ?? '';
        $pneus = [];
        if ($rawPneus) {
            // Séparation sur virgule/point-virgule suivis d'un format de pneu
            $parts = preg_split('/[,;]\s*(?=\d{3}\/|P\d{3}\/)/', $rawPneus);
            foreach ($parts as $p) {
                $t = trim($p);
                if ($t !== '') { $pneus[] = $t; }
            }
            // Si aucune séparation trouvée, afficher comme une seule monte
            if (empty($pneus) && $rawPneus) { $pneus[] = $rawPneus; }
        }
        while (count($pneus) < 6) { $pneus[] = null; }
      @endphp
      @for($i = 0; $i < 6; $i++)
        <div class="tyre{{ !$pneus[$i] ? ' empty' : '' }}">
          <div class="n">{{ $i + 1 }}</div>
          <div class="spec">{{ $pneus[$i] ?: 'non renseigné' }}</div>
        </div>
      @endfor
    </div>
    <p class="tyre-note">Seules les dimensions homologuées figurant dans la réception par type sont autorisées. Toute autre monte requiert une expertise.</p>
  </div>
</div>

<div class="footer">
  <div class="verify">
    <b>Vérifier l'authenticité :</b> reception-par-type.ch/verify · réf. <span class="code">{{ $vehicle->numero_tg }}</span><br>
    Émis le {{ date('d.m.Y') }} · {{ date('H:i') }} · {{ config('app.url') }}
  </div>
  <div class="legal">
    Document d'information établi à partir des données de réception par type publiées par l'OFROU / ASTRA.<br>
    Ne constitue pas le certificat officiel de réception par type. Données sans garantie d'exhaustivité.
  </div>
</div>

</body>
</html>
