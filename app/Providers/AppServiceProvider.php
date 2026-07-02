<?php

namespace App\Providers;

use App\Events\PaymentSucceeded;
use App\Listeners\ActivateSubscription;
use App\Listeners\RecordAffiliateCommission;
use App\Listeners\CreateInvoiceOnPayment;
use App\Models\User;
use App\Policies\UserManagementPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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

        // ── Email verification URL — inject {locale} required by our route prefix ─
        // Laravel's built-in VerifyEmail notification calls route('verification.verify')
        // without locale, causing UrlGenerationException on every registration.
        VerifyEmail::createUrlUsing(function (object $notifiable) {
            $locale = config('app.locale', 'fr');
            return URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'locale' => $locale,
                    'id'     => $notifiable->getKey(),
                    'hash'   => sha1($notifiable->getEmailForVerification()),
                ]
            );
        });

        // ── Password reset URL — inject {locale} required by our route prefix ─
        // Laravel's built-in ResetPassword notification calls route('password.reset')
        // without locale, causing UrlGenerationException. We override it globally.
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            $locale = config('app.locale', 'fr');
            return route('password.reset', ['locale' => $locale, 'token' => $token])
                 . '?email=' . urlencode($notifiable->getEmailForPasswordReset());
        });
    }
}
