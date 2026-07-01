<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password', [
            'meta_title' => 'Mot de passe oublié — ' . config('app.name'),
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        $route = redirect()->route('password.request', ['locale' => app()->getLocale()]);

        return $status === Password::RESET_LINK_SENT
            ? $route->with('status', __($status))
            : $route->withErrors(['email' => __($status)]);
    }
}
