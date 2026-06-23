<?php

use App\Services\TyreService;
use App\Services\CantonalTaxService;

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SUITE 3 — Intelligence métier (Module 6)
 *
 * Vérifie :
 *   - Le calcul mathématique exact du diamètre de pneu (formule ETRTO)
 *   - La tolérance légale suisse stricte de ±8%
 *   - Le simulateur d'impôt cantonal
 * ═══════════════════════════════════════════════════════════════════════════
 */

describe('TyreService — calcul du diamètre (formule ETRTO)', function () {

    beforeEach(fn() => $this->tyre = new TyreService());

    it('calcule exactement le diamètre de 205/55R16', function () {
        // Flanc   = 205 × 0.55          = 112.75 mm
        // Ø jante = 16 × 25.4           = 406.4 mm
        // Ø total = (112.75 × 2) + 406.4 = 631.9 mm
        $diameter = $this->tyre->calculateDiameter(205, 55, 16);

        expect($diameter)->toBeGreaterThan(631.8)
            ->and($diameter)->toBeLessThan(632.0);
    });

    it('calcule exactement le diamètre de 225/45R17', function () {
        // Flanc = 225 × 0.45 = 101.25 ; Ø jante = 431.8 ; total = 634.3
        $diameter = $this->tyre->calculateDiameter(225, 45, 17);

        expect(round($diameter, 1))->toBe(634.3);
    });

    it('décode correctement une dimension complète 205/55R16 91V', function () {
        $decoded = $this->tyre->decode('205/55R16 91V');

        expect($decoded['width'])->toBe(205)
            ->and($decoded['series'])->toBe(55)
            ->and($decoded['rim_diameter'])->toBe(16)
            ->and($decoded['load_index'])->toBe(91)
            ->and($decoded['speed_index'])->toBe('V');
    });

    it('lève une exception pour une dimension invalide', function () {
        $this->tyre->decode('pas-un-pneu');
    })->throws(InvalidArgumentException::class);
});

describe('TyreService — tolérance légale suisse ±8% (ASA)', function () {

    beforeEach(fn() => $this->tyre = new TyreService());

    it('ne propose QUE des alternatives dans la tolérance de ±8%', function () {
        $alternatives = $this->tyre->getLegalAlternatives('205/55R16 91V');

        expect($alternatives)->not->toBeEmpty();

        // Chaque alternative DOIT respecter la tolérance ±8%
        foreach ($alternatives as $alt) {
            expect(abs($alt['deviation_pct']))->toBeLessThanOrEqual(8.0)
                ->and($alt['is_legal'])->toBeTrue();
        }
    });

    it('exclut une dimension hors tolérance (ex: +10% de diamètre)', function () {
        $alternatives = $this->tyre->getLegalAlternatives('205/55R16 91V');
        $original     = $this->tyre->decode('205/55R16 91V');

        // Aucune alternative ne doit dépasser de plus de 8% le diamètre original
        $maxDiameter = $original['diameter_mm'] * 1.08;
        $minDiameter = $original['diameter_mm'] * 0.92;

        foreach ($alternatives as $alt) {
            expect($alt['diameter_mm'])->toBeGreaterThanOrEqual($minDiameter - 0.5)
                ->and($alt['diameter_mm'])->toBeLessThanOrEqual($maxDiameter + 0.5);
        }
    });

    it('calcule l\'écart au compteur de vitesse', function () {
        $original = 631.9;
        $larger   = $original * 1.05; // +5%

        $deviation = $this->tyre->calculateSpeedometerDeviation($original, $larger);

        // À 100 km/h indiqués, la vitesse réelle est ~5% plus élevée
        expect($deviation['percent'])->toBeGreaterThan(4.5)
            ->and($deviation['percent'])->toBeLessThan(5.5);
    });

    it('limite le nombre d\'alternatives à 20 maximum', function () {
        $alternatives = $this->tyre->getLegalAlternatives('205/55R16 91V');
        expect(count($alternatives))->toBeLessThanOrEqual(20);
    });
});

describe('CantonalTaxService — simulateur d\'impôt', function () {

    beforeEach(fn() => $this->tax = new CantonalTaxService());

    $golf = [
        'poids_vide'   => 1320,
        'puissance_kw' => 110,
        'cylindree'    => 1968,
        'co2'          => 128,
        'energie'      => '02',
    ];

    it('calcule un impôt cohérent pour Zürich (basé sur le poids)', function () use ($golf) {
        $result = $this->tax->calculate('ZH', $golf);

        expect($result['canton'])->toBe('ZH')
            ->and($result['amount_chf'])->toBeGreaterThan(0)
            ->and($result['amount_chf'])->toBeLessThan(2000); // Bornes raisonnables
    });

    it('calcule un impôt cohérent pour Vaud (basé sur la puissance)', function () use ($golf) {
        $result = $this->tax->calculate('VD', $golf);

        expect($result['canton'])->toBe('VD')
            ->and($result['amount_chf'])->toBeGreaterThan(0);
    });

    it('applique le montant minimum de 20 CHF', function () {
        $tiny = ['poids_vide' => 50, 'puissance_kw' => 3, 'cylindree' => 50, 'co2' => 5, 'energie' => '01'];
        $result = $this->tax->calculate('ZH', $tiny);

        expect($result['amount_chf'])->toBeGreaterThanOrEqual(20);
    });

    it('arrondit le montant à 5 CHF près', function () use ($golf) {
        $result = $this->tax->calculate('VD', $golf);

        // Le montant final doit être un multiple de 5
        expect($result['amount_chf'] % 5)->toBe(0.0);
    });

    it('rejette un canton inconnu', function () use ($golf) {
        $this->tax->calculate('XX', $golf);
    })->throws(InvalidArgumentException::class);

    it('calcule tous les cantons et les trie par montant croissant', function () use ($golf) {
        $results = $this->tax->calculateAll($golf);

        $amounts = array_values(array_filter(
            array_column($results, 'amount_chf'),
            fn($a) => $a !== null
        ));

        $sorted = $amounts;
        sort($sorted);

        expect($amounts)->toBe($sorted)
            ->and(count($results))->toBeGreaterThanOrEqual(10);
    });

    it('applique un bonus pour les véhicules électriques', function () {
        $electric = [
            'poids_vide' => 1800, 'puissance_kw' => 150, 'cylindree' => 0,
            'co2' => 0, 'energie' => '14', // 14 = électrique
        ];
        $combustion = [
            'poids_vide' => 1800, 'puissance_kw' => 150, 'cylindree' => 2000,
            'co2' => 180, 'energie' => '02',
        ];

        $resultElec = $this->tax->calculate('GE', $electric);
        $resultComb = $this->tax->calculate('GE', $combustion);

        // À Genève, l'électrique bénéficie d'une exonération/réduction
        expect($resultElec['amount_chf'])->toBeLessThanOrEqual($resultComb['amount_chf']);
    });
});

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SUITE AJOUTÉE PAR L'AUDIT — Blindage du TyreService (Module 6)
 *
 * Cas limites : espaces parasites, indices manquants, chaînes vides, valeurs
 * aberrantes, formats US. Le service DOIT lever InvalidArgumentException sur
 * toute entrée non conforme, et tolérer les variations d'espacement valides.
 * ═══════════════════════════════════════════════════════════════════════════
 */
describe('TyreService — robustesse et cas limites (audit)', function () {

    beforeEach(fn() => $this->tyre = new \App\Services\TyreService());

    it('tolère les espaces parasites autour du R et des indices', function () {
        // Espaces en trop : doivent être normalisés, pas rejetés
        $decoded = $this->tyre->decode('  205/55 R 16  91 V  ');
        expect($decoded['width'])->toBe(205)
            ->and($decoded['series'])->toBe(55)
            ->and($decoded['rim_diameter'])->toBe(16);
    });

    it('accepte une dimension sans indices de charge/vitesse', function () {
        // Indices manquants = valides (optionnels), load/speed à null
        $decoded = $this->tyre->decode('195/65R15');
        expect($decoded['width'])->toBe(195)
            ->and($decoded['load_index'])->toBeNull()
            ->and($decoded['speed_index'])->toBeNull();
    });

    it('gère le préfixe P du marché US', function () {
        $decoded = $this->tyre->decode('P225/45R17');
        expect($decoded['width'])->toBe(225);
    });

    it('reconnaît le suffixe XL (charge renforcée)', function () {
        $decoded = $this->tyre->decode('225/45R17 94W XL');
        expect($decoded['is_xl'])->toBeTrue();
    });

    it('REJETTE une chaîne vide', function () {
        $this->tyre->decode('');
    })->throws(InvalidArgumentException::class);

    it('REJETTE une chaîne ne contenant que des espaces', function () {
        $this->tyre->decode('     ');
    })->throws(InvalidArgumentException::class);

    it('REJETTE un format texte sans structure', function () {
        $this->tyre->decode('quatre pneus neufs');
    })->throws(InvalidArgumentException::class);

    it('REJETTE une largeur aberrante (hors 100–400)', function () {
        // Largeur 999 : syntaxiquement plausible mais physiquement impossible
        $this->tyre->decode('999/55R16');
    })->throws(InvalidArgumentException::class);

    it('REJETTE une série de flanc aberrante (hors 20–90)', function () {
        $this->tyre->decode('205/05R16');
    })->throws(InvalidArgumentException::class);

    it('REJETTE un diamètre de jante aberrant (hors 10–24)', function () {
        $this->tyre->decode('205/55R99');
    })->throws(InvalidArgumentException::class);

    it('produit un diamètre arrondi à 2 décimales (pas de flottant sale)', function () {
        $decoded = $this->tyre->decode('205/55R16');
        // round(…, 2) garantit au plus 2 décimales
        $rounded = round($decoded['diameter_mm'], 2);
        expect($decoded['diameter_mm'])->toBe($rounded);
    });
});
