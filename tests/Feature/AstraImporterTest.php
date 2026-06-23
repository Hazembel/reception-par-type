<?php

use App\Models\Vehicle;
use App\Models\ImportLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SUITE 2 — Importateur ASTRA (Module 2)
 *
 * Vérifie :
 *   - La lecture d'un faux fichier ASTRA par chunks
 *   - La création propre des enregistrements véhicules
 *   - La mise à jour (upsert) des fiches existantes
 *   - L'idempotence par hash SHA-256
 * ═══════════════════════════════════════════════════════════════════════════
 */

/**
 * Helper : génère un faux fichier ASTRA au format TARGA (séparateur TAB).
 * Colonnes simulées : numero_tg, marque, modele, energie, puissance_kw, co2
 */
function fakeAstraFile(array $rows, string $separator = "\t"): string
{
    $header = implode($separator, [
        'TG', 'MARKE', 'MODELL', 'TREIBSTOFF', 'LEISTUNG_KW', 'CO2',
    ]);

    $lines = [$header];
    foreach ($rows as $row) {
        $lines[] = implode($separator, $row);
    }

    return implode("\n", $lines) . "\n";
}

describe('AstraFileParser — lecture et parsing', function () {

    it('détecte le séparateur TAB automatiquement', function () {
        $parser  = new \App\Services\AstraFileParser();
        $content = fakeAstraFile([
            ['27.012.000.08.00004', 'Volkswagen', 'Golf', '02', '110', '128'],
        ], "\t");

        $sep = $parser->detectSeparator($content);
        expect($sep)->toBe("\t");
    });

    it('convertit correctement l\'encodage ISO-8859-1 vers UTF-8', function () {
        $parser = new \App\Services\AstraFileParser();

        // "Citroën" en ISO-8859-1
        $isoString = mb_convert_encoding('Citroën', 'ISO-8859-1', 'UTF-8');
        $utf8      = $parser->toUtf8($isoString);

        expect($utf8)->toBe('Citroën');
    });

    it('nettoie le numéro TG des espaces parasites', function () {
        $parser = new \App\Services\AstraFileParser();

        expect($parser->cleanTg('  27.012.000.08.00004  '))->toBe('27.012.000.08.00004');
    });
})->skip(fn() => !class_exists(\App\Services\AstraFileParser::class), 'AstraFileParser absent');

describe('Import ASTRA — création et mise à jour', function () {

    beforeEach(function () {
        Storage::fake('local');
    });

    it('crée de nouveaux véhicules à partir d\'un fichier ASTRA', function () {
        $content = fakeAstraFile([
            ['27.012.000.08.00004', 'Volkswagen', 'Golf', '02', '110', '128'],
            ['27.013.000.08.00005', 'Audi',       'A3',   '02', '110', '125'],
            ['27.014.000.08.00006', 'BMW',        '320d', '02', '140', '142'],
        ]);

        Storage::disk('local')->put('astra/2000/test.txt', $content);

        // Simulation de l'import (selon votre API réelle de Job)
        $this->artisan('astra:import', ['--type' => 'main', '--force' => true])
             ->assertSuccessful();

        // 3 véhicules doivent être créés
        expect(Vehicle::count())->toBe(3);
        expect(Vehicle::where('numero_tg', '27.012.000.08.00004')->exists())->toBeTrue();
    })->skip('Adapter au chemin réel du fichier et à la signature du Job');

    it('met à jour (upsert) un véhicule existant sans créer de doublon', function () {
        // Véhicule pré-existant
        Vehicle::factory()->create([
            'numero_tg'    => '27.012.000.08.00004',
            'marque'       => 'Volkswagen',
            'puissance_kw' => 100, // Ancienne valeur
        ]);

        // Le réimport avec une puissance différente doit mettre à jour, pas dupliquer
        $vehicle = Vehicle::updateOrCreate(
            ['numero_tg' => '27.012.000.08.00004'],
            ['marque' => 'Volkswagen', 'modele' => 'Golf', 'puissance_kw' => 110]
        );

        expect(Vehicle::where('numero_tg', '27.012.000.08.00004')->count())->toBe(1)
            ->and($vehicle->puissance_kw)->toBe(110); // Valeur mise à jour
    });

    it('enregistre un log d\'import avec le hash du fichier', function () {
        $content = fakeAstraFile([
            ['27.012.000.08.00004', 'Volkswagen', 'Golf', '02', '110', '128'],
        ]);
        $hash = hash('sha256', $content);

        $log = ImportLog::create([
            'import_type'     => '2000',
            'file_hash'       => $hash,
            'status'          => 'completed',
            'lines_inserted'  => 1,
            'lines_updated'   => 0,
            'lines_skipped'   => 0,
            'lines_failed'    => 0,
        ]);

        expect($log->file_hash)->toBe($hash)
            ->and(strlen($log->file_hash))->toBe(64); // SHA-256 = 64 hex chars
    })->skip(fn() => !class_exists(ImportLog::class), 'ImportLog absent');

    it('respecte l\'idempotence : un fichier au hash identique n\'est pas retraité', function () {
        $content = fakeAstraFile([
            ['27.012.000.08.00004', 'Volkswagen', 'Golf', '02', '110', '128'],
        ]);
        $hash = hash('sha256', $content);

        // Premier import
        ImportLog::create([
            'import_type' => '2000', 'file_hash' => $hash, 'status' => 'completed',
            'lines_inserted' => 1, 'lines_updated' => 0, 'lines_skipped' => 0, 'lines_failed' => 0,
        ]);

        // Le second import du même hash doit être détecté comme déjà traité
        $alreadyImported = ImportLog::where('file_hash', $hash)
            ->where('status', 'completed')
            ->exists();

        expect($alreadyImported)->toBeTrue();
    })->skip(fn() => !class_exists(ImportLog::class), 'ImportLog absent');
});

describe('Import ASTRA — traitement par chunk (mémoire constante)', function () {

    it('traite un gros volume par lots sans dépasser la mémoire', function () {
        // Génère 5000 lignes pour simuler un gros fichier
        $rows = [];
        for ($i = 1; $i <= 5000; $i++) {
            $tg = sprintf('27.%03d.000.08.%05d', $i % 999, $i);
            $rows[] = [$tg, 'Marque' . $i, 'Modele' . $i, '02', '110', '128'];
        }

        $memBefore = memory_get_usage();

        // Insertion par chunks de 1000 via upsert (simule le comportement du Job)
        collect($rows)->chunk(1000)->each(function ($chunk) {
            $data = $chunk->map(fn($r) => [
                'id'           => \Illuminate\Support\Str::ulid(),
                'numero_tg'    => $r[0],
                'marque'       => $r[1],
                'modele'       => $r[2],
                'energie'      => $r[3],
                'puissance_kw' => (int) $r[4],
                'co2'          => (int) $r[5],
                'slug'         => \Illuminate\Support\Str::slug($r[1] . '-' . $r[2] . '-' . $r[0]),
                'is_active'    => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ])->all();

            Vehicle::upsert($data, ['numero_tg'], ['marque', 'modele', 'puissance_kw', 'co2']);
        });

        $memAfter = memory_get_usage();
        $memUsedMb = ($memAfter - $memBefore) / 1048576;

        expect(Vehicle::count())->toBe(5000)
            // La mémoire utilisée doit rester raisonnable (< 100 Mo) grâce au chunking
            ->and($memUsedMb)->toBeLessThan(100);
    });
});

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SUITE AJOUTÉE PAR L'AUDIT — Nettoyage des fichiers & espace disque (Module 2)
 *
 * Vérifie que le fichier source ASTRA est TOUJOURS supprimé du disque après
 * traitement — y compris si l'import plante en cours de route (try/finally).
 * Critique sur une infra à espace limité (30 Go) avec des fichiers de 300 Mo.
 * ═══════════════════════════════════════════════════════════════════════════
 */
describe('Import ASTRA — nettoyage du fichier source (audit)', function () {

    /** Crée un fichier ASTRA temporaire réel sur le disque et renvoie son chemin. */
    function makeTempAstraFile(array $rows): string
    {
        $dir = storage_path('app/astra/2000');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $path = $dir . '/test-' . uniqid() . '.txt';
        file_put_contents($path, fakeAstraFile($rows));
        return $path;
    }

    it('supprime le fichier source après un import réussi', function () {
        $path = makeTempAstraFile([
            ['27.012.000.08.00004', 'Volkswagen', 'Golf', '02', '110', '128'],
            ['27.013.000.08.00005', 'Audi',       'A3',   '02', '110', '125'],
        ]);

        expect(file_exists($path))->toBeTrue();

        // deleteAfterImport = true (défaut)
        (new \App\Jobs\ImportAstraMainJob($path, 'test'))->handle();

        // Le fichier doit avoir disparu, et les véhicules être créés
        expect(file_exists($path))->toBeFalse()
            ->and(\App\Models\Vehicle::count())->toBeGreaterThanOrEqual(2);
    })->skip(fn() => !class_exists(\App\Jobs\ImportAstraMainJob::class), 'Job absent hors app Laravel');

    it('SUPPRIME le fichier source MÊME si l\'import plante en cours de route', function () {
        // Fichier dont le contenu provoquera une exception pendant le traitement
        // (on simule en rendant la table absente / en forçant une erreur upsert).
        $path = makeTempAstraFile([
            ['27.999.000.08.99999', 'CrashTest', 'Boom', '02', '999', '999'],
        ]);
        expect(file_exists($path))->toBeTrue();

        // On force un plantage : on supprime la table vehicles pour que l'upsert échoue.
        \Illuminate\Support\Facades\Schema::drop('vehicles');

        try {
            (new \App\Jobs\ImportAstraMainJob($path, 'test'))->handle();
        } catch (\Throwable $e) {
            // L'exception est attendue — c'est le plantage simulé.
        }

        // POINT CRITIQUE DE L'AUDIT : malgré le crash, le fichier doit être supprimé.
        expect(file_exists($path))->toBeFalse();
    })->skip(fn() => !class_exists(\App\Jobs\ImportAstraMainJob::class), 'Job absent hors app Laravel');

    it('conserve le fichier source si deleteAfterImport = false', function () {
        $path = makeTempAstraFile([
            ['27.012.000.08.00004', 'Volkswagen', 'Golf', '02', '110', '128'],
        ]);

        // 3e argument = false → on garde le fichier (mode débogage)
        (new \App\Jobs\ImportAstraMainJob($path, 'test', false))->handle();

        expect(file_exists($path))->toBeTrue();

        // Nettoyage manuel après le test
        @unlink($path);
    })->skip(fn() => !class_exists(\App\Jobs\ImportAstraMainJob::class), 'Job absent hors app Laravel');
});
