<?php

use Illuminate\Support\Facades\Schedule;

/**
 * Routes Console (Scheduler) : reception-par-type.ch
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Planification automatisée des imports ASTRA.
 *
 * Prérequis : Le cron Laravel doit être actif sur le serveur.
 * Ajouter dans crontab (crontab -e) :
 *   * * * * * cd /var/www/reception-par-type && php artisan schedule:run >> /dev/null 2>&1
 * ─────────────────────────────────────────────────────────────────────────────
 */

// ── Import Newsletter hebdomadaire (Dossier 5000) ─────────────────────────────
/**
 * Exécution : chaque lundi à 06h00
 * Justification du lundi : l'ASTRA publie les nouvelles homologations en début de semaine.
 * La tâche s'exécute tôt le matin pour éviter les pics de trafic.
 *
 * withoutOverlapping() : empêche l'exécution simultanée si le job précédent est encore actif.
 * runInBackground()    : ne bloque pas le scheduler si le job est long.
 * onOneServer()        : si plusieurs serveurs → exécution sur un seul (nécessite le driver Redis/DB).
 */
Schedule::command('astra:import --type=newsletter --triggered-by=scheduler')
    ->weekly()
    ->mondays()
    ->at('06:00')
    ->withoutOverlapping(10)   // Verrou de 10 minutes max
    ->runInBackground()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/astra-newsletter.log'))
    ->name('astra-newsletter-import')
    ->description('Import hebdomadaire des nouvelles homologations ASTRA (dossier 5000)');

// ── Import Principal mensuel (Dossier 2000) ───────────────────────────────────
/**
 * Exécution : le 10 de chaque mois à 02h00
 * Justification :
 *  - Tôt le matin : trafic minimal → moins d'impact sur les performances BDD
 *  - Le 10 : laisse le temps à l'ASTRA de publier le fichier mensuel complet
 *  - Queue séparée (imports-heavy) : ne bloque pas les autres jobs
 *
 * withoutOverlapping(300) : verrou de 5h (timeout du Job lui-même).
 */
Schedule::command('astra:import --type=main --triggered-by=scheduler')
    ->monthlyOn(10, '02:00')
    ->withoutOverlapping(300)  // Verrou de 5 heures (durée max du gros import)
    ->runInBackground()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/astra-main.log'))
    ->name('astra-main-import')
    ->description('Import mensuel complet ASTRA (dossier 2000, fichier ~300 Mo)');

// ── Nettoyage des anciens logs d'import ──────────────────────────────────────
/**
 * Suppression des entrées imports_log de plus de 6 mois.
 * Exécuté le 1er de chaque mois à 03h00.
 */
Schedule::call(function () {
    \App\Models\ImportLog::where('created_at', '<', now()->subMonths(6))->delete();
})
    ->monthlyOn(1, '03:00')
    ->name('cleanup-import-logs')
    ->description('Purge des anciens logs d\'import (> 6 mois)');

// ── Alerte si aucune newsletter cette semaine (vendredi soir) ────────────────
/**
 * Vérifie que la newsletter a bien été importée cette semaine.
 * Si non → log d'alerte (à connecter à Slack/email en MODULE suivant).
 */
Schedule::call(function () {
    $imported = \App\Models\ImportLog::where('import_type', '5000')
        ->whereIn('status', ['completed', 'partial'])
        ->where('created_at', '>=', now()->startOfWeek())
        ->exists();

    if (!$imported) {
        \Illuminate\Support\Facades\Log::channel('imports')
            ->warning('[ALERTE] Aucune newsletter ASTRA importée cette semaine !', [
                'week' => now()->weekOfYear,
                'year' => now()->year,
            ]);
    }
})
    ->weekly()
    ->fridays()
    ->at('18:00')
    ->name('check-newsletter-imported')
    ->description('Vérifie que la newsletter ASTRA a bien été importée cette semaine');
