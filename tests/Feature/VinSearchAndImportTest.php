<?php

use App\Models\Vehicle;
use App\Services\AstraFileParser;

/**
 * Tests des nouvelles fonctionnalités : recherche VIN, type véhicule, émissions.
 * (S'exécutent dans l'app Laravel complète ; ignorés en lint statique isolé.)
 */

describe('Recherche par VIN (troncature 9 caractères)', function () {

    it('tronque un VIN complet de 17 caractères à ses 9 premiers', function () {
        // VIN complet de 17 caractères → seuls les 9 premiers sont conservés
        expect(Vehicle::normalizeVinPrefix('WVWZZZ1JZ3W386752'))->toBe('WVWZZZ1JZ');
    });

    it('met en majuscules et retire les séparateurs', function () {
        expect(Vehicle::normalizeVinPrefix('wvw zzz-1jz'))->toBe('WVWZZZ1JZ');
    });

    it('renvoie null pour une chaîne vide', function () {
        expect(Vehicle::normalizeVinPrefix(''))->toBeNull()
            ->and(Vehicle::normalizeVinPrefix(null))->toBeNull();
    });

    it('le parser délègue la troncature VIN au modèle', function () {
        expect(AstraFileParser::truncateVin('WVWZZZ1JZ3W386752'))->toBe('WVWZZZ1JZ');
    });

    it('le scope byVin matche le bon véhicule via une égalité exacte sur 9 caractères', function () {
        $target = Vehicle::factory()->create(['vin_prefix' => 'WVWZZZ1JZ']);
        Vehicle::factory()->create(['vin_prefix' => 'ZAR93200N']);

        // On cherche avec un VIN COMPLET : la troncature doit ramener au préfixe
        $found = Vehicle::byVin('WVWZZZ1JZ3W386752')->get();

        expect($found)->toHaveCount(1)
            ->and($found->first()->id)->toBe($target->id);
    })->skip(fn() => !class_exists(Vehicle::class), 'Hors app Laravel');

    it('le scope byVin ne renvoie rien si le VIN est trop court (sécurité)', function () {
        Vehicle::factory()->create(['vin_prefix' => 'WVWZZZ1JZ']);
        expect(Vehicle::byVin('WVW')->get())->toHaveCount(0);
    })->skip(fn() => !class_exists(Vehicle::class), 'Hors app Laravel');
});

describe('Discrimination voiture / moto', function () {

    it('distingue les voitures des motos via le scope ofType', function () {
        Vehicle::factory()->count(2)->create();                    // voitures
        Vehicle::factory()->motorcycle()->count(3)->create();      // motos

        expect(Vehicle::ofType('car')->count())->toBe(2)
            ->and(Vehicle::ofType('motorcycle')->count())->toBe(3);
    })->skip(fn() => !class_exists(Vehicle::class), 'Hors app Laravel');

    it('expose des helpers isCar / isMotorcycle cohérents', function () {
        $moto = Vehicle::factory()->motorcycle()->make();
        expect($moto->isMotorcycle())->toBeTrue()
            ->and($moto->isCar())->toBeFalse();
    })->skip(fn() => !class_exists(Vehicle::class), 'Hors app Laravel');
});

describe('Parsing du fichier des émissions', function () {

    it('extrait TG + CO2 + norme et ignore une ligne sans donnée d\'émission', function () {
        $headers = ['tg_nummer', 'co2', 'abgasnorm'];

        $ok = AstraFileParser::parseEmissionLine($headers, ['00.082.000.09.00011', '142', 'Euro 6d-ISC-FCM']);
        expect($ok)->not->toBeNull()
            ->and($ok['numero_tg'])->toBe('00.082.000.09.00011')
            ->and($ok['co2'])->toBe(142)
            ->and($ok['pollution_norm'])->toBe('Euro 6d-ISC-FCM');

        // Ligne avec TG mais aucune donnée d'émission → ignorée
        $empty = AstraFileParser::parseEmissionLine($headers, ['00.082.000.09.00011', '', '']);
        expect($empty)->toBeNull();
    });

    it('ignore une ligne d\'émission sans numéro TG', function () {
        $headers = ['tg_nummer', 'co2'];
        expect(AstraFileParser::parseEmissionLine($headers, ['', '120']))->toBeNull();
    });
});
