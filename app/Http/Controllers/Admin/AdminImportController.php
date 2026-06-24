<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Jobs\ImportAstraMainJob;
use App\Jobs\ImportAstraNewsletterJob;
use App\Models\ImportLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Controller : AdminImportController
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Interface d'administration pour déclencher manuellement les imports ASTRA.
 * Accessible uniquement aux utilisateurs avec subscription_level = 8 (Entreprise/Admin).
 *
 * Route : /admin/import
 * Middleware : auth + admin (défini dans routes/web.php)
 * ─────────────────────────────────────────────────────────────────────────────
 */
class AdminImportController extends BaseController
{
    /**
     * Affiche le tableau de bord des imports.
     * GET /admin/import
     */
    public function index(): View
    {
        // Récupération des 50 derniers imports pour le tableau de bord
        $recentImports = ImportLog::orderByDesc('created_at')
            ->paginate(50);

        // Statistiques globales (cached 5 min — counters change infrequently)
        $stats = Cache::remember('admin_import_stats', 300, function () {
            return [
                'total_2000'    => ImportLog::where('import_type', '2000')->count(),
                'total_5000'    => ImportLog::where('import_type', '5000')->count(),
                'last_2000'     => ImportLog::where('import_type', '2000')
                    ->completed()
                    ->latest('created_at')
                    ->first(),
                'last_5000'     => ImportLog::where('import_type', '5000')
                    ->completed()
                    ->latest('created_at')
                    ->first(),
                'running_jobs'  => ImportLog::where('status', 'running')->count(),
                'failed_recent' => ImportLog::where('status', 'failed')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
            ];
        });

        // Vérification de l'état des fichiers ASTRA sur disque
        $diskStatus = $this->checkDiskStatus();

        return $this->renderView(
            'admin.import.index',
            compact('recentImports', 'stats', 'diskStatus'),
            'Gestion des Imports ASTRA | Admin',
            'Tableau de bord des imports de données ASTRA'
        );
    }

    /**
     * Déclenche manuellement un import.
     * POST /admin/import
     */
    public function trigger(Request $request): RedirectResponse
    {
        // ── Autorisation ──────────────────────────────────────────────────────
        // Seuls les admins (niveau 8) peuvent déclencher des imports manuels
        if (!auth()->user()?->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        // ── Validation de la requête ──────────────────────────────────────────
        $validated = $request->validate([
            'import_type'   => ['required', 'in:2000,5000'],
            'specific_file' => ['nullable', 'string', 'max:500'],
            'force'         => ['sometimes', 'boolean'],
        ]);

        $importType   = $validated['import_type'];
        $specificFile = $validated['specific_file'] ?? null;
        $force        = (bool) ($validated['force'] ?? false);
        $triggeredBy  = 'admin:' . auth()->id();

        // ── Vérification : import déjà en cours ? ────────────────────────────
        $runningImport = ImportLog::where('import_type', $importType)
            ->where('status', 'running')
            ->exists();

        if ($runningImport && !$force) {
            return redirect()
                ->route('admin.import.index')
                ->with('error', "⚠️ Un import de type {$importType} est déjà en cours. Attendez sa fin ou utilisez 'Forcer'.");
        }

        // ── Validation du fichier spécifique (sécurité path traversal) ───────
        if ($specificFile !== null) {
            $specificFile = $this->validateAndSanitizePath($specificFile);
            if ($specificFile === null) {
                return redirect()
                    ->route('admin.import.index')
                    ->with('error', '❌ Chemin de fichier invalide ou non autorisé.');
            }
        }

        // ── Dispatch du Job approprié ─────────────────────────────────────────
        if ($importType === '2000') {
            $filePath = $specificFile
                ?? config('astra.paths.main_file', storage_path('app/astra/2000/TG-Automobil.txt'));

            if (!file_exists($filePath)) {
                return redirect()
                    ->route('admin.import.index')
                    ->with('error', "❌ Fichier principal introuvable : {$filePath}");
            }

            ImportAstraMainJob::dispatch($filePath, $triggeredBy)
                ->onQueue('imports-heavy');

            $message = "✅ Import principal (2000) déclenché manuellement. Traitement en arrière-plan.";

        } else {
            $directory = config('astra.paths.5000', storage_path('app/astra/5000/'));

            ImportAstraNewsletterJob::dispatch($directory, $triggeredBy, $specificFile)
                ->onQueue('imports');

            $message = "✅ Import newsletter (5000) déclenché manuellement.";
        }

        Cache::forget('admin_import_stats');

        return redirect()
            ->route('admin.import.index')
            ->with('success', $message);
    }

    /**
     * Affiche le détail d'un import spécifique.
     * GET /admin/import/{id}
     */
    public function show(ImportLog $importLog): View
    {
        return $this->renderView(
            'admin.import.show',
            ['importLog' => $importLog],
            "Import #{$importLog->id} — {$importLog->filename}",
            ''
        );
    }

    /**
     * Rejoue un import échoué.
     * POST /admin/import/{id}/retry
     */
    public function retry(ImportLog $importLog): RedirectResponse
    {
        if (!auth()->user()?->isAdmin()) {
            abort(403);
        }

        if (!in_array($importLog->status, ['failed', 'partial'])) {
            return redirect()
                ->route('admin.import.index')
                ->with('error', 'Seuls les imports échoués ou partiels peuvent être rejoués.');
        }

        if ($importLog->import_type === '2000') {
            ImportAstraMainJob::dispatch(
                $importLog->file_path,
                'admin:' . auth()->id() . ':retry'
            )->onQueue('imports-heavy');
        } else {
            ImportAstraNewsletterJob::dispatch(
                dirname($importLog->file_path) . '/',
                'admin:' . auth()->id() . ':retry',
                $importLog->file_path
            )->onQueue('imports');
        }

        return redirect()
            ->route('admin.import.index')
            ->with('success', "✅ Import #{$importLog->id} relancé.");
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    /**
     * Vérifie l'état des fichiers ASTRA sur disque.
     */
    private function checkDiskStatus(): array
    {
        $dir2000    = config('astra.paths.2000', storage_path('app/astra/2000/'));
        $dir5000    = config('astra.paths.5000', storage_path('app/astra/5000/'));
        $mainFile   = config('astra.paths.main_file', storage_path('app/astra/2000/TG-Automobil.txt'));

        return [
            'dir_2000_exists'    => is_dir($dir2000),
            'dir_5000_exists'    => is_dir($dir5000),
            'main_file_exists'   => file_exists($mainFile),
            'main_file_size'     => file_exists($mainFile)
                ? round(filesize($mainFile) / 1_048_576, 1) . ' Mo'
                : null,
            'main_file_modified' => file_exists($mainFile)
                ? \Carbon\Carbon::createFromTimestamp(filemtime($mainFile))->diffForHumans()
                : null,
            'newsletter_files'   => is_dir($dir5000)
                ? count(glob($dir5000 . '*.txt') ?: [])
                : 0,
        ];
    }

    /**
     * Valide et sanitise un chemin de fichier pour éviter le path traversal.
     * Seuls les fichiers dans les répertoires autorisés sont acceptés.
     *
     * @return string|null Chemin réel validé, ou null si invalide
     */
    private function validateAndSanitizePath(string $inputPath): ?string
    {
        // Résolution du chemin réel (supprime les ../ et liens symboliques)
        $realPath = realpath($inputPath);

        if ($realPath === false) {
            return null;
        }

        // Répertoires autorisés (définis dans config/astra.php)
        $allowedDirectories = [
            realpath(config('astra.paths.2000', storage_path('app/astra/2000/'))),
            realpath(config('astra.paths.5000', storage_path('app/astra/5000/'))),
        ];

        foreach ($allowedDirectories as $allowedDir) {
            if ($allowedDir && str_starts_with($realPath, $allowedDir)) {
                return $realPath;
            }
        }

        return null; // Chemin hors des répertoires autorisés
    }
}
