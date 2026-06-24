<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

/**
 * Overrides the default Authenticate middleware so that unauthenticated
 * users are redirected to /{locale}/login instead of /login.
 * The parent class calls redirectTo() before throwing AuthenticationException,
 * so route('login') without a {locale} parameter would throw UrlGenerationException.
 */
class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        $locale = app()->getLocale() ?: 'fr';

        return route('login', ['locale' => $locale]);
    }
}
