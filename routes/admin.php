<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPricingController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminImportController;
use App\Http\Controllers\Admin\AdminAffiliateController;
use Illuminate\Support\Facades\Route;

/**
 * Routes d'administration — reception-par-type.ch
 *
 * Toutes protégées par : web, auth, verified, admin (niveau 8).
 * Chargées depuis bootstrap/app.php.
 *
 * Préfixe : /admin
 * Noms    : admin.*
 */
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['web', 'auth', 'verified', 'admin'])
    ->group(function () {

        // ── Dashboard (Module 8) ──────────────────────────────────────────────
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('home');

        // ── Tarifs (Module 8) ─────────────────────────────────────────────────
        Route::get('/pricing',         [AdminPricingController::class, 'index'])->name('pricing.index');
        Route::post('/pricing/{plan}', [AdminPricingController::class, 'update'])->name('pricing.update');

        // ── Utilisateurs (Module 8) ───────────────────────────────────────────
        Route::get('/users',               [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}',        [AdminUserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/grant', [AdminUserController::class, 'grant'])->name('users.grant');

        // ── Imports ASTRA (Module 2) ──────────────────────────────────────────
        Route::prefix('import')->name('import.')->group(function () {
            Route::get('/',                   [AdminImportController::class, 'index'])->name('index');
            Route::post('/',                  [AdminImportController::class, 'trigger'])->name('trigger');
            Route::get('/{importLog}',        [AdminImportController::class, 'show'])->name('show');
            Route::post('/{importLog}/retry', [AdminImportController::class, 'retry'])->name('retry');
        });

        // ── Affiliés (Module 9) ───────────────────────────────────────────────
        Route::get('/affiliates',                      [AdminAffiliateController::class, 'index'])->name('affiliates.index');
        Route::post('/affiliates/{affiliate}/approve', [AdminAffiliateController::class, 'approve'])->name('affiliates.approve');
        Route::post('/affiliates/{affiliate}/pay',     [AdminAffiliateController::class, 'pay'])->name('affiliates.pay');
        Route::post('/affiliates/{affiliate}/toggle',  [AdminAffiliateController::class, 'toggleStatus'])->name('affiliates.toggle');
    });
