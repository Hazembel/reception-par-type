@extends('admin.layouts.prestashop')

@section('page_title', 'Paramètres')
@section('title_icon', '⚙️')
@section('breadcrumb') Paramètres @endsection

@section('content')

{{-- ── PAYPAL ───────────────────────────────────────────────────────────────── --}}
<form method="POST" action="{{ route('admin.settings.paypal') }}">
@csrf
<div class="ps-panel">
    <div class="ps-panel-header">
        <span class="ps-icon">💳</span> Paiement PayPal
        <div class="ps-panel-tools">
            <span class="ps-badge {{ ($paypal['paypal_mode'] ?? 'sandbox') === 'live' ? 'ps-badge-success' : 'ps-badge-warning' }}">
                {{ strtoupper($paypal['paypal_mode'] ?? 'SANDBOX') }}
            </span>
        </div>
    </div>
    <div class="ps-panel-body">

        <div class="ps-form-group">
            <label class="ps-label">Mode <span class="ps-help">— sandbox pour tester, live pour la production</span></label>
            <select name="paypal_mode" class="ps-select" style="max-width:200px">
                <option value="sandbox" {{ ($paypal['paypal_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (test)</option>
                <option value="live"    {{ ($paypal['paypal_mode'] ?? '') === 'live' ? 'selected' : '' }}>Live (production)</option>
            </select>
        </div>

        <div class="ps-grid-2">
            <div class="ps-form-group">
                <label class="ps-label">Client ID <span class="ps-help">— depuis developer.paypal.com → Apps</span></label>
                <input type="text" name="paypal_client_id" class="ps-input"
                       value="{{ $paypal['paypal_client_id'] ?? '' }}"
                       placeholder="AY... ou SB-...">
            </div>
            <div class="ps-form-group">
                <label class="ps-label">Client Secret <span class="ps-help">— ne jamais partager</span></label>
                <input type="password" name="paypal_client_secret" class="ps-input"
                       value="{{ $paypal['paypal_client_secret'] ?? '' }}"
                       placeholder="••••••••••••••••"
                       autocomplete="new-password">
            </div>
        </div>

        <div class="ps-form-group">
            <label class="ps-label">Webhook ID <span class="ps-help">— depuis Dashboard PayPal → Webhooks → copier l'ID</span></label>
            <input type="text" name="paypal_webhook_id" class="ps-input"
                   value="{{ $paypal['paypal_webhook_id'] ?? '' }}"
                   placeholder="WH-XXXXXXXXXXXXXXXXXXXX"
                   style="max-width:420px">
        </div>

        <div class="ps-grid-2">
            <div class="ps-form-group">
                <label class="ps-label">Return URL <span class="ps-help">— page de succès après paiement</span></label>
                <input type="url" name="paypal_return_url" class="ps-input"
                       value="{{ $paypal['paypal_return_url'] ?? '' }}"
                       placeholder="https://reception-par-type.ch/payment/success">
            </div>
            <div class="ps-form-group">
                <label class="ps-label">Cancel URL <span class="ps-help">— page si l'utilisateur annule</span></label>
                <input type="url" name="paypal_cancel_url" class="ps-input"
                       value="{{ $paypal['paypal_cancel_url'] ?? '' }}"
                       placeholder="https://reception-par-type.ch/payment/cancel">
            </div>
        </div>

        <div style="padding-top:4px">
            <button type="submit" class="ps-btn ps-btn-primary">💾 Enregistrer PayPal</button>
            <span class="ps-help" style="margin-left:12px">
                Le token OAuth2 en cache sera automatiquement vidé à l'enregistrement.
            </span>
        </div>
    </div>
</div>
</form>

{{-- ── SOCIÉTÉ & MENTIONS LÉGALES ───────────────────────────────────────────── --}}
<form method="POST" action="{{ route('admin.settings.company') }}">
@csrf
<div class="ps-panel">
    <div class="ps-panel-header">
        <span class="ps-icon">🏢</span> Société &amp; Mentions légales
    </div>
    <div class="ps-panel-body">

        <p class="ps-help" style="margin-bottom:16px">
            Ces informations apparaissent dans les mentions légales, la politique de confidentialité, les CGU et les factures PDF.
        </p>

        <div class="ps-grid-2">
            <div class="ps-form-group">
                <label class="ps-label">Raison sociale</label>
                <input type="text" name="company_name" class="ps-input"
                       value="{{ $company['company_name'] ?? '' }}"
                       placeholder="reception-par-type.ch Sàrl">
            </div>
            <div class="ps-form-group">
                <label class="ps-label">E-mail de contact <span class="ps-help">— affiché sur les pages légales</span></label>
                <input type="email" name="company_email" class="ps-input"
                       value="{{ $company['company_email'] ?? '' }}"
                       placeholder="admin@reception-par-type.ch">
            </div>
        </div>

        <div class="ps-form-group">
            <label class="ps-label">Adresse (rue + numéro)</label>
            <input type="text" name="company_address" class="ps-input"
                   value="{{ $company['company_address'] ?? '' }}"
                   placeholder="Rue de Genève 1"
                   style="max-width:360px">
        </div>

        <div class="ps-grid-3">
            <div class="ps-form-group">
                <label class="ps-label">Code postal</label>
                <input type="text" name="company_postal" class="ps-input"
                       value="{{ $company['company_postal'] ?? '' }}"
                       placeholder="CH-1003">
            </div>
            <div class="ps-form-group">
                <label class="ps-label">Ville</label>
                <input type="text" name="company_city" class="ps-input"
                       value="{{ $company['company_city'] ?? '' }}"
                       placeholder="Lausanne">
            </div>
            <div class="ps-form-group">
                <label class="ps-label">IDE/UID (CHE-XXX.XXX.XXX) <span class="ps-help">— laisser vide si non inscrit</span></label>
                <input type="text" name="company_uid" class="ps-input"
                       value="{{ $company['company_uid'] ?? '' }}"
                       placeholder="CHE-123.456.789">
            </div>
        </div>

        <div class="ps-form-group">
            <label class="ps-label">IBAN <span class="ps-help">— compte bancaire suisse pour la QR-facture (ex: CH56 0483 5012 3456 7800 9)</span></label>
            <input type="text" name="company_iban" class="ps-input"
                   value="{{ $company['company_iban'] ?? '' }}"
                   placeholder="CH56 0483 5012 3456 7800 9"
                   style="max-width:380px; font-family: monospace; letter-spacing: 0.5px">
        </div>

        <div class="ps-grid-2">
            <div class="ps-form-group">
                <label class="ps-label">Statut TVA</label>
                <select name="company_vat_exempt" class="ps-select">
                    <option value="true"  {{ ($company['company_vat_exempt'] ?? 'true') === 'true'  ? 'selected' : '' }}>Exonéré (CA &lt; 100 000 CHF/an)</option>
                    <option value="false" {{ ($company['company_vat_exempt'] ?? 'true') === 'false' ? 'selected' : '' }}>Assujetti à la TVA</option>
                </select>
            </div>
            <div class="ps-form-group">
                <label class="ps-label">Numéro TVA <span class="ps-help">— uniquement si assujetti</span></label>
                <input type="text" name="company_vat_number" class="ps-input"
                       value="{{ $company['company_vat_number'] ?? '' }}"
                       placeholder="CHE-123.456.789 TVA">
            </div>
        </div>

        <div style="padding-top:4px">
            <button type="submit" class="ps-btn ps-btn-primary">💾 Enregistrer la société</button>
            <span class="ps-help" style="margin-left:12px">
                Les pages légales et les factures PDF refléteront immédiatement ces changements.
            </span>
        </div>
    </div>
</div>
</form>

{{-- ── CONFIGURATION E-MAIL (SMTP) ───────────────────────────────────────── --}}
<form method="POST" action="{{ route('admin.settings.mail') }}">
@csrf
<div class="ps-panel">
    <div class="ps-panel-header">
        <span class="ps-icon">📧</span> Configuration e-mail (SMTP)
        <div class="ps-panel-tools">
            <span class="ps-badge {{ ($mail['mail_mailer'] ?? 'log') === 'smtp' ? 'ps-badge-success' : 'ps-badge-warning' }}">
                {{ strtoupper($mail['mail_mailer'] ?? 'LOG') }}
            </span>
        </div>
    </div>
    <div class="ps-panel-body">

        <p class="ps-help" style="margin-bottom:16px">
            Configurez ici l'expéditeur des e-mails transactionnels (vérification d'e-mail, réinitialisation de mot de passe).
            En production, choisissez <strong>SMTP</strong> et renseignez vos identifiants.
        </p>

        <div x-data="{ mailer: '{{ $mail['mail_mailer'] ?? 'log' }}' }">

            <div class="ps-form-group" style="max-width:220px">
                <label class="ps-label">Mode d'envoi</label>
                <select name="mail_mailer" class="ps-select" x-model="mailer">
                    <option value="log">Log (dev uniquement)</option>
                    <option value="smtp">SMTP (production)</option>
                </select>
            </div>

            <div x-show="mailer === 'smtp'" x-transition>

                <div class="ps-grid-2" style="margin-top:12px">
                    <div class="ps-form-group">
                        <label class="ps-label">Hôte SMTP <span class="ps-help">— ex : smtp.mailgun.org</span></label>
                        <input type="text" name="mail_host" class="ps-input"
                               value="{{ $mail['mail_host'] ?? '' }}"
                               placeholder="smtp.mailgun.org">
                    </div>
                    <div class="ps-form-group">
                        <label class="ps-label">Port <span class="ps-help">— 587 (TLS) · 465 (SSL) · 25</span></label>
                        <input type="number" name="mail_port" class="ps-input num"
                               value="{{ $mail['mail_port'] ?? 587 }}"
                               placeholder="587" min="1" max="65535" style="max-width:120px">
                    </div>
                    <div class="ps-form-group">
                        <label class="ps-label">Nom d'utilisateur SMTP</label>
                        <input type="text" name="mail_username" class="ps-input"
                               value="{{ $mail['mail_username'] ?? '' }}"
                               placeholder="postmaster@mg.example.com"
                               autocomplete="off">
                    </div>
                    <div class="ps-form-group">
                        <label class="ps-label">Mot de passe SMTP <span class="ps-help">— laisser vide pour conserver l'actuel</span></label>
                        <input type="password" name="mail_password" class="ps-input"
                               placeholder="{{ empty($mail['mail_password'] ?? '') ? 'Entrez un mot de passe' : '•••••••• (défini)' }}"
                               autocomplete="new-password">
                    </div>
                </div>

                <div class="ps-form-group" style="max-width:240px">
                    <label class="ps-label">Chiffrement</label>
                    <select name="mail_encryption" class="ps-select">
                        <option value="tls" {{ ($mail['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (recommandé — port 587)</option>
                        <option value="ssl" {{ ($mail['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL (port 465)</option>
                        <option value="none" {{ ($mail['mail_encryption'] ?? '') === 'none' ? 'selected' : '' }}>Aucun (non recommandé)</option>
                    </select>
                </div>

            </div>{{-- /x-show smtp --}}

            <div class="ps-grid-2">
                <div class="ps-form-group">
                    <label class="ps-label">Adresse « De » <span class="ps-help">— expéditeur visible par le destinataire</span></label>
                    <input type="email" name="mail_from_address" class="ps-input"
                           value="{{ $mail['mail_from_address'] ?? '' }}"
                           placeholder="noreply@reception-par-type.ch">
                </div>
                <div class="ps-form-group">
                    <label class="ps-label">Nom « De »</label>
                    <input type="text" name="mail_from_name" class="ps-input"
                           value="{{ $mail['mail_from_name'] ?? '' }}"
                           placeholder="reception-par-type.ch">
                </div>
            </div>

        </div>

        <div style="padding-top:8px;display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <button type="submit" class="ps-btn ps-btn-primary">💾 Enregistrer e-mail</button>
            <span class="ps-help">Les modifications s'appliquent immédiatement pour les prochains envois.</span>
        </div>
    </div>
</div>
</form>

{{-- Bouton de test séparé --}}
<form method="POST" action="{{ route('admin.settings.mail.test') }}">
@csrf
<div class="ps-panel">
    <div class="ps-panel-header"><span class="ps-icon">📨</span> Envoyer un e-mail de test</div>
    <div class="ps-panel-body" style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
        <div>
            <p class="ps-help">
                Un e-mail de test sera envoyé à <strong>{{ \App\Models\Setting::get('company_email', config('mail.from.address', '—')) }}</strong>
                en utilisant les paramètres SMTP enregistrés ci-dessus.
            </p>
        </div>
        <button type="submit" class="ps-btn ps-btn-success">📨 Envoyer un test</button>
    </div>
</div>
</form>

@endsection
