<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayPalWebhookController;
use App\Http\Controllers\Account\AffiliateController;
use App\Http\Controllers\Account\InvoiceController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\CompareController;
use Illuminate\Support\Facades\Route;

/**
 * Routes Web — reception-par-type.ch
 *
 * Architecture des URLs (toutes localisées) :
 *   /{locale}/                        → Accueil
 *   /{locale}/search                  → Recherche
 *   /{locale}/vehicle/{slug}          → Fiche technique (slug, jamais l'ULID)
 *   /{locale}/faq, /{locale}/guide    → Aide
 *   /{locale}/account/affiliate       → Espace affilié
 *   /{locale}/account/invoices        → Mes factures
 *   /{locale}/checkout/create-order   → Création ordre PayPal
 *
 * La locale est validée STRICTEMENT par le middleware SetLocale (HTTP 400 sinon).
 * Les routes admin sont dans routes/admin.php (chargé via bootstrap/app.php).
 */

// ── Webhook PayPal (hors locale, sans CSRF — vérifié par signature HMAC) ──────
Route::post('/webhooks/paypal', [PayPalWebhookController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('webhooks.paypal');

// ── Déconnexion (route nommée 'logout', utilisée par le back-office) ─────────
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->middleware('web')->name('logout');

// ── Redirection racine → locale préférée du navigateur ───────────────────────
Route::get('/', function () {
    $preferred = request()->getPreferredLanguage(['fr', 'de', 'it', 'en']) ?? 'fr';
    return redirect()->route('home', ['locale' => $preferred]);
})->name('root');

// ── Redirections sans locale pour les retours PayPal ─────────────────────────
// PayPal stocke l'URL configurée telle quelle (ex: https://example.ch/payment/success).
// Ces routes permettent que ça fonctionne même sans préfixe de locale.
Route::get('/payment/success', function () {
    $locale = request()->getPreferredLanguage(['fr', 'de', 'it', 'en']) ?? 'fr';
    return redirect()->route('payment.success', ['locale' => $locale] + request()->query());
});
Route::get('/payment/cancel', function () {
    $locale = request()->getPreferredLanguage(['fr', 'de', 'it', 'en']) ?? 'fr';
    return redirect()->route('payment.cancel', ['locale' => $locale]);
});

// ── Groupe principal avec préfixe locale ─────────────────────────────────────
Route::prefix('{locale}')
    ->middleware(['web', 'setlocale'])
    ->where(['locale' => 'fr|de|it|en'])
    ->group(function () {

        // ── Pages publiques ───────────────────────────────────────────────────
        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::get('/search',  [SearchController::class, 'index'])->name('search');
        Route::post('/search', [SearchController::class, 'results'])->name('search.results');

        Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
        Route::get('/compare', [CompareController::class, 'index'])->name('compare');

        // Fiche véhicule par slug (Route Model Binding — ULID jamais exposé)
        // Le middleware vehicle.access applique la matrice freemium + anti-cloaking.
        Route::get('/vehicle/{vehicle:slug}', [VehicleController::class, 'show'])
            ->middleware('vehicle.access')
            ->name('vehicle.show');

        // ── Aide (Module 4) ───────────────────────────────────────────────────
        Route::get('/faq',   [FaqController::class, 'index'])->name('faq');
        Route::get('/guide', [GuideController::class, 'index'])->name('guide');

        // ── Checkout PayPal (Module 9 — lit le cookie affilié) ────────────────
        Route::post('/checkout/create-order', [CheckoutController::class, 'createOrder'])
            ->middleware(['auth', 'throttle:10,1'])
            ->name('checkout.create-order');

        // ── Retour PayPal après paiement ──────────────────────────────────────
        Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
        Route::get('/payment/cancel',  [PaymentController::class, 'cancel'])->name('payment.cancel');

        // ── Espace compte (Module 9) ──────────────────────────────────────────
        Route::middleware(['auth', 'verified'])->prefix('account')->name('account.')->group(function () {
            Route::get('/affiliate',       [AffiliateController::class, 'index'])->name('affiliate.index');
            Route::post('/affiliate/join', [AffiliateController::class, 'join'])->name('affiliate.join');
            Route::get('/profile',          [ProfileController::class, 'show'])->name('profile.show');
            Route::put('/profile',          [ProfileController::class, 'updateInfo'])->name('profile.update');
            Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
            Route::get('/invoices',                     [InvoiceController::class, 'index'])->name('invoices.index');
            Route::get('/invoices/{invoice}/download',  [InvoiceController::class, 'download'])->name('invoices.download');
        });

        // ── Pages d'authentification ─────────────────────────────────────────
        Route::get('/login',  [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
        Route::get('/register',  [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [RegisterController::class, 'register']);
        Route::get('/password/reset',         [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('/password/email',        [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('/password/reset',        [ResetPasswordController::class, 'reset'])->name('password.update');
        Route::get('/email/verify', fn () => view('auth.verify-email'))->middleware('auth')->name('verification.notice');
        Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
            $request->fulfill();
            return redirect()->route('home', ['locale' => app()->getLocale()])->with('verified', true);
        })->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');
        Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
            $request->user()->sendEmailVerificationNotification();
            return back()->with('resent', true);
        })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

        // ── Pages légales (LPD / RGPD) ────────────────────────────────────────
        Route::get('/mentions-legales', fn () => view('legal.mentions', ['meta_title' => 'Mentions Légales', 'meta_description' => '']))->name('legal');
        Route::get('/confidentialite',  fn () => view('legal.privacy',  ['meta_title' => 'Confidentialité', 'meta_description' => '']))->name('privacy');
        Route::get('/cgu',              fn () => view('legal.terms',    ['meta_title' => "Conditions d'utilisation", 'meta_description' => '']))->name('terms');
    });
