<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(): View
    {
        $paypal  = Setting::group('paypal');
        $company = Setting::group('company');
        $mail    = Setting::group('mail');

        return view('admin.settings.index', compact('paypal', 'company', 'mail'));
    }

    public function updatePaypal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'paypal_mode'          => ['required', 'in:sandbox,live'],
            'paypal_client_id'     => ['required', 'string', 'max:200'],
            'paypal_client_secret' => ['required', 'string', 'max:200'],
            'paypal_webhook_id'    => ['nullable', 'string', 'max:200'],
            'paypal_return_url'    => ['nullable', 'url', 'max:500'],
            'paypal_cancel_url'    => ['nullable', 'url', 'max:500'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '', 'paypal');
        }

        // Bust the cached OAuth token so the new credentials are used immediately
        Cache::forget('paypal_access_token');
        Setting::clearGroupCache('paypal');

        return back()->with('success', 'Paramètres PayPal enregistrés.');
    }

    public function updateCompany(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name'       => ['required', 'string', 'max:200'],
            'company_address'    => ['required', 'string', 'max:300'],
            'company_postal'     => ['required', 'string', 'max:20'],
            'company_city'       => ['required', 'string', 'max:100'],
            'company_email'      => ['required', 'email', 'max:200'],
            'company_uid'        => ['nullable', 'string', 'max:50'],
            'company_iban'       => ['nullable', 'string', 'max:34', 'regex:/^CH\d{2}[\s\d]{12,30}$/i'],
            'company_vat_exempt' => ['nullable', 'in:true,false,1,0'],
            'company_vat_number' => ['nullable', 'string', 'max:50'],
        ]);

        $data['company_vat_exempt'] = isset($data['company_vat_exempt'])
            && in_array($data['company_vat_exempt'], ['true', '1'])
            ? 'true' : 'false';

        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '', 'company');
        }

        Setting::clearGroupCache('company');

        return back()->with('success', 'Informations société enregistrées.');
    }

    public function updateMail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mail_mailer'       => ['required', 'in:smtp,log'],
            'mail_host'         => ['required_if:mail_mailer,smtp', 'nullable', 'string', 'max:200'],
            'mail_port'         => ['required_if:mail_mailer,smtp', 'nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username'     => ['nullable', 'string', 'max:200'],
            'mail_password'     => ['nullable', 'string', 'max:500'],
            'mail_encryption'   => ['required_if:mail_mailer,smtp', 'nullable', 'in:tls,ssl,none'],
            'mail_from_address' => ['required', 'email', 'max:200'],
            'mail_from_name'    => ['required', 'string', 'max:100'],
        ]);

        // Default encryption to tls if not submitted (log mode hides the select)
        $data['mail_encryption'] = $data['mail_encryption'] ?? 'tls';

        // Never overwrite a stored password with an empty field (masked field left blank)
        if (empty($data['mail_password'])) {
            unset($data['mail_password']);
        }

        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '', 'mail');
        }

        Setting::clearGroupCache('mail');

        return back()->with('success', '✅ Paramètres e-mail enregistrés. Utilisez le bouton « Test » pour vérifier la connexion SMTP.');
    }

    public function testMail(Request $request): RedirectResponse
    {
        $mail = Setting::group('mail');

        if (empty($mail['mail_host']) && ($mail['mail_mailer'] ?? 'log') === 'smtp') {
            return back()->with('error', '❌ Aucun hôte SMTP configuré. Enregistrez d\'abord vos paramètres e-mail.');
        }

        // Apply settings to runtime config for this request
        $this->applyMailConfig($mail);

        $to = Setting::get('company_email', config('mail.from.address'));

        try {
            Mail::raw(
                "✅ Test SMTP réussi !\n\n"
                . "Serveur : " . ($mail['mail_host'] ?? 'log') . "\n"
                . "Port    : " . ($mail['mail_port'] ?? 587) . "\n"
                . "De      : " . ($mail['mail_from_address'] ?? '') . "\n\n"
                . "Ce message confirme que la configuration e-mail du tableau de bord fonctionne correctement.",
                fn ($m) => $m
                    ->to($to)
                    ->subject('✅ Test SMTP — reception-par-type.ch')
            );

            return back()->with('success', "✅ E-mail de test envoyé à {$to}. Vérifiez votre boîte de réception.");
        } catch (\Exception $e) {
            return back()->with('error', '❌ Échec SMTP : ' . $e->getMessage());
        }
    }

    public static function applyMailConfig(array $mail): void
    {
        $mailer = $mail['mail_mailer'] ?? 'log';

        Config::set('mail.default', $mailer);
        Config::set('mail.from.address', $mail['mail_from_address'] ?? config('mail.from.address'));
        Config::set('mail.from.name',    $mail['mail_from_name']    ?? config('mail.from.name'));

        if ($mailer === 'smtp') {
            Config::set('mail.mailers.smtp.host',       $mail['mail_host']       ?? '');
            Config::set('mail.mailers.smtp.port',       (int) ($mail['mail_port'] ?? 587));
            Config::set('mail.mailers.smtp.username',   $mail['mail_username']   ?? '');
            Config::set('mail.mailers.smtp.password',   $mail['mail_password']   ?? '');
            Config::set('mail.mailers.smtp.encryption', ($mail['mail_encryption'] ?? 'tls') === 'none' ? null : ($mail['mail_encryption'] ?? 'tls'));
        }
    }
}
