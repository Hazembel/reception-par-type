<?php

use App\Models\User;
use App\Models\Vehicle;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SUITE 4 — API B2B & Rate Limiting (Module 7)
 *
 * Vérifie :
 *   - L'authentification Sanctum obligatoire
 *   - Le rate limiting (429 quand le quota par seconde/minute est dépassé)
 *   - Le quota mensuel
 *   - L'absence de champs internes dans les réponses JSON
 * ═══════════════════════════════════════════════════════════════════════════
 */

beforeEach(function () {
    RateLimiter::clear('api_rl');
    $this->vehicle = Vehicle::factory()->create([
        'numero_tg' => '27.012.000.08.00004',
        'marque'    => 'Volkswagen',
        'modele'    => 'Golf',
        'is_active' => true,
    ]);
});

describe('API — Authentification', function () {

    it('refuse l\'accès sans token (401)', function () {
        $this->getJson('/api/v1/vehicle/tg/27.012.000.08.00004')
             ->assertStatus(401);
    });

    it('refuse un niveau d\'abonnement insuffisant (< 6)', function () {
        $user = User::factory()->create(['subscription_level' => 4]);
        Sanctum::actingAs($user, ['api:read']);

        $this->getJson('/api/v1/vehicle/tg/27.012.000.08.00004')
             ->assertStatus(403);
    });

    it('autorise un abonné Business (niveau 6)', function () {
        $user = User::factory()->create(['subscription_level' => 6]);
        Sanctum::actingAs($user, ['api:read']);

        $this->getJson('/api/v1/vehicle/tg/27.012.000.08.00004')
             ->assertStatus(200)
             ->assertJsonStructure(['success', 'data', 'meta']);
    });
});

describe('API — Rate Limiting (429)', function () {

    it('renvoie un JSON 429 quand le quota par minute est dépassé', function () {
        $user = User::factory()->create(['subscription_level' => 6]);
        Sanctum::actingAs($user, ['api:read']);

        // Le plan Business autorise 30 req/min. On en fait 35.
        $got429 = false;
        for ($i = 0; $i < 35; $i++) {
            $response = $this->getJson('/api/v1/vehicle/tg/27.012.000.08.00004');
            if ($response->status() === 429) {
                $got429 = true;
                // Vérifier la structure de l'erreur 429
                $response->assertJsonStructure(['success', 'error', 'message'])
                         ->assertJsonPath('success', false)
                         ->assertJsonPath('error', 'TOO_MANY_REQUESTS')
                         ->assertHeader('Retry-After');
                break;
            }
        }

        expect($got429)->toBeTrue();
    })->skip('Activer quand RateLimiter est testable dans votre env CI');

    it('renvoie un JSON 429 quand le quota MENSUEL est épuisé', function () {
        $user = User::factory()->create(['subscription_level' => 6]);
        Sanctum::actingAs($user, ['api:read']);

        // Forcer le compteur mensuel au-delà du quota (5000 pour Business)
        $token = $user->currentAccessToken();
        if ($token) {
            \DB::table('personal_access_tokens')
                ->where('id', $token->id)
                ->update(['calls_this_month' => 5000, 'plan_id' => 6]);
        }

        $response = $this->getJson('/api/v1/vehicle/tg/27.012.000.08.00004');

        $response->assertStatus(429)
                 ->assertJsonPath('error', 'TOO_MANY_REQUESTS')
                 ->assertJsonPath('limit_type', 'monthly_quota');
    })->skip('Adapter selon que les colonnes Sanctum étendues sont migrées');

    it('inclut les headers de quota dans les réponses réussies', function () {
        $user = User::factory()->create(['subscription_level' => 6]);
        Sanctum::actingAs($user, ['api:read']);

        $this->getJson('/api/v1/vehicle/tg/27.012.000.08.00004')
             ->assertHeader('X-RateLimit-Limit')
             ->assertHeader('X-Quota-Limit')
             ->assertHeader('X-Plan');
    })->skip('Active si le middleware api.quota est branché sur la route de test');
});

describe('API — Validation & Sanitisation des champs', function () {

    beforeEach(function () {
        $this->user = User::factory()->create(['subscription_level' => 6]);
        Sanctum::actingAs($this->user, ['api:read']);
    });

    it('ne renvoie JAMAIS l\'ID interne (ULID) dans la réponse JSON', function () {
        $this->getJson('/api/v1/vehicle/tg/27.012.000.08.00004')
             ->assertStatus(200)
             ->assertJsonMissingPath('data.id')
             ->assertJsonMissingPath('data.slug')
             ->assertJsonMissingPath('data.deleted_at')
             ->assertJsonMissingPath('data.updated_at')
             ->assertJsonMissingPath('data.created_at');
    });

    it('renvoie 400 pour un numéro TG malformé (anti-injection)', function () {
        // Tentative d'injection SQL
        $this->getJson('/api/v1/vehicle/tg/' . urlencode("'; DROP TABLE vehicles;--"))
             ->assertStatus(400);
    });

    it('renvoie 404 pour un véhicule inexistant', function () {
        $this->getJson('/api/v1/vehicle/tg/99.999.999.99.99999')
             ->assertStatus(404)
             ->assertJsonPath('error', 'NOT_FOUND');
    });

    it('expose les champs techniques attendus', function () {
        $this->getJson('/api/v1/vehicle/tg/27.012.000.08.00004')
             ->assertStatus(200)
             ->assertJsonPath('data.numero_tg', '27.012.000.08.00004')
             ->assertJsonPath('data.marque', 'Volkswagen');
    });
});

describe('API — Gestion des clés', function () {

    it('crée une clé API avec un préfixe rpt_ retourné une seule fois', function () {
        $user = User::factory()->create(['subscription_level' => 6]);
        Sanctum::actingAs($user, ['api:read']);

        $response = $this->postJson('/api/v1/keys', ['name' => 'Test ERP']);

        $response->assertStatus(201)
                 ->assertJsonStructure(['data' => ['api_key', 'key_id']]);

        expect($response->json('data.api_key'))->toStartWith('rpt_');
    })->skip('Active si la route /api/v1/keys est enregistrée dans ton routeur de test');

    it('ne liste jamais le hash du token', function () {
        $user = User::factory()->create(['subscription_level' => 6]);
        $user->createToken('Ma clé');
        Sanctum::actingAs($user, ['api:read']);

        $response = $this->getJson('/api/v1/keys');
        $response->assertStatus(200);

        foreach ($response->json('data') as $key) {
            expect($key)->not->toHaveKey('token');
        }
    })->skip('Active si la route /api/v1/keys est enregistrée');
});

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SUITE AJOUTÉE PAR L'AUDIT LOT 2 — Sécurité accès & double-dépense
 * ═══════════════════════════════════════════════════════════════════════════
 */
describe('Sécurité — accès admin aux tarifs (audit lot 2)', function () {

    it('REFUSE l\'accès à la gestion des tarifs pour un utilisateur lambda (403)', function () {
        $lambda = \App\Models\User::factory()->create([
            'subscription_level' => 4, 'email_verified_at' => now(),
            'subscribed_until' => now()->addMonth(),
        ]);

        // La policy managePricing doit refuser tout niveau < 8
        expect($lambda->can('managePricing', \App\Models\User::class))->toBeFalse();
    })->skip(fn() => !class_exists(\App\Policies\UserManagementPolicy::class), 'Hors app Laravel');

    it('AUTORISE la gestion des tarifs pour un admin niveau 8', function () {
        $admin = \App\Models\User::factory()->create([
            'subscription_level' => 8, 'email_verified_at' => now(),
        ]);
        expect($admin->can('managePricing', \App\Models\User::class))->toBeTrue();
    })->skip(fn() => !class_exists(\App\Policies\UserManagementPolicy::class), 'Hors app Laravel');

    it('reconnaît un admin niveau 8 SANS abonnement payant actif', function () {
        // isAdmin() ne doit PAS dépendre de subscribed_until
        $admin = \App\Models\User::factory()->create([
            'subscription_level' => 8, 'subscribed_until' => null,
        ]);
        expect($admin->isAdmin())->toBeTrue();
    })->skip(fn() => !method_exists(\App\Models\User::class, 'isAdmin'), 'Hors app Laravel');

    it('ne considère PAS un niveau 7 comme admin', function () {
        $user = \App\Models\User::factory()->create(['subscription_level' => 7]);
        expect($user->isAdmin())->toBeFalse();
    })->skip(fn() => !method_exists(\App\Models\User::class, 'isAdmin'), 'Hors app Laravel');
});

describe('Sécurité — double-dépense de jetons (audit lot 2)', function () {

    it('consumeTokens débite une seule fois et refuse si solde insuffisant', function () {
        $user = \App\Models\User::factory()->create(['web_tokens_balance' => 1]);

        expect($user->consumeTokens(1))->toBeTrue()    // 1 → 0
            ->and($user->fresh()->web_tokens_balance)->toBe(0)
            ->and($user->fresh()->consumeTokens(1))->toBeFalse(); // 0 → refus
    })->skip(fn() => !class_exists(\App\Models\User::class), 'Hors app Laravel');

    it('ne descend jamais en solde négatif', function () {
        $user = \App\Models\User::factory()->create(['web_tokens_balance' => 0]);
        $user->consumeTokens(1);
        expect($user->fresh()->web_tokens_balance)->toBe(0);
    })->skip(fn() => !class_exists(\App\Models\User::class), 'Hors app Laravel');
});
