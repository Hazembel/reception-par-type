{{-- Vue : admin/import/show — Détail d'un import ASTRA (PrestaShop) --}}
@extends('admin.layouts.prestashop')

@section('page_title', 'Import #' . $importLog->id)
@section('title_icon', '📄')
@section('breadcrumb')
    <a href="{{ route('admin.import.index') }}">Données ASTRA</a><span class="sep">›</span><span>Import #{{ $importLog->id }}</span>
@endsection

@section('content')

<div class="ps-panel">
    <div class="ps-panel-header">
        <span class="ps-icon">📄</span> Import #{{ $importLog->id }}
        <div class="ps-panel-tools">
            <span class="ps-badge ps-badge-{{ $importLog->status === 'completed' ? 'success' : ($importLog->status === 'running' ? 'info' : ($importLog->status === 'partial' ? 'warning' : 'danger')) }}">
                {{ strtoupper($importLog->status) }}
            </span>
        </div>
    </div>
    <div class="ps-panel-body flush">
        <table class="ps-table">
            <tbody>
                <tr><td style="color:var(--ps-text-muted);width:40%">Type</td><td><span class="ps-badge ps-badge-muted">{{ $importLog->import_type }}</span></td></tr>
                <tr><td style="color:var(--ps-text-muted)">Fichier</td><td><code>{{ $importLog->filename }}</code></td></tr>
                <tr><td style="color:var(--ps-text-muted)">Empreinte SHA-256</td><td><code style="font-size:11px">{{ \Illuminate\Support\Str::limit($importLog->file_hash, 32) }}</code></td></tr>
                <tr><td style="color:var(--ps-text-muted)">Taille</td><td class="num">{{ $importLog->file_size_bytes ? number_format($importLog->file_size_bytes / 1048576, 1, '.', '\'') . ' Mo' : '—' }}</td></tr>
                <tr><td style="color:var(--ps-text-muted)">Lignes totales</td><td class="num">{{ number_format($importLog->total_lines, 0, '.', '\'') }}</td></tr>
                <tr><td style="color:var(--ps-text-muted)">Insérées</td><td class="num"><span class="ps-badge ps-badge-success">+{{ number_format($importLog->lines_inserted, 0, '.', '\'') }}</span></td></tr>
                <tr><td style="color:var(--ps-text-muted)">Mises à jour</td><td class="num">{{ number_format($importLog->lines_updated, 0, '.', '\'') }}</td></tr>
                <tr><td style="color:var(--ps-text-muted)">Ignorées</td><td class="num">{{ number_format($importLog->lines_skipped, 0, '.', '\'') }}</td></tr>
                <tr><td style="color:var(--ps-text-muted)">Échouées</td><td class="num">{{ $importLog->lines_failed > 0 ? '⚠ ' . number_format($importLog->lines_failed, 0, '.', '\'') : '0' }}</td></tr>
                <tr><td style="color:var(--ps-text-muted)">Durée</td><td>{{ $importLog->duration_seconds ? round($importLog->duration_seconds / 60, 1) . ' min' : '—' }}</td></tr>
                <tr><td style="color:var(--ps-text-muted)">Démarré</td><td>{{ $importLog->started_at?->format('d.m.Y H:i:s') ?? '—' }}</td></tr>
                <tr><td style="color:var(--ps-text-muted)">Terminé</td><td>{{ $importLog->finished_at?->format('d.m.Y H:i:s') ?? '—' }}</td></tr>
            </tbody>
        </table>
    </div>
</div>

@if($importLog->error_message)
<div class="ps-panel">
    <div class="ps-panel-header"><span class="ps-icon">⚠️</span> Erreur</div>
    <div class="ps-panel-body">
        <div class="ps-alert ps-alert-danger" style="margin:0">{{ $importLog->error_message }}</div>
        @if($importLog->error_details)
        <pre style="margin-top:12px;background:#fafbfc;border:1px solid var(--ps-border);border-radius:4px;padding:12px;font-size:12px;overflow:auto;max-height:300px">{{ is_string($importLog->error_details) ? $importLog->error_details : json_encode($importLog->error_details, JSON_PRETTY_PRINT) }}</pre>
        @endif
    </div>
</div>
@endif

<div style="display:flex;gap:10px">
    <a href="{{ route('admin.import.index') }}" class="ps-btn">← Retour</a>
    @if(in_array($importLog->status, ['failed', 'partial']))
    <form method="POST" action="{{ route('admin.import.retry', $importLog) }}">
        @csrf
        <button type="submit" class="ps-btn ps-btn-primary">Rejouer cet import</button>
    </form>
    @endif
</div>

@endsection
