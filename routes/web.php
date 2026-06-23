<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PayPalWebhookController;
use App\Http\Controllers\Account\AffiliateController;
use App\Http\Controllers\Account\InvoiceController;
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
Route::post('/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->middleware('web')->name('logout');

// ── Redirection racine → locale préférée du navigateur ───────────────────────
Route::get('/', function () {
    $preferred = request()->getPreferredLanguage(['fr', 'de', 'it', 'en']) ?? 'fr';
    return redirect()->route('home', ['locale' => $preferred]);
})->name('root');

// ── Groupe principal avec préfixe locale ─────────────────────────────────────
Route::prefix('{locale}')
    ->middleware(['web', 'setlocale'])
    ->where(['locale' => 'fr|de|it|en'])
    ->group(function () {

        // ── Pages publiques ───────────────────────────────────────────────────
        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::get('/search',  [SearchController::class, 'index'])->name('search');
        Route::post('/search', [SearchController::class, 'results'])->name('search.results');

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
            ->middleware(['auth'])
            ->name('checkout.create-order');

        // ── Espace compte (Module 9) ──────────────────────────────────────────
        Route::middleware(['auth', 'verified'])->prefix('account')->name('account.')->group(function () {
            Route::get('/affiliate',       [AffiliateController::class, 'index'])->name('affiliate.index');
            Route::post('/affiliate/join', [AffiliateController::class, 'join'])->name('affiliate.join');
            Route::get('/invoices',                     [InvoiceController::class, 'index'])->name('invoices.index');
            Route::get('/invoices/{invoice}/download',  [InvoiceController::class, 'download'])->name('invoices.download');
        });

        // ── Pages d'authentification (placeholders — Breeze/Fortify à brancher) ─
        Route::get('/login',    fn () => abort(404))->name('login');
        Route::get('/register', fn () => abort(404))->name('register');

        // ── Pages légales (LPD / RGPD) ────────────────────────────────────────
        Route::get('/mentions-legales', fn () => view('legal.mentions', ['meta_title' => 'Mentions Légales', 'meta_description' => '']))->name('legal');
        Route::get('/confidentialite',  fn () => view('legal.privacy',  ['meta_title' => 'Confidentialité', 'meta_description' => '']))->name('privacy');
        Route::get('/cgu',              fn () => view('legal.terms',    ['meta_title' => "Conditions d'utilisation", 'meta_description' => '']))->name('terms');
    });
