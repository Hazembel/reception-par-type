<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('general')->index();
            $table->timestamps();
        });

        // Seed defaults from current env so admin starts with values pre-filled
        $now = now();
        DB::table('settings')->insert([
            // ── PayPal ──────────────────────────────────────────────────────
            ['key' => 'paypal_mode',          'value' => env('PAYPAL_MODE', 'sandbox'),         'group' => 'paypal',  'created_at' => $now, 'updated_at' => $now],
            ['key' => 'paypal_client_id',     'value' => env('PAYPAL_CLIENT_ID', ''),           'group' => 'paypal',  'created_at' => $now, 'updated_at' => $now],
            ['key' => 'paypal_client_secret', 'value' => env('PAYPAL_CLIENT_SECRET', ''),       'group' => 'paypal',  'created_at' => $now, 'updated_at' => $now],
            ['key' => 'paypal_webhook_id',    'value' => env('PAYPAL_WEBHOOK_ID', ''),          'group' => 'paypal',  'created_at' => $now, 'updated_at' => $now],
            ['key' => 'paypal_return_url',    'value' => env('PAYPAL_RETURN_URL', ''),          'group' => 'paypal',  'created_at' => $now, 'updated_at' => $now],
            ['key' => 'paypal_cancel_url',    'value' => env('PAYPAL_CANCEL_URL', ''),          'group' => 'paypal',  'created_at' => $now, 'updated_at' => $now],
            // ── Société (mentions légales, factures) ─────────────────────────
            ['key' => 'company_name',         'value' => env('BILLING_COMPANY', 'reception-par-type.ch Sàrl'), 'group' => 'company', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'company_address',      'value' => env('BILLING_ADDRESS', 'Rue de Genève 1'),            'group' => 'company', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'company_postal',       'value' => env('BILLING_POSTAL', 'CH-1003'),                     'group' => 'company', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'company_city',         'value' => env('BILLING_CITY', 'Lausanne'),                      'group' => 'company', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'company_email',        'value' => 'admin@reception-par-type.ch',                        'group' => 'company', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'company_uid',          'value' => env('BILLING_UID', ''),                               'group' => 'company', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'company_vat_exempt',   'value' => env('BILLING_VAT_EXEMPT', 'true'),                    'group' => 'company', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'company_vat_number',   'value' => env('BILLING_VAT_NUMBER', ''),                        'group' => 'company', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
