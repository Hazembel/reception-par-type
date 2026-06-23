<?php

namespace App\Providers;

use App\Events\PaymentSucceeded;
use App\Listeners\ActivateSubscription;
use App\Listeners\RecordAffiliateCommission;
use App\Listeners\CreateInvoiceOnPayment;
use App\Models\User;
use App\Policies\UserManagementPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * AppServiceProvider — reception-par-type.ch
 *
 * Enregistre :
 *  - Les listeners de l'événement PaymentSucceeded (ordre important : Module 8 & 9)
 *  - La policy de gestion des utilisateurs (Module 8)
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ── Événement central post-paiement PayPal ────────────────────────────
        // L'ordre compte : on active d'abord l'abonnement (CORRECTIF BUG #1),
        // puis on enregistre la commission affilié, puis on génère la facture.
        Event::listen(PaymentSucceeded::class, [ActivateSubscription::class, 'handle']);
        Event::listen(PaymentSucceeded::class, [RecordAffiliateCommission::class, 'handle']);
        Event::listen(PaymentSucceeded::class, [CreateInvoiceOnPayment::class, 'handle']);

        // ── Policies ──────────────────────────────────────────────────────────
        Gate::policy(User::class, UserManagementPolicy::class);
    }
}
