{{-- Vue : admin/import/index — Imports ASTRA (PrestaShop) --}}
@extends('admin.layouts.prestashop')

@section('page_title', 'Données ASTRA')
@section('title_icon', '⚡')
@section('breadcrumb')<span>Données ASTRA</span>@endsection

@section('content')

<div class="ps-kpi-row">
    <div class="ps-kpi">
        <span class="ps-kpi-label">Imports 2000 (mensuel)</span>
        <span class="ps-kpi-value">{{ $stats['total_2000'] }}</span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Imports 5000 (newsletter)</span>
        <span class="ps-kpi-value">{{ $stats['total_5000'] }}</span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">En cours</span>
        <span class="ps-kpi-value cyan">{{ $stats['running_jobs'] }}</span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Échoués (7j)</span>
        <span class="ps-kpi-value" style="color:{{ $stats['failed_recent'] > 0 ? 'var(--ps-danger)' : 'var(--ps-success)' }}">{{ $stats['failed_recent'] }}</span>
    </div>
</div>

<div class="ps-grid-2">
    {{-- Lancer un import --}}
    <div class="ps-panel">
        <div class="ps-panel-header"><span class="ps-icon">▶️</span> Lancer un import</div>
        <div class="ps-panel-body">
            <form method="POST" action="{{ route('admin.import.trigger') }}">
                @csrf
                <div class="ps-form-group">
                    <label class="ps-label">Type d'import</label>
                    <select name="type" class="ps-select">
                        <option value="newsletter">Newsletter 5000 (hebdomadaire, rapide)</option>
                        <option value="main">Fichier principal 2000 (mensuel, 1-3 h)</option>
                    </select>
                </div>
                <label class="ps-check" style="margin-bottom:14px">
                    <input type="hidden" name="force" value="0">
                    <input type="checkbox" name="force" value="1"> Forcer le retraitement (ignore le hash)
                </label>
                <button type="submit" class="ps-btn ps-btn-primary">Démarrer l'import</button>
            </form>
        </div>
    </div>

    {{-- État des fichiers --}}
    <div class="ps-panel">
        <div class="ps-panel-header"><span class="ps-icon">💾</span> État des fichiers sur disque</div>
        <div class="ps-panel-body flush">
            <table class="ps-table">
                <tbody>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Dossier 2000</td>
                        <td style="text-align:right"><span class="ps-badge ps-badge-{{ $diskStatus['dir_2000_exists'] ? 'success' : 'danger' }}">{{ $diskStatus['dir_2000_exists'] ? 'OK' : 'Absent' }}</span></td>
                    </tr>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Dossier 5000</td>
                        <td style="text-align:right"><span class="ps-badge ps-badge-{{ $diskStatus['dir_5000_exists'] ? 'success' : 'danger' }}">{{ $diskStatus['dir_5000_exists'] ? 'OK' : 'Absent' }}</span></td>
                    </tr>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Fichier principal</td>
                        <td style="text-align:right">
                            @if($diskStatus['main_file_exists'])
                                <span class="ps-badge ps-badge-success">{{ $diskStatus['main_file_size'] }}</span>
                                <div class="ps-help" style="margin-top:3px">{{ $diskStatus['main_file_modified'] }}</div>
                            @else
                                <span class="ps-badge ps-badge-warning">Absent</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="color:var(--ps-text-muted)">Fichiers newsletter</td>
                        <td style="text-align:right" class="num"><strong>{{ $diskStatus['newsletter_files'] }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Historique --}}
<div class="ps-panel">
    <div class="ps-panel-header"><span class="ps-icon">📜</span> Historique des imports</div>
    <div class="ps-panel-body flush">
        <table class="ps-table">
            <thead>
                <tr><th>#</th><th>Type</th><th>Fichier</th><th>Statut</th><th>Insérés</th><th>MAJ</th><th>Date</th><th style="text-align:right">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($recentImports as $log)
                <tr>
                    <td class="num">{{ $log->id }}</td>
                    <td><span class="ps-badge ps-badge-muted">{{ $log->import_type }}</span></td>
                    <td style="color:var(--ps-text-muted)"><code>{{ \Illuminate\Support\Str::limit($log->filename, 28) }}</code></td>
                    <td>
                        <span class="ps-badge ps-badge-{{ $log->status === 'completed' ? 'success' : ($log->status === 'running' ? 'info' : ($log->status === 'partial' ? 'warning' : 'danger')) }}">{{ strtoupper($log->status) }}</span>
                    </td>
                    <td class="num">{{ number_format($log->lines_inserted, 0, '.', '\'') }}</td>
                    <td class="num">{{ number_format($log->lines_updated, 0, '.', '\'') }}</td>
                    <td style="color:var(--ps-text-muted)">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                    <td style="text-align:right">
                        @if(in_array($log->status, ['failed', 'partial']))
                        <form method="POST" action="{{ route('admin.import.retry', $log) }}" style="display:inline">
                            @csrf
                            <button type="submit" class="ps-btn ps-btn-sm">Rejouer</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:32px;color:var(--ps-text-muted)">Aucun import enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($recentImports->hasPages())
    <div style="padding:14px 18px;border-top:1px solid var(--ps-border)">{{ $recentImports->links() }}</div>
    @endif
</div>

@endsection
