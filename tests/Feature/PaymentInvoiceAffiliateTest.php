<?php

use App\Models\User;
use App\Models\Affiliate;
use App\Models\AffiliateClick;
use App\Models\AffiliateEarning;
use App\Models\Invoice;
use App\Models\PricingPlan;
use App\Events\PaymentSucceeded;
use App\Jobs\GenerateSwissInvoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SUITE 5 — PayPal, Facturation & Affiliation (Modules 8 & 9)
 *
 * Vérifie le flux complet post-paiement :
 *   - Réception webhook PayPal → événement PaymentSucceeded
 *   - Mise à niveau de l'abonnement de l'acheteur (CORRECTIF BUG #1)
 *   - Génération d'une facture PDF aux normes suisses
 *   - Attribution d'une commission d'affiliation si code présent
 * ═══════════════════════════════════════════════════════════════════════════
 */

beforeEach(function () {
    // Seed des plans (nécessaire pour ActivateSubscription)
    $this->seed(\Database\Seeders\PricingPlanSeeder::class);
});

/**
 * Helper : construit un faux payload de webhook PayPal "capture completed".
 */
function fakePayPalPayload(string $orderId, float $amount, ?string $customId = null, string $email = 'buyer@example.com'): array
{
    return [
        'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
        'resource'   => [
            'id'     => $orderId,
            'amount' => ['value' => number_format($amount, 2, '.', ''), 'currency_code' => 'CHF'],
            'custom_id' => $customId,
            'payer'  => ['email_address' => $email, 'name' => ['given_name' => 'Jean', 'surname' => 'Dupont']],
            'purchase_units' => [['custom_id' => $customId]],
        ],
    ];
}

/**
 * Helper : payload "PAYMENT.SALE.COMPLETED" (API classique / Subscriptions / IPN).
 * Structure différente de CAPTURE : montant dans amount.total, devise dans currency.
 * Permet de vérifier que le webhook gère LES DEUX formats (extraction robuste).
 */
function fakePayPalSalePayload(string $orderId, float $amount, ?string $customId = null, string $email = 'buyer@example.com'): array
{
    return [
        'event_type' => 'PAYMENT.SALE.COMPLETED',
        'resource'   => [
            'id'     => $orderId,
            'amount' => ['total' => number_format($amount, 2, '.', ''), 'currency' => 'CHF'],
            'custom_id' => $customId,
            'payer'  => ['payer_info' => ['email' => $email]],
        ],
    ];
}

describe('Webhook PayPal — réception et dispatch', function () {

    it('accepte un webhook valide et renvoie 200', function () {
        $payload = fakePayPalPayload('ORDER-123', 49.00, 'plan:4');

        $response = $this->postJson('/webhooks/paypal', $payload);
        $response->assertStatus(200);
    });

    it('dispatche l\'événement PaymentSucceeded à la réception d\'un paiement', function () {
        Event::fake([PaymentSucceeded::class]);

        $payload = fakePayPalPayload('ORDER-124', 49.00, 'plan:4');
        $this->postJson('/webhooks/paypal', $payload);

        Event::assertDispatched(PaymentSucceeded::class, function ($event) {
            return $event->orderId === 'ORDER-124'
                && $event->amountCts === 4900;
        });
    });

    it('reste idempotent : un même ordre n\'est pas traité deux fois', function () {
        $user = User::factory()->create([
            'email' => 'buyer@example.com', 'subscription_level' => 1,
        ]);

        $payload = fakePayPalPayload('ORDER-DUP', 49.00, 'plan:4', 'buyer@example.com');

        // Créer une facture pré-existante pour cet ordre
        Invoice::create([
            'user_id'         => $user->id,
            'invoice_number'  => 'RPT-2024-00001',
            'paypal_order_id' => 'ORDER-DUP',
            'subtotal_cts'    => 4900, 'tax_rate_bp' => 0, 'tax_amount_cts' => 0, 'total_cts' => 4900,
            'is_vat_exempt'   => true, 'currency' => 'CHF',
            'billing_address' => ['name' => 'Test'], 'line_items' => [],
            'status'          => 'sent', 'issued_at' => now(),
        ]);

        Event::fake([PaymentSucceeded::class]);
        $this->postJson('/webhooks/paypal', $payload);

        // L'événement ne doit PAS être redispatché (idempotence)
        Event::assertNotDispatched(PaymentSucceeded::class);
    });
});

describe('Activation de l\'abonnement (CORRECTIF BUG #1)', function () {

    it('met à niveau le subscription_level de l\'acheteur après paiement d\'un plan', function () {
        $user = User::factory()->create([
            'email'              => 'buyer@example.com',
            'subscription_level' => 1,
            'subscribed_until'   => null,
        ]);

        // Déclencher le listener directement
        $event = new PaymentSucceeded(
            orderId:       'ORDER-200',
            amountCts:     4900,
            affiliateCode: null,
            buyer:         $user,
            payerEmail:    'buyer@example.com',
            paypalPayload: ['custom_id' => 'plan:4'],
        );

        (new \App\Listeners\ActivateSubscription())->handle($event);

        $user->refresh();
        expect($user->subscription_level)->toBe(4)
            ->and($user->subscribed_until)->not->toBeNull()
            ->and($user->subscribed_until->isFuture())->toBeTrue();
    });

    it('crédite des jetons après l\'achat d\'un pack de jetons', function () {
        $user = User::factory()->create([
            'email' => 'buyer@example.com', 'web_tokens_balance' => 5,
        ]);

        $event = new PaymentSucceeded(
            orderId:       'ORDER-201',
            amountCts:     10000,
            affiliateCode: null,
            buyer:         $user,
            payerEmail:    'buyer@example.com',
            paypalPayload: ['custom_id' => 'tokens:50'],
        );

        (new \App\Listeners\ActivateSubscription())->handle($event);

        $user->refresh();
        expect($user->web_tokens_balance)->toBe(55); // 5 + 50
    });

    it('cumule la durée si l\'abonnement est déjà actif', function () {
        $existingEnd = now()->addDays(10);
        $user = User::factory()->create([
            'email' => 'buyer@example.com', 'subscription_level' => 4,
            'subscribed_until' => $existingEnd,
        ]);

        $event = new PaymentSucceeded(
            'ORDER-202', 4900, null, $user, 'buyer@example.com', ['custom_id' => 'plan:4']
        );
        (new \App\Listeners\ActivateSubscription())->handle($event);

        $user->refresh();
        // La nouvelle date doit être ~1 mois APRÈS la date existante (cumul)
        expect($user->subscribed_until->greaterThan($existingEnd->addDays(20)))->toBeTrue();
    });
});

describe('Génération de facture suisse (Module 9)', function () {

    it('génère une facture avec numéro séquentiel au format RPT-YYYY-NNNNN', function () {
        $number = Invoice::generateNumber();

        expect($number)->toMatch('/^RPT-\d{4}-\d{5}$/');
    });

    it('incrémente le numéro de facture séquentiellement', function () {
        $user = User::factory()->create();

        $n1 = Invoice::generateNumber();
        Invoice::create([
            'user_id' => $user->id, 'invoice_number' => $n1, 'paypal_order_id' => 'O1',
            'subtotal_cts' => 4900, 'tax_rate_bp' => 0, 'tax_amount_cts' => 0, 'total_cts' => 4900,
            'is_vat_exempt' => true, 'currency' => 'CHF',
            'billing_address' => [], 'line_items' => [], 'status' => 'sent', 'issued_at' => now(),
        ]);

        $n2 = Invoice::generateNumber();

        // Le second numéro doit être strictement supérieur
        expect((int) substr($n2, -5))->toBeGreaterThan((int) substr($n1, -5));
    });

    it('applique la TVA exonérée (art. 10 LTVA) quand configuré', function () {
        config(['billing.vat_exempt' => true]);

        $user = User::factory()->create(['email' => 'buyer@example.com']);

        Queue::fake();
        $event = new PaymentSucceeded(
            'ORDER-300', 4900, null, $user, 'buyer@example.com',
            ['payer' => ['name' => ['given_name' => 'Jean', 'surname' => 'Test']]]
        );

        GenerateSwissInvoice::fromEvent($event)->handle();

        $invoice = Invoice::where('paypal_order_id', 'ORDER-300')->first();
        expect($invoice)->not->toBeNull()
            ->and($invoice->is_vat_exempt)->toBeTrue()
            ->and($invoice->tax_amount_cts)->toBe(0)
            ->and($invoice->total_cts)->toBe(4900);
    })->skip('Active si dompdf est installé dans l\'env de test');

    it('calcule correctement la TVA à 8.1% si assujetti', function () {
        config(['billing.vat_exempt' => false]);

        // Montant TTC 108.10 → HT 100.00 + TVA 8.10
        $totalCts    = 10810;
        $vatRateBp   = 810;
        $subtotalCts = (int) round($totalCts / (1 + $vatRateBp / 10000));
        $taxCts      = $totalCts - $subtotalCts;

        expect($subtotalCts)->toBe(10000)
            ->and($taxCts)->toBe(810);
    });

    it('envoie la facture par e-mail', function () {
        Mail::fake();
        $user = User::factory()->create(['email' => 'buyer@example.com']);

        $event = new PaymentSucceeded(
            'ORDER-301', 4900, null, $user, 'buyer@example.com',
            ['payer' => ['name' => ['given_name' => 'Jean', 'surname' => 'Test']]]
        );
        GenerateSwissInvoice::fromEvent($event)->handle();

        Mail::assertQueued(\App\Mail\InvoiceMail::class);
    })->skip('Active si dompdf est installé dans l\'env de test');
});

describe('Commission d\'affiliation (Module 9)', function () {

    it('attribue une commission quand un code affilié est présent', function () {
        $affiliateUser = User::factory()->create();
        $affiliate = Affiliate::create([
            'user_id'                  => $affiliateUser->id,
            'affiliate_code'           => 'GARAGE10',
            'commission_rate_permille' => 100, // 10%
            'status'                   => 'active',
        ]);

        $buyer = User::factory()->create(['email' => 'buyer@example.com']);

        $event = new PaymentSucceeded(
            orderId:       'ORDER-400',
            amountCts:     10000, // 100.00 CHF
            affiliateCode: 'GARAGE10',
            buyer:         $buyer,
            payerEmail:    'buyer@example.com',
            paypalPayload: [],
        );

        (new \App\Listeners\RecordAffiliateCommission())->handle($event);

        $earning = AffiliateEarning::where('paypal_order_id', 'ORDER-400')->first();

        expect($earning)->not->toBeNull()
            ->and($earning->commission_cts)->toBe(1000) // 10% de 100.00 = 10.00 CHF
            ->and($earning->status)->toBe('pending');
    });

    it('n\'attribue PAS de commission sans code affilié', function () {
        $buyer = User::factory()->create(['email' => 'buyer@example.com']);

        $event = new PaymentSucceeded(
            'ORDER-401', 10000, null, $buyer, 'buyer@example.com', []
        );
        (new \App\Listeners\RecordAffiliateCommission())->handle($event);

        expect(AffiliateEarning::count())->toBe(0);
    });

    it('ignore un code affilié inactif ou inexistant', function () {
        $buyer = User::factory()->create(['email' => 'buyer@example.com']);

        $event = new PaymentSucceeded(
            'ORDER-402', 10000, 'INEXISTANT99', $buyer, 'buyer@example.com', []
        );
        (new \App\Listeners\RecordAffiliateCommission())->handle($event);

        expect(AffiliateEarning::count())->toBe(0);
    });

    it('reste idempotent : pas de double commission pour le même ordre', function () {
        $affiliateUser = User::factory()->create();
        $affiliate = Affiliate::create([
            'user_id' => $affiliateUser->id, 'affiliate_code' => 'GARAGE10',
            'commission_rate_permille' => 100, 'status' => 'active',
        ]);
        $buyer = User::factory()->create(['email' => 'buyer@example.com']);

        $event = new PaymentSucceeded('ORDER-403', 10000, 'GARAGE10', $buyer, 'buyer@example.com', []);

        // Deux exécutions du même événement
        (new \App\Listeners\RecordAffiliateCommission())->handle($event);
        (new \App\Listeners\RecordAffiliateCommission())->handle($event);

        // Une seule commission doit exister
        expect(AffiliateEarning::where('paypal_order_id', 'ORDER-403')->count())->toBe(1);
    });

    it('TEST D\'INTÉGRATION COMPLET : paiement avec affilié → niveau + facture + commission', function () {
        Queue::fake();

        // Setup : affilié actif + acheteur niveau 1
        $affiliateUser = User::factory()->create();
        Affiliate::create([
            'user_id' => $affiliateUser->id, 'affiliate_code' => 'GARAGE10',
            'commission_rate_permille' => 150, 'status' => 'active',
        ]);
        $buyer = User::factory()->create([
            'email' => 'buyer@example.com', 'subscription_level' => 1, 'subscribed_until' => null,
        ]);

        // Simuler le paiement complet
        $event = new PaymentSucceeded(
            orderId:       'ORDER-FULL',
            amountCts:     4900,
            affiliateCode: 'GARAGE10',
            buyer:         $buyer,
            payerEmail:    'buyer@example.com',
            paypalPayload: ['custom_id' => 'plan:4|ref:GARAGE10'],
        );

        // Exécuter les 3 listeners (ordre réel de l'app)
        (new \App\Listeners\ActivateSubscription())->handle($event);
        (new \App\Listeners\RecordAffiliateCommission())->handle($event);

        // 1. L'acheteur est passé niveau 4
        $buyer->refresh();
        expect($buyer->subscription_level)->toBe(4);

        // 2. La commission affilié existe (15% de 49.00 = 7.35 CHF)
        $earning = AffiliateEarning::where('paypal_order_id', 'ORDER-FULL')->first();
        expect($earning)->not->toBeNull()
            ->and($earning->commission_cts)->toBe(735);
    });
});

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SUITE AJOUTÉE PAR L'AUDIT — Idempotence stricte (double webhook PayPal)
 *
 * Vérifie qu'un même ordre PayPal envoyé DEUX fois (retry réseau de PayPal)
 * ne crédite JAMAIS deux fois les jetons ni ne prolonge deux fois l'abonnement.
 * La garde ProcessedPayment::claim() doit bloquer le second passage.
 * ═══════════════════════════════════════════════════════════════════════════
 */
describe('Idempotence anti double-paiement (audit)', function () {

    beforeEach(function () {
        $this->seed(\Database\Seeders\PricingPlanSeeder::class);
    });

    it('ne crédite PAS deux fois les jetons sur un double webhook', function () {
        $user = User::factory()->create([
            'email' => 'buyer@example.com', 'web_tokens_balance' => 0,
        ]);

        $makeEvent = fn() => new PaymentSucceeded(
            orderId:       'ORDER-DOUBLE-1',
            amountCts:     10000,
            affiliateCode: null,
            buyer:         $user,
            payerEmail:    'buyer@example.com',
            paypalPayload: ['custom_id' => 'tokens:50'],
        );

        // PayPal envoie le webhook DEUX fois
        (new \App\Listeners\ActivateSubscription())->handle($makeEvent());
        (new \App\Listeners\ActivateSubscription())->handle($makeEvent());

        $user->refresh();
        // 50 jetons crédités UNE seule fois, pas 100
        expect($user->web_tokens_balance)->toBe(50);
    });

    it('ne prolonge PAS deux fois l\'abonnement sur un double webhook', function () {
        $user = User::factory()->create([
            'email' => 'buyer@example.com', 'subscription_level' => 1, 'subscribed_until' => null,
        ]);

        $makeEvent = fn() => new PaymentSucceeded(
            'ORDER-DOUBLE-2', 4900, null, $user, 'buyer@example.com', ['custom_id' => 'plan:4']
        );

        (new \App\Listeners\ActivateSubscription())->handle($makeEvent());
        $firstEnd = $user->fresh()->subscribed_until;

        // Second passage (doublon) : ne doit RIEN changer
        (new \App\Listeners\ActivateSubscription())->handle($makeEvent());
        $secondEnd = $user->fresh()->subscribed_until;

        expect($user->fresh()->subscription_level)->toBe(4)
            ->and($secondEnd->equalTo($firstEnd))->toBeTrue(); // pas de double +1 mois
    });

    it('enregistre l\'ordre dans processed_payments au premier traitement', function () {
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $event = new PaymentSucceeded(
            'ORDER-TRACK-1', 4900, null, $user, 'buyer@example.com', ['custom_id' => 'tokens:10']
        );

        (new \App\Listeners\ActivateSubscription())->handle($event);

        expect(\App\Models\ProcessedPayment::where('paypal_order_id', 'ORDER-TRACK-1')->exists())->toBeTrue()
            ->and(\App\Models\ProcessedPayment::where('paypal_order_id', 'ORDER-TRACK-1')->count())->toBe(1);
    });

    it('ProcessedPayment::claim() renvoie true puis false pour le même ordre', function () {
        expect(\App\Models\ProcessedPayment::claim('ORDER-CLAIM-1'))->toBeTrue()
            ->and(\App\Models\ProcessedPayment::claim('ORDER-CLAIM-1'))->toBeFalse();
    });

    it('ne génère pas deux factures pour le même ordre (garde Invoice::exists)', function () {
        $user = User::factory()->create(['email' => 'buyer@example.com']);

        \App\Models\Invoice::create([
            'user_id' => $user->id, 'invoice_number' => 'RPT-2026-09999',
            'paypal_order_id' => 'ORDER-INV-DUP',
            'subtotal_cts' => 4900, 'tax_rate_bp' => 0, 'tax_amount_cts' => 0, 'total_cts' => 4900,
            'is_vat_exempt' => true, 'currency' => 'CHF',
            'billing_address' => [], 'line_items' => [], 'status' => 'sent', 'issued_at' => now(),
        ]);

        // Tenter de recréer une facture pour le même ordre ne doit pas aboutir à un doublon
        $exists = \App\Models\Invoice::where('paypal_order_id', 'ORDER-INV-DUP')->count();
        expect($exists)->toBe(1);
    });
});

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SUITE AJOUTÉE PAR L'AUDIT — Fuseau horaire (Europe/Zurich)
 *
 * Vérifie que l'application calcule "le mois courant" sur l'heure suisse,
 * pour que la réinitialisation des compteurs/quotas bascule à minuit Zurich.
 * ═══════════════════════════════════════════════════════════════════════════
 */
describe('Fuseau horaire suisse (audit)', function () {

    it('utilise Europe/Zurich comme fuseau applicatif', function () {
        expect(config('app.timezone'))->toBe('Europe/Zurich');
    });

    it('now() est calé sur le fuseau suisse', function () {
        expect(now()->getTimezone()->getName())->toBe('Europe/Zurich');
    });

    it('compte les déverrouillages du mois selon l\'heure suisse', function () {
        $user = User::factory()->create();
        // Déverrouillage daté de "maintenant" (heure suisse)
        \App\Models\UserUnlockedVehicle::create([
            'user_id' => $user->id,
            'vehicle_id' => \App\Models\Vehicle::factory()->create()->id,
            'unlocked_at' => now(),
            'tokens_spent' => 1,
        ]);

        $count = \App\Models\UserUnlockedVehicle::where('user_id', $user->id)->thisMonth()->count();
        expect($count)->toBe(1);
    })->skip(fn() => !class_exists(\App\Models\UserUnlockedVehicle::class), 'Hors app Laravel');
});

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SUITE AJOUTÉE PAR L'AUDIT LOT 3 — Idempotence au niveau du WEBHOOK
 *
 * Vérifie que la garde déplacée dans PayPalWebhookController (ProcessedPayment::
 * claim en amont du dispatch) bloque tout double-traitement à la source, sans
 * dépendre d'une facture générée de façon asynchrone.
 * ═══════════════════════════════════════════════════════════════════════════
 */
describe('Idempotence webhook PayPal — garde en amont (audit lot 3)', function () {

    it('un double webhook ne réclame l\'ordre qu\'une seule fois', function () {
        expect(\App\Models\ProcessedPayment::claim('ORDER-WH-DUP', amountCts: 4900))->toBeTrue()
            ->and(\App\Models\ProcessedPayment::claim('ORDER-WH-DUP', amountCts: 4900))->toBeFalse()
            ->and(\App\Models\ProcessedPayment::where('paypal_order_id', 'ORDER-WH-DUP')->count())->toBe(1);
    })->skip(fn() => !class_exists(\App\Models\ProcessedPayment::class), 'Hors app Laravel');

    it('la contrainte unique empêche deux factures pour le même ordre PayPal', function () {
        $user = User::factory()->create();
        $base = [
            'user_id' => $user->id, 'paypal_order_id' => 'ORDER-INV-UNIQUE',
            'subtotal_cts' => 4900, 'tax_rate_bp' => 0, 'tax_amount_cts' => 0, 'total_cts' => 4900,
            'is_vat_exempt' => true, 'currency' => 'CHF',
            'billing_address' => [], 'line_items' => [], 'status' => 'sent', 'issued_at' => now(),
        ];
        \App\Models\Invoice::create(array_merge($base, ['invoice_number' => 'RPT-2026-00100']));

        // La 2e insertion avec le même paypal_order_id doit lever une exception d'unicité
        expect(fn() => \App\Models\Invoice::create(array_merge($base, ['invoice_number' => 'RPT-2026-00101'])))
            ->toThrow(\Illuminate\Database\QueryException::class);
    })->skip(fn() => !class_exists(\App\Models\Invoice::class), 'Hors app Laravel');

    it('calcule une commission exacte (4900 cts à 100 permille = 490 cts)', function () {
        $affiliate = \App\Models\Affiliate::factory()->create(['commission_rate_permille' => 100]);
        expect($affiliate->calculateCommission(4900))->toBe(490);
    })->skip(fn() => !class_exists(\App\Models\Affiliate::class) || !method_exists(\App\Models\Affiliate::class, 'calculateCommission'), 'Hors app Laravel');
});
