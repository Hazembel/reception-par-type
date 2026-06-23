{{--
    Vue : resources/views/admin/dashboard.blade.php — thème PrestaShop 8.
--}}
@extends('admin.layouts.prestashop')

@section('page_title', 'Tableau de bord')
@section('title_icon', '📊')

@section('content')

{{-- KPI ROW --}}
<div class="ps-kpi-row">
    <div class="ps-kpi">
        <span class="ps-kpi-label">CA mensuel estimé</span>
        <span class="ps-kpi-value">{{ number_format($business['monthly_revenue_chf'], 0, '.', '\'') }}<span style="font-size:14px;color:var(--ps-text-muted)"> CHF</span></span>
        <span class="ps-kpi-sub">{{ $business['active_subscribers'] }} abonnés actifs</span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Nouveaux abonnements</span>
        <span class="ps-kpi-value green">+{{ $business['new_this_month'] }}</span>
        <span class="ps-kpi-sub">ce mois-ci</span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Déblocages / jetons</span>
        <span class="ps-kpi-value cyan">{{ number_format($business['token_unlocks_month'], 0, '.', '\'') }}</span>
        <span class="ps-kpi-sub">consultations payantes</span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Appels API facturés</span>
        <span class="ps-kpi-value">{{ number_format($business['api_calls_this_month'], 0, '.', '\'') }}</span>
        <span class="ps-kpi-sub">ce mois-ci</span>
    </div>
</div>

<div class="ps-grid-2">
    {{-- CLIENTS --}}
    <div class="ps-panel">
        <div class="ps-panel-header"><span class="ps-icon">👥</span> Clients</div>
        <div class="ps-panel-body flush">
            <table class="ps-table">
                <tbody>
                    @foreach([
                        ['Inscrits au total',   number_format($users['total'], 0, '.', '\''), ''],
                        ['E-mails vérifiés',     $users['verified'] . ' (' . $users['verified_pct'] . '%)', 'success'],
                        ['E-mails non vérifiés', $users['unverified'], $users['unverified'] > 0 ? 'warning' : ''],
                        ['Nouveaux ce mois',     '+' . $users['new_this_month'], 'info'],
                        ['Actifs (30 jours)',     number_format($users['recently_active'], 0, '.', '\''), ''],
                    ] as $row)
                    <tr>
                        <td style="color:var(--ps-text-muted)">{{ $row[0] }}</td>
                        <td style="text-align:right" class="num">
                            @if($row[2])
                                <span class="ps-badge ps-badge-{{ $row[2] }}">{{ $row[1] }}</span>
                            @else
                                <strong>{{ $row[1] }}</strong>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- SYSTÈME --}}
    <div class="ps-panel">
        <div class="ps-panel-header"><span class="ps-icon">⚙️</span> Système &amp; Imports</div>
        <div class="ps-panel-body flush">
            <table class="ps-table">
                <tbody>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Fiches ASTRA en base</td>
                        <td style="text-align:right" class="num"><strong>{{ number_format($system['total_vehicles'], 0, '.', '\'') }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Fiches actives</td>
                        <td style="text-align:right" class="num"><span class="ps-badge ps-badge-success">{{ number_format($system['active_vehicles'], 0, '.', '\'') }}</span></td>
                    </tr>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Dernier import 2000</td>
                        <td style="text-align:right">
                            @if($system['last_import_2000'])
                                @php $s = $system['last_import_2000']['status']; @endphp
                                <span class="ps-badge ps-badge-{{ $s === 'completed' ? 'success' : ($s === 'running' ? 'info' : ($s === 'partial' ? 'warning' : 'danger')) }}">{{ strtoupper($s) }}</span>
                                <div class="ps-help" style="margin-top:3px">{{ $system['last_import_2000']['date'] }} · +{{ number_format($system['last_import_2000']['inserted'], 0, '.', '\'') }}</div>
                            @else
                                <span class="ps-badge ps-badge-muted">JAMAIS</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Dernier import 5000</td>
                        <td style="text-align:right">
                            @if($system['last_import_5000'])
                                @php $s = $system['last_import_5000']['status']; @endphp
                                <span class="ps-badge ps-badge-{{ $s === 'completed' ? 'success' : ($s === 'running' ? 'info' : 'warning') }}">{{ strtoupper($s) }}</span>
                                <div class="ps-help" style="margin-top:3px">{{ $system['last_import_5000']['date'] }}</div>
                            @else
                                <span class="ps-badge ps-badge-muted">JAMAIS</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Imports échoués (7j)</td>
                        <td style="text-align:right">
                            <span class="ps-badge ps-badge-{{ $system['failed_recent'] > 0 ? 'danger' : 'success' }}">
                                {{ $system['failed_recent'] === 0 ? '0' : $system['failed_recent'] }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- SÉCURITÉ --}}
<div class="ps-panel">
    <div class="ps-panel-header">
        <span class="ps-icon">🔒</span> Sécurité — aujourd'hui
        <div class="ps-panel-tools">
            <span class="ps-badge ps-badge-{{ $security['alert_level'] === 'red' ? 'danger' : ($security['alert_level'] === 'amber' ? 'warning' : 'success') }}">
                {{ strtoupper($security['alert_level']) }}
            </span>
        </div>
    </div>
    <div class="ps-panel-body">
        <div class="ps-grid-4">
            @foreach([
                ['Rate Limits (429)',   $security['rate_limited_today'],  $security['rate_limited_today'] > 100],
                ['Auth invalides (401)', $security['unauthorized_today'], $security['unauthorized_today'] > 50],
                ['Taux d\'erreur API',  $security['error_rate_pct'] . '%', $security['error_rate_pct'] > 5],
                ['Anomalies en attente', $security['pending_anomalies'],  $security['pending_anomalies'] > 0],
            ] as $stat)
            <div style="padding:14px;border:1px solid {{ $stat[2] ? 'var(--ps-warning)' : 'var(--ps-border)' }};border-radius:4px;">
                <div class="ps-kpi-label">{{ $stat[0] }}</div>
                <div style="font-size:24px;font-weight:700;margin-top:4px;color:{{ $stat[2] ? 'var(--ps-warning)' : 'var(--ps-text)' }}" class="num">
                    {{ is_numeric($stat[1]) ? number_format($stat[1], 0, '.', '\'') : $stat[1] }}
                </div>
            </div>
            @endforeach
        </div>

        @if(!empty($security['suspicious_ips']))
        <div class="ps-alert ps-alert-danger" style="margin-top:16px;margin-bottom:0;align-items:flex-start;flex-direction:column;gap:8px">
            <strong>IPs suspectes (&gt;100 erreurs 429 aujourd'hui)</strong>
            @foreach($security['suspicious_ips'] as $ip)
            <div style="display:flex;justify-content:space-between;width:100%;font-size:12.5px">
                <code>{{ $ip['ip'] }}</code>
                <strong>{{ number_format($ip['count'], 0, '.', '\'') }} tentatives</strong>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

@endsection
