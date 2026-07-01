<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register', [
            'meta_title' => 'Créer un compte — ' . config('app.name'),
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name'               => $validated['name'],
            'email'              => $validated['email'],
            'password'           => Hash::make($validated['password']),
            'subscription_level' => 1, // Free tier
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice', ['locale' => app()->getLocale()]);
    }
}
