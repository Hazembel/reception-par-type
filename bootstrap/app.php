<?php

use App\Http\Middleware\SetLocale;
use App\Http\Middleware\AdminAccess;
use App\Http\Middleware\CheckVehicleAccess;
use App\Http\Middleware\EnsureApiKeyHasQuota;
use App\Http\Middleware\TrackAffiliate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Bootstrap : Application Laravel 11 — reception-par-type.ch
 *
 * Laravel 11 n'utilise plus Kernel.php : toute la configuration des middlewares,
 * du routing, des exceptions et des événements se fait ici.
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__ . '/../routes/web.php',
        api:      __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health:   '/up',
        then: function () {
            // Routes d'administration (préfixe /admin, middleware admin)
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ── Alias de middlewares (tous modules) ───────────────────────────────
        $middleware->alias([
            'setlocale'      => SetLocale::class,            // Module 1 — locale stricte (HTTP 400)
            'admin'          => AdminAccess::class,          // Module 2 — accès admin (niveau 8)
            'vehicle.access' => CheckVehicleAccess::class,   // Module 5 — matrice freemium + anti-cloaking
            'api.quota'      => EnsureApiKeyHasQuota::class,  // Module 7 — rate limiting API + quota
        ]);

        // ── Middlewares globaux Web ───────────────────────────────────────────
        // TrackAffiliate (Module 9) : intercepte ?ref= et dépose le cookie 30j.
        $middleware->web(append: [
            TrackAffiliate::class,
        ]);

        // ── Exclusion CSRF pour le webhook PayPal (vérifié par signature HMAC) ─
        $middleware->validateCsrfTokens(except: [
            'webhooks/paypal',
        ]);

        // ── Trust proxies (load balancer / Cloudflare) ────────────────────────
        // Production : définir TRUSTED_PROXIES dans .env avec les plages IP Cloudflare.
        $rawProxies = env('TRUSTED_PROXIES', '*');
        $middleware->trustProxies(
            at: $rawProxies === '*' ? '*' : array_map('trim', explode(',', $rawProxies))
        );

        // ── API stateful (Sanctum SPA) ────────────────────────────────────────
        $middleware->statefulApi();

    })
    ->withExceptions(function (Exceptions $exceptions) {

        // ── Rendu personnalisé de l'erreur 400 (locale invalide — Module 1) ───
        $exceptions->render(function (BadRequestHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error'   => 'Bad Request',
                    'message' => $e->getMessage(),
                ], 400);
            }

            return response()->view('errors.400', [
                'message'          => $e->getMessage(),
                'meta_title'       => 'Requête invalide - 400',
                'meta_description' => '',
            ], 400);
        });

    })
    ->create();
