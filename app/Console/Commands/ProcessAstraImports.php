<?php

namespace App\Console\Commands;

use App\Jobs\ImportAstraEmissionsJob;
use App\Jobs\ImportAstraMainJob;
use App\Jobs\ImportAstraNewsletterJob;
use App\Jobs\ImportAstraVerbrauchJob;
use App\Models\ImportLog;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Console Command : ProcessAstraImports
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Commande d'automatisation du Scheduler Laravel pour les imports ASTRA.
 *
 * Modes d'exécution (--type) :
 *   newsletter  → ImportAstraNewsletterJob       (dossier 5000)
 *   main        → ImportAstraMainJob (voitures)   (TG-Automobil.txt)
 *   moto        → ImportAstraMainJob (motos)      (TG-Moto.txt)
 *   emissions   → ImportAstraEmissionsJob         (emissionen.txt)
 *   verbrauch   → ImportAstraVerbrauchJob         (verbrauch.txt)
 *   all         → main → moto → emissions → verbrauch (cet ordre est important :
 *                 les imports d'enrichissement doivent suivre les imports TG)
 *
 * Options :
 *   --force            → Ignore les vérifications de planning
 *   --file=<path>      → Force un fichier spécifique
 *   --triggered-by=... → Origine du déclenchement (journalisation)
 *
 * Planification dans routes/console.php :
 *   Schedule::command('astra:import --type=newsletter')->weekly()->mondays()->at('06:00');
 *   Schedule::command('astra:import --type=all')->monthlyOn(10, '02:00');
 * ─────────────────────────────────────────────────────────────────────────────
 */
class ProcessAstraImports extends Command
{
    protected $signature = 'astra:import
                            {--type=newsletter : Type d\'import (newsletter|main|moto|emissions|verbrauch|all)}
                            {--force           : Force l\'import même si déjà fait récemment}
                            {--file=           : Chemin d\'un fichier spécifique à importer}
                            {--triggered-by=scheduler : Origine du déclenchement}';

    protected $description = 'Lance les imports ASTRA (newsletter, voitures, motos, émissions)';

    // ── Exécution ─────────────────────────────────────────────────────────────

    public function handle(): int
    {
        $type         = (string) $this->option('type');
        $force        = (bool) $this->option('force');
        $specificFile = $this->option('file');
        $triggeredBy  = $this->option('triggered-by') ?? 'scheduler';

        $this->info("🚗 Import ASTRA — Type : {$type}" . ($force ? ' (forcé)' : ''));

        Log::channel('imports')->info('Commande astra:import déclenchée', [
            'type'          => $type,
            'force'         => $force,
            'specific_file' => $specificFile,
            'triggered_by'  => $triggeredBy,
        ]);

        return match ($type) {
            'newsletter' => $this->runNewsletterImport($force, $triggeredBy, $specificFile),
            'main'       => $this->runMainImport($force, $triggeredBy, $specificFile),
            'moto'       => $this->runMotoImport($force, $triggeredBy, $specificFile),
            'emissions'  => $this->runEmissionsImport($force, $triggeredBy, $specificFile),
            'verbrauch'  => $this->runVerbrauchImport($force, $triggeredBy, $specificFile),
            'all'        => $this->runAll($force, $triggeredBy),
            default      => $this->invalidType($type),
        };
    }

    // ── Import Newsletter (Dossier 5000) ──────────────────────────────────────

    private function runNewsletterImport(bool $force, string $triggeredBy, ?string $specificFile): int
    {
        $directory = config('astra.paths.5000', storage_path('app/astra/5000/'));

        if (!$force) {
            $recentImport = ImportLog::where('import_type', '5000')
                ->whereIn('status', ['completed', 'partial'])
                ->where('created_at', '>=', now()->startOfWeek())
                ->exists();

            if ($recentImport) {
                $this->info('✓ Import newsletter déjà effectué cette semaine. Utilisez --force pour forcer.');
                return self::SUCCESS;
            }
        }

        if (!is_dir($directory) && $specificFile === null) {
            $this->error("❌ Répertoire 5000 introuvable : {$directory}");
            return self::FAILURE;
        }

        $this->line("📁 Répertoire 5000 : {$directory}");

        ImportAstraNewsletterJob::dispatch($directory, $triggeredBy, $specificFile)
            ->onQueue('imports');

        $this->info('✅ ImportAstraNewsletterJob dispatché en queue.');

        return self::SUCCESS;
    }

    // ── Import Principal Voitures (TG-Automobil.txt) ──────────────────────────

    private function runMainImport(bool $force, string $triggeredBy, ?string $specificFile): int
    {
        $filePath = $specificFile
            ?? config('astra.paths.main_file', storage_path('app/astra/2000/TG-Automobil.txt'));

        if (!$force) {
            $recentImport = ImportLog::where('import_type', '2000')
                ->whereIn('status', ['completed', 'partial'])
                ->where('created_at', '>=', now()->startOfMonth())
                ->exists();

            if ($recentImport) {
                $this->info('✓ Import voitures déjà effectué ce mois-ci. Utilisez --force pour forcer.');
                return self::SUCCESS;
            }
        }

        if (!file_exists($filePath)) {
            $this->error("❌ Fichier voitures introuvable : {$filePath}");
            return self::FAILURE;
        }

        $fileSize = round(filesize($filePath) / 1_048_576, 1);
        $this->line("📄 Voitures : {$filePath} ({$fileSize} Mo)");
        $this->warn('⚠️  Traitement en arrière-plan (peut prendre 1-3h pour 300 Mo)');

        ImportAstraMainJob::dispatch($filePath, $triggeredBy, false, Vehicle::TYPE_CAR)
            ->onQueue('imports-heavy');

        $this->info('✅ ImportAstraMainJob (voitures) dispatché en queue imports-heavy.');

        return self::SUCCESS;
    }

    // ── Import Motos (TG-Moto.txt) ────────────────────────────────────────────

    private function runMotoImport(bool $force, string $triggeredBy, ?string $specificFile): int
    {
        $filePath = $specificFile
            ?? config('astra.paths.moto_file', storage_path('app/astra/2000/TG-Moto.txt'));

        if (!$force) {
            $recentImport = ImportLog::where('import_type', '2000-moto')
                ->whereIn('status', ['completed', 'partial'])
                ->where('created_at', '>=', now()->startOfMonth())
                ->exists();

            if ($recentImport) {
                $this->info('✓ Import motos déjà effectué ce mois-ci. Utilisez --force pour forcer.');
                return self::SUCCESS;
            }
        }

        if (!file_exists($filePath)) {
            $this->error("❌ Fichier motos introuvable : {$filePath}");
            $this->line("Configurez 'astra.paths.moto_file' dans config/astra.php");
            return self::FAILURE;
        }

        $fileSize = round(filesize($filePath) / 1_048_576, 1);
        $this->line("🏍️  Motos : {$filePath} ({$fileSize} Mo)");

        // Même job, mais marqué TYPE_MOTORCYCLE → toutes les lignes seront des motos.
        ImportAstraMainJob::dispatch($filePath, $triggeredBy, false, Vehicle::TYPE_MOTORCYCLE)
            ->onQueue('imports-heavy');

        $this->info('✅ ImportAstraMainJob (motos) dispatché en queue imports-heavy.');

        return self::SUCCESS;
    }

    // ── Import des Émissions (emissionen.txt) ─────────────────────────────────

    private function runEmissionsImport(bool $force, string $triggeredBy, ?string $specificFile): int
    {
        $filePath = $specificFile
            ?? config('astra.paths.emissions_file', storage_path('app/astra/2000/emissionen.txt'));

        if (!$force) {
            $recentImport = ImportLog::where('import_type', 'emissions')
                ->whereIn('status', ['completed', 'partial'])
                ->where('created_at', '>=', now()->startOfMonth())
                ->exists();

            if ($recentImport) {
                $this->info('✓ Import émissions déjà effectué ce mois-ci. Utilisez --force pour forcer.');
                return self::SUCCESS;
            }
        }

        if (!file_exists($filePath)) {
            $this->error("❌ Fichier émissions introuvable : {$filePath}");
            $this->line("Configurez 'astra.paths.emissions_file' dans config/astra.php");
            return self::FAILURE;
        }

        $fileSize = round(filesize($filePath) / 1_048_576, 1);
        $this->line("🌱 Émissions : {$filePath} ({$fileSize} Mo)");

        ImportAstraEmissionsJob::dispatch($filePath, $triggeredBy)
            ->onQueue('imports-heavy');

        $this->info('✅ ImportAstraEmissionsJob dispatché en queue imports-heavy.');

        return self::SUCCESS;
    }

    // ── Import Consommation (verbrauch.txt) ───────────────────────────────────

    private function runVerbrauchImport(bool $force, string $triggeredBy, ?string $specificFile): int
    {
        $filePath = $specificFile
            ?? config('astra.paths.verbrauch_file', storage_path('app/astra/2000/verbrauch.txt'));

        if (!$force) {
            $recentImport = ImportLog::where('import_type', 'verbrauch')
                ->whereIn('status', ['completed', 'partial'])
                ->where('created_at', '>=', now()->startOfMonth())
                ->exists();

            if ($recentImport) {
                $this->info('✓ Import consommation déjà effectué ce mois-ci. Utilisez --force pour forcer.');
                return self::SUCCESS;
            }
        }

        if (!file_exists($filePath)) {
            $this->error("❌ Fichier consommation introuvable : {$filePath}");
            $this->line("Configurez 'astra.paths.verbrauch_file' dans config/astra.php");
            return self::FAILURE;
        }

        $fileSize = round(filesize($filePath) / 1_048_576, 1);
        $this->line("⛽ Consommation : {$filePath} ({$fileSize} Mo)");

        ImportAstraVerbrauchJob::dispatch($filePath, $triggeredBy)
            ->onQueue('imports-heavy');

        $this->info('✅ ImportAstraVerbrauchJob dispatché en queue imports-heavy.');

        return self::SUCCESS;
    }

    // ── Import complet : voitures → motos → émissions → consommation ──────────

    private function runAll(bool $force, string $triggeredBy): int
    {
        $this->line('--- 1/4 Voitures (TG-Automobil.txt) ---');
        $r1 = $this->runMainImport($force, $triggeredBy, null);

        $this->line('--- 2/4 Motos (TG-Moto.txt) ---');
        $r2 = $this->runMotoImport($force, $triggeredBy, null);

        $this->line('--- 3/4 Émissions (emissionen.txt) ---');
        $this->warn('ℹ️  Les émissions sont couplées aux fiches TG : assurez-vous que les imports voitures/motos sont terminés avant que ce job ne s\'exécute (queue séquentielle recommandée).');
        $r3 = $this->runEmissionsImport($force, $triggeredBy, null);

        $this->line('--- 4/4 Consommation (verbrauch.txt) ---');
        $r4 = $this->runVerbrauchImport($force, $triggeredBy, null);

        return ($r1 === self::SUCCESS && $r2 === self::SUCCESS && $r3 === self::SUCCESS && $r4 === self::SUCCESS)
            ? self::SUCCESS
            : self::FAILURE;
    }

    private function invalidType(string $type): int
    {
        $this->error("Type d'import invalide : '{$type}'. Utilisez : newsletter, main, moto, emissions, verbrauch, ou all.");
        return self::INVALID;
    }
}
