<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

/**
 * Commande : app:install
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Automatise l'installation complète de reception-par-type.ch.
 *
 * Usage :
 *   php artisan app:install                    → Installation interactive
 *   php artisan app:install --fresh            → Repart de zéro (drop + migrate)
 *   php artisan app:install --no-interaction   → Mode silencieux (CI/CD)
 *
 * Étapes :
 *   1. Vérification des prérequis système
 *   2. Préparation du fichier .env
 *   3. Génération de la clé d'application
 *   4. Test de la connexion à la base de données
 *   5. Migrations
 *   6. Seeders (prix + admin)
 *   7. Création des dossiers de stockage
 *   8. Publication des assets
 *   9. Vidage des caches
 *  10. Récapitulatif final
 * ─────────────────────────────────────────────────────────────────────────────
 */
class InstallCommand extends Command
{
    protected $signature = 'app:install
                            {--fresh   : Repart de zéro — DROP toutes les tables avant migration}
                            {--force   : Passe toutes les confirmations (mode CI/CD)}
                            {--skip-admin : Ne crée pas le compte administrateur}';

    protected $description = 'Installe et configure reception-par-type.ch (migrations, seeders, admin, caches)';

    // Couleurs ANSI pour les sorties console
    private const OK    = '<fg=green>✓</>';
    private const WARN  = '<fg=yellow>⚠</>';
    private const ERR   = '<fg=red>✗</>';
    private const INFO  = '<fg=cyan>›</>';
    private const ARROW = '<fg=blue>→</>';

    public function handle(): int
    {
        $this->displayBanner();

        // ── Confirmation en mode --fresh ──────────────────────────────────────
        if ($this->option('fresh') && !$this->option('force')) {
            $this->newLine();
            $this->line('  <fg=red;options=bold>ATTENTION : --fresh va supprimer TOUTES les données existantes.</>');
            if (!$this->confirm('  Continuer ?', false)) {
                $this->line('  <fg=yellow>Installation annulée.</> ');
                return self::FAILURE;
            }
        }

        $this->newLine();

        try {
            $this->step1CheckRequirements();
            $this->step2PrepareEnv();
            $this->step3GenerateKey();
            $this->step4TestDatabase();
            $this->step5RunMigrations();
            $this->step6RunSeeders();
            $this->step7CreateDirectories();
            $this->step8PublishAssets();
            $this->step9ClearCaches();
            $this->step10CreateAdmin();
            $this->displaySuccess();

        } catch (\Throwable $e) {
            $this->newLine();
            $this->line("  " . self::ERR . " <fg=red>Erreur fatale : {$e->getMessage()}</>");
            $this->line("  " . self::INFO . " Fichier : {$e->getFile()}:{$e->getLine()}");
            $this->newLine();
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Étape 1 : Prérequis système
    // ══════════════════════════════════════════════════════════════════════════

    private function step1CheckRequirements(): void
    {
        $this->section('Vérification des prérequis');

        $checks = [
            'PHP ≥ 8.2'           => fn() => version_compare(PHP_VERSION, '8.2.0', '>='),
            'Extension PDO'        => fn() => extension_loaded('pdo'),
            'Extension PDO MySQL'  => fn() => extension_loaded('pdo_mysql'),
            'Extension mbstring'   => fn() => extension_loaded('mbstring'),
            'Extension openssl'    => fn() => extension_loaded('openssl'),
            'Extension fileinfo'   => fn() => extension_loaded('fileinfo'),
            'Extension intl'       => fn() => extension_loaded('intl'),
            'Extension zip'        => fn() => extension_loaded('zip'),
            'Dossier storage/ writable' => fn() => is_writable(storage_path()),
            'Dossier bootstrap/cache/ writable' => fn() => is_writable(base_path('bootstrap/cache')),
        ];

        $allPassed = true;
        foreach ($checks as $label => $check) {
            $passed = $check();
            $icon   = $passed ? self::OK : self::ERR;
            $color  = $passed ? 'green' : 'red';
            $this->line("    {$icon} <fg={$color}>{$label}</>");
            if (!$passed) {
                $allPassed = false;
            }
        }

        // Warnings non-bloquants
        $warnings = [
            'Extension redis'    => fn() => extension_loaded('redis'),
            'dompdf/dompdf'      => fn() => class_exists('\Dompdf\Dompdf'),
            'PHP memory ≥ 256Mo' => fn() => $this->parseMemoryLimit(ini_get('memory_limit')) >= 256,
        ];

        foreach ($warnings as $label => $check) {
            $passed = $check();
            $icon   = $passed ? self::OK : self::WARN;
            $color  = $passed ? 'green' : 'yellow';
            $this->line("    {$icon} <fg={$color}>{$label}" . ($passed ? '' : ' (recommandé)') . "</>");
        }

        if (!$allPassed) {
            throw new \RuntimeException('Prérequis manquants. Corrigez les erreurs ci-dessus avant de continuer.');
        }

        $this->line("  " . self::OK . " <fg=green>Tous les prérequis sont satisfaits.</>");
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Étape 2 : Préparation du .env
    // ══════════════════════════════════════════════════════════════════════════

    private function step2PrepareEnv(): void
    {
        $this->section('Configuration de l\'environnement (.env)');

        $envPath     = base_path('.env');
        $examplePath = base_path('.env.example');

        if (File::exists($envPath)) {
            $this->line("  " . self::OK . " Fichier <fg=cyan>.env</> déjà présent — conservé.");
            return;
        }

        if (!File::exists($examplePath)) {
            throw new \RuntimeException('.env.example introuvable. Le projet semble incomplet.');
        }

        File::copy($examplePath, $envPath);
        $this->line("  " . self::OK . " Fichier <fg=cyan>.env</> créé depuis <fg=cyan>.env.example</>.");
        $this->line("  " . self::WARN . " <fg=yellow>Pensez à configurer les variables (DB, PayPal, SMTP) avant de continuer.</>");

        if (!$this->option('force') && !$this->option('no-interaction')) {
            $this->newLine();
            $this->line("  " . self::INFO . " <fg=cyan>Variables minimales à configurer dans <fg=white>.env</> :</>");
            foreach (['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $var) {
                $this->line("    <fg=yellow>{$var}</>");
            }
            $this->newLine();
            $this->confirm('  Avez-vous configuré la connexion BDD dans .env ?', true);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Étape 3 : Clé d'application
    // ══════════════════════════════════════════════════════════════════════════

    private function step3GenerateKey(): void
    {
        $this->section('Clé d\'application Laravel');

        $currentKey = config('app.key');

        if ($currentKey && strlen($currentKey) > 10) {
            $this->line("  " . self::OK . " Clé d'application déjà présente.");
            return;
        }

        Artisan::call('key:generate', ['--force' => true]);
        $this->line("  " . self::OK . " <fg=green>Clé d'application générée (AES-256-CBC).</>");
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Étape 4 : Connexion BDD
    // ══════════════════════════════════════════════════════════════════════════

    private function step4TestDatabase(): void
    {
        $this->section('Connexion à la base de données');

        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            $driver = DB::connection()->getDriverName();
            $this->line("  " . self::OK . " <fg=green>Connexion {$driver} établie → base : <fg=cyan>{$dbName}</></>");
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Impossible de se connecter à la base de données : {$e->getMessage()}\n" .
                "  → Vérifiez DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD dans .env"
            );
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Étape 5 : Migrations
    // ══════════════════════════════════════════════════════════════════════════

    private function step5RunMigrations(): void
    {
        $this->section('Migrations de la base de données');

        $args = ['--force' => true];

        if ($this->option('fresh')) {
            $this->line("  " . self::WARN . " <fg=yellow>Mode --fresh : suppression et recréation de toutes les tables...</>");
            Artisan::call('migrate:fresh', $args);
            $this->line("  " . self::OK . " <fg=green>Tables recréées depuis zéro.</>");
        } else {
            Artisan::call('migrate', $args);
            $output = Artisan::output();

            if (str_contains($output, 'Nothing to migrate')) {
                $this->line("  " . self::OK . " Migrations déjà à jour — aucune modification.");
            } else {
                // Compter les migrations exécutées
                $count = substr_count($output, 'Migrating:');
                $this->line("  " . self::OK . " <fg=green>{$count} migration(s) exécutée(s).</>");
            }
        }

        // Afficher un résumé des tables créées
        try {
            $tables = DB::select('SHOW TABLES');
            $count  = count($tables);
            $this->line("  " . self::INFO . " <fg=cyan>{$count} tables présentes en base.</>");
        } catch (\Throwable) {
            // Non-fatal : MySQL uniquement
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Étape 6 : Seeders
    // ══════════════════════════════════════════════════════════════════════════

    private function step6RunSeeders(): void
    {
        $this->section('Initialisation des données de référence');

        $seeders = [
            \Database\Seeders\PricingPlanSeeder::class => 'Plans tarifaires (8 niveaux)',
        ];

        foreach ($seeders as $class => $description) {
            if (!class_exists($class)) {
                $this->line("  " . self::WARN . " <fg=yellow>{$description}</> — classe introuvable, ignoré.");
                continue;
            }

            try {
                Artisan::call('db:seed', [
                    '--class' => $class,
                    '--force' => true,
                ]);
                $this->line("  " . self::OK . " <fg=green>{$description}</> — initialisés.");
            } catch (\Throwable $e) {
                $this->line("  " . self::WARN . " <fg=yellow>{$description}</> — {$e->getMessage()}");
            }
        }

        // Seed des traductions de véhicules ASTRA si le seeder existe
        if (class_exists(\Database\Seeders\VehicleTranslationSeeder::class)) {
            Artisan::call('db:seed', ['--class' => \Database\Seeders\VehicleTranslationSeeder::class, '--force' => true]);
            $this->line("  " . self::OK . " <fg=green>Traductions ASTRA</> — initialisées.");
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Étape 7 : Dossiers de stockage
    // ══════════════════════════════════════════════════════════════════════════

    private function step7CreateDirectories(): void
    {
        $this->section('Création des répertoires de stockage');

        $directories = [
            storage_path('app/astra/2000')   => 'Imports ASTRA — dossier 2000 (mensuel)',
            storage_path('app/astra/5000')   => 'Imports ASTRA — dossier 5000 (newsletter)',
            storage_path('app/invoices')     => 'Factures PDF (disque privé)',
            storage_path('logs')             => 'Journaux',
            public_path('sitemaps')          => 'Sitemaps XML',
        ];

        foreach ($directories as $path => $description) {
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0755, true);
                $this->line("  " . self::OK . " <fg=green>Créé</> : <fg=cyan>{$description}</>");
            } else {
                $this->line("  " . self::OK . " Existant : <fg=cyan>{$description}</>");
            }
        }

        // Lien symbolique storage → public
        try {
            Artisan::call('storage:link', ['--force' => true]);
            $this->line("  " . self::OK . " <fg=green>Lien symbolique</> <fg=cyan>public/storage</> → <fg=cyan>storage/app/public</> créé.");
        } catch (\Throwable) {
            $this->line("  " . self::WARN . " <fg=yellow>Lien symbolique storage déjà existant ou non nécessaire.</>");
        }

        // Fichiers .gitkeep dans les dossiers ASTRA vides
        foreach ([storage_path('app/astra/2000'), storage_path('app/astra/5000')] as $dir) {
            $keepFile = $dir . '/.gitkeep';
            if (!File::exists($keepFile)) {
                File::put($keepFile, '');
            }
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Étape 8 : Publication des assets
    // ══════════════════════════════════════════════════════════════════════════

    private function step8PublishAssets(): void
    {
        $this->section('Publication des assets et configuration');

        // Publication config Sanctum
        Artisan::call('vendor:publish', [
            '--provider' => 'Laravel\Sanctum\SanctumServiceProvider',
            '--force'    => true,
        ]);
        $this->line("  " . self::OK . " <fg=green>Laravel Sanctum</> — configuration publiée.");

        // Optimisation de l'autoloader Composer
        $this->line("  " . self::INFO . " <fg=cyan>Optimisation de l'autoloader Composer...</>");
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Étape 9 : Vidage des caches
    // ══════════════════════════════════════════════════════════════════════════

    private function step9ClearCaches(): void
    {
        $this->section('Vidage des caches');

        $cacheCommands = [
            'config:clear'     => 'Cache de configuration',
            'route:clear'      => 'Cache des routes',
            'view:clear'       => 'Cache des vues Blade',
            'event:clear'      => 'Cache des événements',
            'cache:clear'      => 'Cache applicatif (Redis/file)',
        ];

        foreach ($cacheCommands as $command => $label) {
            try {
                Artisan::call($command);
                $this->line("  " . self::OK . " <fg=green>{$label}</> vidé.");
            } catch (\Throwable $e) {
                $this->line("  " . self::WARN . " <fg=yellow>{$label}</> — {$e->getMessage()}");
            }
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Étape 10 : Création du compte administrateur
    // ══════════════════════════════════════════════════════════════════════════

    private function step10CreateAdmin(): void
    {
        if ($this->option('skip-admin')) {
            return;
        }

        $this->section('Compte administrateur');

        // Vérifier si un admin (niveau 8) existe déjà
        $adminExists = \App\Models\User::where('subscription_level', 8)->exists();

        if ($adminExists) {
            $this->line("  " . self::OK . " Un compte administrateur existe déjà — ignoré.");
            return;
        }

        $this->newLine();
        $this->line("  <fg=cyan>Création du compte administrateur (niveau 8) :</>");
        $this->newLine();

        // En mode non-interactif : utiliser les valeurs .env ou des défauts
        if ($this->option('force') || $this->option('no-interaction')) {
            $name     = env('ADMIN_NAME', 'Administrateur');
            $email    = env('ADMIN_EMAIL', 'admin@reception-par-type.ch');
            $password = env('ADMIN_PASSWORD', 'ChangeMe1234!');
        } else {
            $name     = $this->ask('    Nom complet', 'Administrateur');
            $email    = $this->ask('    Adresse e-mail', 'admin@reception-par-type.ch');
            $password = $this->secret('    Mot de passe (min. 12 caractères)');

            if (!$password || strlen($password) < 12) {
                $this->line("  " . self::WARN . " <fg=yellow>Mot de passe trop court — utilisation d'un mot de passe temporaire.</>");
                $password = 'ChangeMe1234!';
            }
        }

        \App\Models\User::create([
            'name'               => $name,
            'email'              => $email,
            'password'           => Hash::make($password),
            'email_verified_at'  => now(),    // Admin vérifié d'office
            'subscription_level' => 8,
            'subscribed_until'   => now()->addYears(10),
            'preferred_locale'   => 'fr',
        ]);

        $this->line("  " . self::OK . " <fg=green>Compte admin créé :</>");
        $this->line("     <fg=white>E-mail    :</> <fg=cyan>{$email}</>");
        $this->line("     <fg=white>Niveau    :</> <fg=cyan>8 (Administrateur)</>");

        if ($this->option('force') || $this->option('no-interaction')) {
            $this->line("     <fg=white>Mot de passe :</> <fg=yellow>{$password} ← À changer immédiatement !</>");
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Affichage final
    // ══════════════════════════════════════════════════════════════════════════

    private function displaySuccess(): void
    {
        $this->newLine();
        $this->line('  <fg=green;options=bold>╔════════════════════════════════════════════════════╗</>');
        $this->line('  <fg=green;options=bold>║     ✓  Installation terminée avec succès !         ║</>');
        $this->line('  <fg=green;options=bold>╚════════════════════════════════════════════════════╝</>');
        $this->newLine();

        $this->line('  <fg=cyan;options=bold>Prochaines étapes :</>');
        $this->newLine();
        $this->line('  <fg=yellow>1.</> Configurez les variables PayPal dans <fg=cyan>.env</> :');
        $this->line('     PAYPAL_CLIENT_ID, PAYPAL_CLIENT_SECRET, PAYPAL_WEBHOOK_ID');
        $this->newLine();
        $this->line('  <fg=yellow>2.</> Configurez le SMTP pour les e-mails :');
        $this->line('     MAIL_MAILER, MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD');
        $this->newLine();
        $this->line('  <fg=yellow>3.</> Ajoutez le cron Laravel Scheduler :');
        $this->line('     <fg=cyan>* * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1</>');
        $this->newLine();
        $this->line('  <fg=yellow>4.</> Lancez les workers de queue :');
        $this->line('     <fg=cyan>php artisan queue:work redis --queue=imports,invoices,default</>');
        $this->newLine();
        $this->line('  <fg=yellow>5.</> Déposez le fichier ASTRA dans :');
        $this->line('     <fg=cyan>' . storage_path('app/astra/2000/TG-Automobil.txt') . '</>');
        $this->line('     Puis lancez : <fg=cyan>php artisan astra:import --type=main --force</>');
        $this->newLine();
        $this->line('  <fg=yellow>6.</> Démarrez le serveur de développement :');
        $this->line('     <fg=cyan>php artisan serve</>  →  <fg=white>http://localhost:8000</>');
        $this->newLine();
        $this->line('  <fg=green>Interface admin :</> <fg=cyan>http://localhost:8000/admin</>');
        $this->newLine();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Helpers visuels
    // ══════════════════════════════════════════════════════════════════════════

    private function displayBanner(): void
    {
        $this->newLine();
        $this->line('  <fg=blue;options=bold>┌─────────────────────────────────────────────────────┐</>');
        $this->line('  <fg=blue;options=bold>│</>  <fg=white;options=bold>réception-par-type.ch</> <fg=blue>— Installateur v1.0</> <fg=blue;options=bold>       │</>');
        $this->line('  <fg=blue;options=bold>│</>  <fg=cyan>Données techniques automobiles ASTRA — Suisse</>  <fg=blue;options=bold>   │</>');
        $this->line('  <fg=blue;options=bold>│</>  <fg=gray>Laravel 11 / PHP 8.2+ / MySQL / Redis</>  <fg=blue;options=bold>         │</>');
        $this->line('  <fg=blue;options=bold>└─────────────────────────────────────────────────────┘</>');
        $this->newLine();
        $this->line('  <fg=cyan>PHP</> ' . PHP_VERSION . '  <fg=cyan>·  Laravel</> ' . app()->version());
        $this->newLine();
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->line("  <fg=blue;options=bold>── {$title} ──</>");
    }

    /**
     * Convertit une limite mémoire PHP (ex: "256M", "1G") en MB.
     */
    private function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1') return PHP_INT_MAX;
        $limit = strtolower(trim($limit));
        $value = (int) $limit;
        $unit  = substr($limit, -1);
        return match($unit) {
            'g' => $value * 1024,
            'm' => $value,
            'k' => (int) ($value / 1024),
            default => (int) ($value / 1048576),
        };
    }
}
