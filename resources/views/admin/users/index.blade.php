{{-- Vue : admin/users/index — Liste clients --}}
@extends('admin.layouts.prestashop')

@section('page_title', $level === '8' ? 'Administrateurs' : 'Clients')
@section('title_icon', $level === '8' ? '🔑' : '👥')
@section('breadcrumb')<span>{{ $level === '8' ? 'Administrateurs' : 'Clients' }}</span>@endsection

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

{{-- Créer un utilisateur (Alpine collapsible) --}}
@if($level !== '8')
<div x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }" class="ps-panel">
    <div class="ps-panel-header" style="cursor:pointer;user-select:none" @click="open = !open">
        <span class="ps-icon">➕</span> Créer un utilisateur
        <span class="ps-help" style="margin-left:8px;font-weight:400" x-text="open ? '▲ fermer' : '▼ ouvrir'"></span>
    </div>
    <div class="ps-panel-body" x-show="open" x-transition style="display:none">
        @if($errors->any())
        <div class="ps-alert ps-alert-danger" style="margin-bottom:14px">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="ps-grid-2" style="margin-bottom:14px">
                <div class="ps-form-group">
                    <label class="ps-label">Nom complet <span style="color:var(--ps-danger)">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="ps-input" placeholder="Jean Dupont" required>
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Adresse e-mail <span style="color:var(--ps-danger)">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="ps-input" placeholder="jean@exemple.ch" required>
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Mot de passe <span style="color:var(--ps-danger)">*</span> <span class="ps-help">(min. 8 car.)</span></label>
                    <input type="password" name="password" class="ps-input" placeholder="••••••••" required>
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Niveau d'abonnement</label>
                    <select name="subscription_level" class="ps-select">
                        @foreach($plans as $plan)
                            @if($plan->level < 8)
                                <option value="{{ $plan->level }}" {{ old('subscription_level', 1) == $plan->level ? 'selected' : '' }}>
                                    Niveau {{ $plan->level }} — {{ $plan->name_fr }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <p class="ps-help" style="margin-bottom:12px">✅ Compte créé avec e-mail <strong>pré-vérifié</strong> — aucun mail envoyé.</p>
            <button type="submit" class="ps-btn ps-btn-primary">Créer le compte</button>
        </form>
    </div>
</div>
@endif

{{-- Filtres --}}
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

{{-- Table --}}
<div class="ps-panel">
    <div class="ps-panel-header">
        <span class="ps-icon">📋</span> {{ $level === '8' ? 'Liste des administrateurs' : 'Liste des clients' }}
        <div class="ps-panel-tools"><span class="ps-badge ps-badge-muted">{{ $users->total() }} résultat(s)</span></div>
    </div>
    <div class="ps-panel-body flush">
        <table class="ps-table">
            <thead>
                <tr>
                    @foreach([['name','Nom'],['email','E-mail'],['subscription_level','Niveau'],['web_tokens_balance','Jetons'],['created_at','Inscription']] as $th)
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => $th[0], 'dir' => $sort === $th[0] && $dir === 'asc' ? 'desc' : 'asc']) }}" style="color:inherit">
                            {{ $th[1] }}@if($sort === $th[0]){{ $dir === 'asc' ? ' ▲' : ' ▼' }}@endif
                        </a>
                    </th>
                    @endforeach
                    <th>Statut</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr style="{{ $user->deleted_at ? 'opacity:.5;background:#fff8f8' : '' }}">
                    <td><strong>{{ $user->name }}</strong></td>
                    <td style="color:var(--ps-text-muted)"><code>{{ $user->email }}</code></td>
                    <td>
                        <span class="ps-badge ps-badge-{{ $user->subscription_level >= 6 ? 'info' : ($user->subscription_level >= 3 ? 'success' : 'muted') }}">
                            L{{ $user->subscription_level }} · {{ $plans[$user->subscription_level]->name_fr ?? '?' }}
                        </span>
                    </td>
                    <td class="num">{{ number_format($user->web_tokens_balance, 0, '.', '\'') }}</td>
                    <td style="color:var(--ps-text-muted)">{{ $user->created_at->format('d.m.Y') }}</td>
                    <td style="white-space:nowrap">
                        @if($user->email_verified_at)
                            <span class="ps-badge ps-badge-success">Vérifié</span>
                        @else
                            <span class="ps-badge ps-badge-warning">Non vérifié</span>
                        @endif
                        @if($user->deleted_at)
                            <span class="ps-badge ps-badge-danger">Supprimé</span>
                        @endif
                    </td>
                    <td style="text-align:right;white-space:nowrap">
                        @if($user->deleted_at)
                            {{-- Restaurer --}}
                            <form method="POST" action="{{ route('admin.users.restore', $user->id) }}" style="display:inline"
                                  x-data
                                  @submit.prevent="if(confirm('Restaurer le compte de {{ addslashes($user->name) }} ?')) $el.submit()">
                                @csrf
                                <button type="submit" class="ps-btn ps-btn-sm ps-btn-success">↩ Restaurer</button>
                            </form>
                        @else
                            <a href="{{ route('admin.users.show', $user) }}" class="ps-btn ps-btn-sm">Modifier</a>
                            @if($user->id !== auth()->id() && !$user->isAdmin())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:inline"
                                  x-data
                                  @submit.prevent="if(confirm('Supprimer le compte de {{ addslashes($user->name) }} ?\n(soft delete — récupérable)')) $el.submit()">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ps-btn ps-btn-sm ps-btn-danger">Supprimer</button>
                            </form>
                            @endif
                        @endif
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
