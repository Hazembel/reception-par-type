<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(): View
    {
        $paypal  = Setting::group('paypal');
        $company = Setting::group('company');

        return view('admin.settings.index', compact('paypal', 'company'));
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
}
