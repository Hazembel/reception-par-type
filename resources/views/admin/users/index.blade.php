{{-- Vue : admin/users/index — Liste clients (Helper List PrestaShop) --}}
@extends('admin.layouts.prestashop')

@section('page_title', 'Clients')
@section('title_icon', '👥')
@section('breadcrumb')<span>Clients</span>@endsection

@section('content')

{{-- KPI résumé --}}
<div class="ps-kpi-row">
    <div class="ps-kpi">
        <span class="ps-kpi-label">Total inscrits</span>
        <span class="ps-kpi-value">{{ number_format($stats['total'], 0, '.', '\'') }}</span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Vérifiés</span>
        <span class="ps-kpi-value green">{{ number_format($stats['verified'], 0, '.', '\'') }}</span>
    </div>
    <div class="ps-kpi">
        <span class="ps-kpi-label">Abonnements actifs</span>
        <span class="ps-kpi-value cyan">{{ number_format($stats['active'], 0, '.', '\'') }}</span>
    </div>
</div>

<div class="ps-panel">
    <div class="ps-panel-header"><span class="ps-icon">🔍</span> Filtrer</div>
    <div class="ps-panel-body">
        <form method="GET" action="{{ route('admin.users.index') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
            <div style="flex:1;min-width:200px">
                <label class="ps-label">Recherche <span class="ps-help">nom ou e-mail</span></label>
                <input type="text" name="q" value="{{ $search }}" class="ps-input" placeholder="Jean Dupont, jean@...">
            </div>
            <div style="width:220px">
                <label class="ps-label">Niveau</label>
                <select name="level" class="ps-select">
                    <option value="">Tous les niveaux</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->level }}" {{ (string)$level === (string)$plan->level ? 'selected' : '' }}>
                            Niveau {{ $plan->level }} — {{ $plan->name_fr }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="ps-btn ps-btn-primary">Rechercher</button>
            @if($search || $level)
                <a href="{{ route('admin.users.index') }}" class="ps-btn">Réinitialiser</a>
            @endif
        </form>
    </div>
</div>

<div class="ps-panel">
    <div class="ps-panel-header">
        <span class="ps-icon">📋</span> Liste des clients
        <div class="ps-panel-tools"><span class="ps-badge ps-badge-muted">{{ $users->total() }} résultat(s)</span></div>
    </div>
    <div class="ps-panel-body flush">
        <table class="ps-table">
            <thead>
                <tr>
                    @foreach([['name','Nom'],['email','E-mail'],['subscription_level','Niveau'],['web_tokens_balance','Jetons'],['created_at','Inscription']] as $th)
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => $th[0], 'dir' => $sort === $th[0] && $dir === 'asc' ? 'desc' : 'asc']) }}" style="color:inherit">
                            {{ $th[1] }}
                            @if($sort === $th[0]){{ $dir === 'asc' ? ' ▲' : ' ▼' }}@endif
                        </a>
                    </th>
                    @endforeach
                    <th>Statut</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td style="color:var(--ps-text-muted)"><code>{{ $user->email }}</code></td>
                    <td>
                        <span class="ps-badge ps-badge-{{ $user->subscription_level >= 6 ? 'info' : ($user->subscription_level >= 3 ? 'success' : 'muted') }}">
                            L{{ $user->subscription_level }} · {{ $plans[$user->subscription_level]->name_fr ?? '?' }}
                        </span>
                    </td>
                    <td class="num">{{ number_format($user->web_tokens_balance, 0, '.', '\'') }}</td>
                    <td style="color:var(--ps-text-muted)">{{ $user->created_at->format('d.m.Y') }}</td>
                    <td>
                        @if($user->email_verified_at)
                            <span class="ps-badge ps-badge-success">Vérifié</span>
                        @else
                            <span class="ps-badge ps-badge-warning">Non vérifié</span>
                        @endif
                        @if($user->deleted_at)<span class="ps-badge ps-badge-danger">Supprimé</span>@endif
                    </td>
                    <td style="text-align:right">
                        <a href="{{ route('admin.users.show', $user) }}" class="ps-btn ps-btn-sm">Modifier</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--ps-text-muted)">Aucun client trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div style="padding:14px 18px;border-top:1px solid var(--ps-border)">{{ $users->withQueryString()->links() }}</div>
    @endif
</div>

@endsection
