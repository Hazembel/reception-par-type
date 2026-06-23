<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder : AdminUserSeeder
 *
 * Crée le compte administrateur par défaut.
 * Utilisé par `php artisan app:install` en mode non-interactif.
 *
 * ⚠️  Le mot de passe par défaut DOIT être changé immédiatement après l'installation.
 *
 * Usage :
 *   php artisan db:seed --class=AdminUserSeeder
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ne pas créer si un admin existe déjà
        if (User::where('subscription_level', 8)->exists()) {
            $this->command->line('  <fg=yellow>ⓘ</> Compte admin existant — ignoré.');
            return;
        }

        $email    = env('ADMIN_EMAIL', 'admin@reception-par-type.ch');
        $name     = env('ADMIN_NAME', 'Administrateur');
        $password = env('ADMIN_PASSWORD', 'ChangeMe1234!');

        User::create([
            'name'               => $name,
            'email'              => $email,
            'password'           => Hash::make($password),
            'email_verified_at'  => now(),
            'subscription_level' => 8,
            'web_tokens_balance' => 0,
            'subscribed_until'   => now()->addYears(10),
            'preferred_locale'   => 'fr',
        ]);

        $this->command->info("  ✓ Compte admin créé : {$email}");

        if (env('ADMIN_PASSWORD') === 'ChangeMe1234!') {
            $this->command->warn('  ⚠  Mot de passe par défaut détecté — changez-le immédiatement !');
        }
    }
}
