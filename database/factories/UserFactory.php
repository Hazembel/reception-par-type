<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Factory : UserFactory
 *
 * Génère des utilisateurs de test. La PK ULID est gérée par le trait HasUlids.
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'               => $this->faker->name(),
            'email'              => $this->faker->unique()->safeEmail(),
            'email_verified_at'  => now(),
            'password'           => Hash::make('password'),
            'preferred_locale'   => $this->faker->randomElement(['fr', 'de', 'it', 'en']),
            'subscription_level' => 1,
            'web_tokens_balance' => 0,
            'web_monthly_counter'=> 0,
            'subscribed_until'   => null,
            'remember_token'     => Str::random(10),
        ];
    }

    /** Compte non vérifié */
    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    /** Abonné actif à un niveau donné */
    public function subscribed(int $level = 4): static
    {
        return $this->state(fn () => [
            'subscription_level' => $level,
            'subscribed_until'   => now()->addMonth(),
        ]);
    }

    /** Administrateur (niveau 8) */
    public function admin(): static
    {
        return $this->state(fn () => [
            'subscription_level' => 8,
            'subscribed_until'   => now()->addYears(10),
        ]);
    }
}
