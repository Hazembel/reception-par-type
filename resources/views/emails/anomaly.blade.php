@component('mail::message')
# Nouveau signalement d'anomalie

Un visiteur a signalé une anomalie sur une fiche technique.

@component('mail::panel')
**Référence :** #{{ $report->id }}
**N° TG concerné :** {{ $report->numero_tg ?? '—' }}
**Champ signalé :** {{ $report->field_reported ?? 'Non précisé' }}
**Signalé par :** {{ $report->reporter_email ?? 'Anonyme' }}
@endcomponent

**Description :**

{{ $report->description }}

@component('mail::button', ['url' => config('app.url') . '/admin'])
Ouvrir l'administration
@endcomponent

@component('mail::subcopy')
Signalement reçu le {{ $report->created_at?->format('d.m.Y à H:i') }} · IP : {{ $report->ip_address }}
@endcomponent
@endcomponent
