{{-- Vue : admin/users/show — Fiche client + actions cadeaux (PrestaShop) --}}
@extends('admin.layouts.prestashop')

@section('page_title', $user->name)
@section('title_icon', '👤')
@section('breadcrumb')
    <a href="{{ route('admin.users.index') }}">Clients</a><span class="sep">›</span><span>{{ $user->name }}</span>
@endsection

@section('content')

{{-- Profil --}}
<div class="ps-panel">
    <div class="ps-panel-header">
        <span class="ps-icon">👤</span> {{ $user->name }}
        <div class="ps-panel-tools">
            <span class="ps-badge ps-badge-{{ $user->subscription_level >= 6 ? 'info' : ($user->subscription_level >= 3 ? 'success' : 'muted') }}">
                Niveau {{ $user->subscription_level }} — {{ $plans->where('level', $user->subscription_level)->first()->name_fr ?? '?' }}
            </span>
        </div>
    </div>
    <div class="ps-panel-body">
        <p style="color:var(--ps-text-muted);margin-bottom:16px"><code>{{ $user->email }}</code></p>
        <div class="ps-grid-4">
            @foreach([
                ['Jetons', number_format($user->web_tokens_balance, 0, '.', '\'')],
                ['Consultations / mois', number_format($user->web_monthly_counter, 0, '.', '\'')],
                ['Abonnement jusqu\'au', $user->subscribed_until?->format('d.m.Y') ?? '—'],
                ['Déblocages ce mois', $unlockedCount],
            ] as $stat)
            <div style="padding:14px;background:#fafbfc;border:1px solid var(--ps-border);border-radius:4px;text-align:center">
                <div style="font-size:22px;font-weight:700" class="num">{{ $stat[1] }}</div>
                <div class="ps-help" style="margin-top:3px">{{ $stat[0] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Action : niveau --}}
<div class="ps-panel">
    <div class="ps-panel-header"><span class="ps-icon">⭐</span> Modifier le niveau d'abonnement</div>
    <div class="ps-panel-body">
        <form method="POST" action="{{ route('admin.users.grant', $user) }}">
            @csrf
            <input type="hidden" name="action" value="set_level">
            <div class="ps-grid-2">
                <div class="ps-form-group">
                    <label class="ps-label">Nouveau niveau</label>
                    <select name="subscription_level" class="ps-select">
                        @foreach($plans as $plan)
                            @if($plan->level < 8)
                                <option value="{{ $plan->level }}" {{ $user->subscription_level === $plan->level ? 'selected' : '' }}>
                                    Niveau {{ $plan->level }} — {{ $plan->name_fr }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Note <span class="ps-help">(journal d'audit)</span></label>
                    <input type="text" name="note" class="ps-input" placeholder="Ex : geste commercial">
                </div>
            </div>
            <button type="submit" class="ps-btn ps-btn-primary">Appliquer le niveau</button>
        </form>
    </div>
</div>

{{-- Action : jetons --}}
<div class="ps-panel">
    <div class="ps-panel-header"><span class="ps-icon">🎟️</span> Ajouter des jetons <span class="ps-help" style="font-weight:400">· solde : {{ number_format($user->web_tokens_balance, 0, '.', '\'') }}</span></div>
    <div class="ps-panel-body">
        <form method="POST" action="{{ route('admin.users.grant', $user) }}">
            @csrf
            <input type="hidden" name="action" value="add_tokens">
            <div class="ps-grid-2">
                <div class="ps-form-group">
                    <label class="ps-label">Nombre de jetons</label>
                    <input type="number" name="tokens_to_add" value="10" min="1" max="1000" class="ps-input num">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Note</label>
                    <input type="text" name="note" class="ps-input" placeholder="Raison">
                </div>
            </div>
            <button type="submit" class="ps-btn ps-btn-success">Créditer les jetons</button>
        </form>
    </div>
</div>

{{-- Action : abonnement --}}
<div class="ps-panel">
    <div class="ps-panel-header"><span class="ps-icon">📅</span> Prolonger l'abonnement <span class="ps-help" style="font-weight:400">· expire : {{ $user->subscribed_until?->format('d.m.Y') ?? 'aucun' }}</span></div>
    <div class="ps-panel-body">
        <form method="POST" action="{{ route('admin.users.grant', $user) }}">
            @csrf
            <input type="hidden" name="action" value="extend_subscription">
            <div class="ps-grid-2">
                <div class="ps-form-group">
                    <label class="ps-label">Durée</label>
                    <select name="extend_days" class="ps-select">
                        <option value="7">7 jours</option>
                        <option value="30" selected>30 jours (1 mois)</option>
                        <option value="90">90 jours (3 mois)</option>
                        <option value="365">365 jours (1 an)</option>
                    </select>
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Note</label>
                    <input type="text" name="note" class="ps-input" placeholder="Raison">
                </div>
            </div>
            <button type="submit" class="ps-btn ps-btn-primary">Prolonger</button>
        </form>
    </div>
</div>

{{-- Action : reset compteur --}}
<div class="ps-panel">
    <div class="ps-panel-header"><span class="ps-icon">♻️</span> Réinitialiser le compteur mensuel</div>
    <div class="ps-panel-body" style="display:flex;justify-content:space-between;align-items:center;gap:16px">
        <span class="ps-help">Compteur actuel : {{ number_format($user->web_monthly_counter, 0, '.', '\'') }} consultations</span>
        <form method="POST" action="{{ route('admin.users.grant', $user) }}" onsubmit="return confirm('Réinitialiser le compteur de {{ $user->name }} ?')">
            @csrf
            <input type="hidden" name="action" value="reset_counter">
            <input type="hidden" name="note" value="Reset manuel admin">
            <button type="submit" class="ps-btn">Réinitialiser</button>
        </form>
    </div>
</div>

{{-- Zone de danger --}}
@if($user->id !== auth()->id() && !$user->isAdmin())
<div class="ps-panel" style="border-color:#e74c3c22">
    <div class="ps-panel-header" style="color:#c0392b"><span class="ps-icon">🗑️</span> Zone de danger</div>
    <div class="ps-panel-body" style="display:flex;justify-content:space-between;align-items:center;gap:16px">
        <div>
            <strong style="color:#c0392b">Supprimer ce compte</strong>
            <p class="ps-help" style="margin-top:4px">Soft delete — le compte reste dans la base (récupérable depuis la liste).</p>
        </div>
        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
              onsubmit="return confirm('Supprimer définitivement le compte de {{ addslashes($user->name) }} ?\n\n(Soft delete — récupérable)')">
            @csrf
            @method('DELETE')
            <button type="submit" class="ps-btn ps-btn-danger">Supprimer le compte</button>
        </form>
    </div>
</div>
@endif

@endsection
