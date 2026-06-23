<?php

/**
 * Configuration Pest — reception-par-type.ch
 * tests/Pest.php
 */

use Illuminate\Foundation\Testing\RefreshDatabase;

// ── Application du TestCase Laravel à tous les tests ─────────────────────────
uses(Tests\TestCase::class)->in('Feature', 'Unit');

// ── Expectations personnalisées ──────────────────────────────────────────────

/**
 * Vérifie qu'une chaîne est un ULID valide (26 chars Crockford Base32).
 */
expect()->extend('toBeUlid', function () {
    return $this->toBeString()
        ->toMatch('/^[0-9A-HJKMNP-TV-Z]{26}$/');
});

/**
 * Vérifie qu'un montant en centimes correspond à un montant CHF attendu.
 */
expect()->extend('toBeChf', function (float $expectedChf) {
    return $this->toBe((int) round($expectedChf * 100));
});

// ── Helpers globaux ──────────────────────────────────────────────────────────

/**
 * Crée un utilisateur abonné à un niveau donné, avec abonnement actif.
 */
function subscribedUser(int $level = 4): \App\Models\User
{
    return \App\Models\User::factory()->create([
        'subscription_level' => $level,
        'email_verified_at'  => now(),
        'subscribed_until'   => now()->addMonth(),
    ]);
}
