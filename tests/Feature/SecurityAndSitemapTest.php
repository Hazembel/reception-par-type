<?php

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SUITE 1 — Sécurité, ULID & Anti-Cloaking (Modules 1 & 5)
 *
 * Vérifie :
 *   - Les véhicules utilisent un ULID (26 chars) comme PK, pas un ID séquentiel
 *   - L'ULID n'apparaît JAMAIS dans les URLs (slug uniquement)
 *   - Un visiteur non autorisé ne voit AUCUNE donnée premium dans le HTML source
 *   - Les balises hreflang sont générées correctement
 * ═══════════════════════════════════════════════════════════════════════════
 */

// ── ULID ─────────────────────────────────────────────────────────────────────

describe('Sécurité ULID (Module 1)', function () {

    it('génère un ULID de 26 caractères comme clé primaire', function () {
        $vehicle = Vehicle::factory()->create();

        // Un ULID fait exactement 26 caractères alphanumériques (Crockford Base32)
        expect($vehicle->id)->toBeString()
            ->and(strlen($vehicle->id))->toBe(26)
            ->and($vehicle->id)->toMatch('/^[0-9A-HJKMNP-TV-Z]{26}$/');
    });

    it('n\'utilise PAS d\'auto-increment entier', function () {
        $vehicle = Vehicle::factory()->create();

        expect($vehicle->getIncrementing())->toBeFalse()
            ->and($vehicle->getKeyType())->toBe('string');
    });

    it('résout les routes par slug et non par ULID', function () {
        $vehicle = Vehicle::factory()->create();

        // getRouteKeyName doit retourner 'slug' pour ne jamais exposer l'ULID
        expect($vehicle->getRouteKeyName())->toBe('slug');
    });

    it('génère un slug SEO automatiquement à la création', function () {
        $vehicle = Vehicle::factory()->create([
            'marque'    => 'Volkswagen',
            'modele'    => 'Golf',
            'variante'  => '2.0 TDI',
            'numero_tg' => '27.012.000.08.00004',
            'slug'      => null,
        ]);

        expect($vehicle->slug)->not->toBeNull()
            ->and($vehicle->slug)->toContain('volkswagen')
            ->and($vehicle->slug)->toContain('golf');
    });

    it('garantit l\'unicité des slugs en cas de collision', function () {
        $v1 = Vehicle::factory()->create([
            'marque' => 'Audi', 'modele' => 'A3', 'variante' => 'Sport',
            'numero_tg' => '27.111.000.08.00001', 'slug' => null,
        ]);
        $v2 = Vehicle::factory()->create([
            'marque' => 'Audi', 'modele' => 'A3', 'variante' => 'Sport',
            'numero_tg' => '27.111.000.08.00001', 'slug' => null,
        ]);

        expect($v1->slug)->not->toBe($v2->slug);
    });
});

// ── Anti-Cloaking : aucune fuite de données premium ──────────────────────────

describe('Anti-Cloaking — masquage strict côté serveur (Module 5)', function () {

    beforeEach(function () {
        $this->vehicle = Vehicle::factory()->create([
            'numero_tg'         => '27.012.000.08.00004',
            'marque'            => 'Volkswagen',
            'modele'            => 'Golf',
            'is_active'         => true,
            // Données premium qui ne DOIVENT PAS fuiter
            'poids_vide'        => 1320,
            'poids_total'       => 1900,
            'poids_remorquable' => 1600,
            'entraxe'           => 112,
            'alesage'           => 57,
            'deport_et'         => 45,
            'pneus_origine'     => '205/55R16 91V',
        ]);
    });

    it('ne fait PAS fuiter les données premium dans le HTML pour un visiteur anonyme', function () {
        $locale = app()->getLocale();
        $response = $this->get("/{$locale}/vehicle/{$this->vehicle->slug}");

        $response->assertOk();
        $html = $response->getContent();

        // AUCUNE des valeurs premium ne doit apparaître dans le HTML source
        expect($html)
            ->not->toContain('1320')      // poids_vide
            ->not->toContain('1\'320')    // poids_vide formaté
            ->not->toContain('1600')      // poids_remorquable
            ->not->toContain('205/55R16') // pneus
            ->not->toContain('112')       // entraxe (attention aux faux positifs)
            ->not->toContain('ET45');     // déport
    });

    it('ne fait PAS fuiter les données premium pour un utilisateur niveau 1 connecté', function () {
        $user = User::factory()->create(['subscription_level' => 1, 'email_verified_at' => now()]);
        $locale = app()->getLocale();

        $response = $this->actingAs($user)->get("/{$locale}/vehicle/{$this->vehicle->slug}");

        $response->assertOk();
        $html = $response->getContent();

        expect($html)
            ->not->toContain('205/55R16')
            ->not->toContain('1600 kg');
    });

    it('AUTORISE les données premium pour un abonné Business (niveau 6)', function () {
        $user = User::factory()->create([
            'subscription_level' => 6,
            'email_verified_at'  => now(),
            'subscribed_until'   => now()->addMonth(),
        ]);
        $locale = app()->getLocale();

        $response = $this->actingAs($user)->get("/{$locale}/vehicle/{$this->vehicle->slug}");

        $response->assertOk();
        // Pour un abonné, les données DOIVENT être présentes
        $response->assertSee('205/55R16');
    });

    it('affiche les données publiques (motorisation) même pour un anonyme', function () {
        $locale = app()->getLocale();
        $response = $this->get("/{$locale}/vehicle/{$this->vehicle->slug}");

        $response->assertOk()
                 ->assertSee('Volkswagen')
                 ->assertSee('Golf');
    });
});

// ── Hreflang ─────────────────────────────────────────────────────────────────

describe('Balises hreflang multilingues (Module 1)', function () {

    it('génère les 4 balises hreflang + x-default sur la page d\'accueil', function () {
        $response = $this->get('/fr');
        $response->assertOk();
        $html = $response->getContent();

        // Les 4 langues + x-default doivent être présentes
        expect($html)
            ->toContain('hreflang="fr-CH"')
            ->toContain('hreflang="de-CH"')
            ->toContain('hreflang="it-CH"')
            ->toContain('hreflang="en"')
            ->toContain('hreflang="x-default"');
    });

    it('pointe les balises hreflang vers les bonnes URLs localisées', function () {
        $response = $this->get('/fr');
        $html = $response->getContent();

        expect($html)
            ->toContain('/fr')
            ->toContain('/de')
            ->toContain('/it')
            ->toContain('/en');
    });

    it('rejette une locale invalide avec une erreur 400', function () {
        // Le middleware SetLocale doit refuser les locales hors whitelist
        $response = $this->get('/xx/vehicle/test');
        $response->assertStatus(400);
    });

    it('accepte les 4 locales valides', function () {
        foreach (['fr', 'de', 'it', 'en'] as $locale) {
            $response = $this->get("/{$locale}");
            expect($response->status())->toBeIn([200, 302]); // 200 ou redirect, jamais 400
        }
    });
});
