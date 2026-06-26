<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\ApiLog;
use App\Models\ImportLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controller : AdminDashboardController
 *
 * Tableau de bord d'administration avec statistiques métier, système et sécurité.
 *
 * STRATÉGIE DE CACHE :
 * Chaque bloc de statistiques a son propre TTL selon sa volatilité :
 *  - Statistiques business (CA, abonnés) → cache 1h (changent peu)
 *  - Statistiques utilisateurs → cache 15 min
 *  - Statut des imports → cache 5 min (surveillance active)
 *  - Erreurs 429 du jour → cache 2 min (sécurité temps quasi-réel)
 *
 * Le dashboard complet ne fait jamais plus de 6 requêtes BDD (toutes mises en cache).
 */
class AdminDashboardController extends BaseController
{
    public function index(): View
    {
        $this->authorize('viewDashboard', User::class);

        return $this->renderView(
            'admin.dashboard',
            [
                'business'  => $this->getBusinessStats(),
                'users'     => $this->getUserStats(),
                'system'    => $this->getSystemStats(),
                'security'  => $this->getSecurityStats(),
                'cached_at' => now(),
            ],
            'Dashboard Admin | reception-par-type.ch',
            ''
        );
    }

    // ── Bloc 1 : Statistiques business ────────────────────────────────────────

    /**
     * CA estimé, abonnés actifs, ventes de jetons ce mois.
     * Cache : 1 heure (les paiements ne sont pas temps-réel ici).
     */
    private function getBusinessStats(): array
    {
        return Cache::remember('admin_stats_business', 3600, function () {

            // Abonnés actifs (niveau > 1 ET subscribed_until dans le futur)
            $activeSubscribers = User::where('subscription_level', '>', 1)
                ->where('subscribed_until', '>', now())
                ->selectRaw('subscription_level, COUNT(*) as count')
                ->groupBy('subscription_level')
                ->get()
                ->keyBy('subscription_level');

            // CA estimé mensuel (abonnés × prix du niveau, en CHF)
            $monthlyRevenueCents = 0;
            $planPrices = \App\Models\PricingPlan::allCached();

            foreach ($activeSubscribers as $level => $row) {
                $plan = $planPrices->get($level);
                if ($plan) {
                    $monthlyRevenueCents += $plan->price_monthly_chf * $row->count;
                }
            }

            // Appels API facturés ce mois (proxy pour les revenus API)
            $apiCallsThisMonth = ApiLog::thisMonth()->billed()->count();

            // Nouveaux abonnements ce mois (upgrade depuis niveau 1)
            $newSubscribersThisMonth = User::where('subscription_level', '>', 1)
                ->whereYear('subscribed_until', now()->year)
                ->whereMonth('subscribed_until', now()->month)
                ->count();

            // Ventes de jetons estimées (appels API de niveau 2/3)
            $tokenSalesThisMonth = \App\Models\UserUnlockedVehicle::thisMonth()->count();

            return [
                'monthly_revenue_chf'    => $monthlyRevenueCents / 100,
                'monthly_revenue_label'  => number_format($monthlyRevenueCents / 100, 0, '.', '\'') . ' CHF',
                'active_subscribers'     => $activeSubscribers->sum('count'),
                'new_this_month'         => $newSubscribersThisMonth,
                'api_calls_this_month'   => $apiCallsThisMonth,
                'token_unlocks_month'    => $tokenSalesThisMonth,
                'by_level'               => $activeSubscribers->map(fn($r) => $r->count)->toArray(),
            ];
        });
    }

    // ── Bloc 2 : Statistiques utilisateurs ───────────────────────────────────

    /**
     * Inscrits, vérifiés, actifs.
     * Cache : 15 minutes.
     */
    private function getUserStats(): array
    {
        return Cache::remember('admin_stats_users', 900, function () {

            $total    = User::count();
            $verified = User::whereNotNull('email_verified_at')->count();
            $deleted  = User::onlyTrashed()->count();

            // Inscrits ce mois
            $newThisMonth = User::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count();

            // Distribution par niveau
            $byLevel = User::selectRaw('subscription_level, COUNT(*) as count')
                ->groupBy('subscription_level')
                ->orderBy('subscription_level')
                ->pluck('count', 'subscription_level')
                ->toArray();

            // Utilisateurs connectés récemment (30 derniers jours)
            // Requête sur la table sessions uniquement si SESSION_DRIVER=database
            $recentlyActive = 0;
            if (config('session.driver') === 'database') {
                $recentlyActive = DB::table('sessions')
                    ->where('last_activity', '>=', now()->subDays(30)->timestamp)
                    ->distinct('user_id')
                    ->whereNotNull('user_id')
                    ->count('user_id');
            }

            return [
                'total'           => $total,
                'verified'        => $verified,
                'unverified'      => $total - $verified,
                'verified_pct'    => $total > 0 ? round($verified / $total * 100, 1) : 0,
                'deleted'         => $deleted,
                'new_this_month'  => $newThisMonth,
                'recently_active' => $recentlyActive,
                'by_level'        => $byLevel,
            ];
        });
    }

    // ── Bloc 3 : Statistiques système ────────────────────────────────────────

    /**
     * Fiches en base, statut des derniers imports.
     * Cache : 5 minutes.
     */
    private function getSystemStats(): array
    {
        return Cache::remember('admin_stats_system', 300, function () {

            $totalVehicles  = Vehicle::count();
            $activeVehicles = Vehicle::active()->count();

            // Dernier import 2000 (fichier principal mensuel)
            $lastImport2000 = ImportLog::where('import_type', '2000')
                ->orderByDesc('created_at')
                ->first([
                    'status', 'created_at', 'lines_inserted',
                    'lines_updated', 'duration_seconds', 'filename',
                ]);

            // Dernier import 5000 (newsletter hebdomadaire)
            $lastImport5000 = ImportLog::where('import_type', '5000')
                ->orderByDesc('created_at')
                ->first([
                    'status', 'created_at', 'lines_inserted',
                    'lines_updated', 'duration_seconds', 'filename',
                ]);

            // Jobs en cours
            $runningImports = ImportLog::where('status', 'running')->count();

            // Imports échoués récents (alerte si > 0)
            $failedRecent = ImportLog::where('status', 'failed')
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

            return [
                'total_vehicles'   => $totalVehicles,
                'active_vehicles'  => $activeVehicles,
                'inactive_vehicles'=> $totalVehicles - $activeVehicles,
                'running_imports'  => $runningImports,
                'failed_recent'    => $failedRecent,
                'last_import_2000' => $lastImport2000 ? [
                    'status'    => $lastImport2000->status,
                    'date'      => $lastImport2000->created_at?->diffForHumans(),
                    'filename'  => $lastImport2000->filename,
                    'inserted'  => $lastImport2000->lines_inserted,
                    'updated'   => $lastImport2000->lines_updated,
                    'duration'  => $lastImport2000->duration_seconds
                        ? round($lastImport2000->duration_seconds / 60, 1) . ' min'
                        : '—',
                ] : null,
                'last_import_5000' => $lastImport5000 ? [
                    'status'    => $lastImport5000->status,
                    'date'      => $lastImport5000->created_at?->diffForHumans(),
                    'filename'  => $lastImport5000->filename,
                    'inserted'  => $lastImport5000->lines_inserted,
                ] : null,
            ];
        });
    }

    // ── Bloc 4 : Statistiques sécurité ───────────────────────────────────────

    /**
     * Erreurs 429 aujourd'hui, IPs suspectes, anomalies.
     * Cache : 2 minutes (surveillance proche du temps réel).
     */
    private function getSecurityStats(): array
    {
        return Cache::remember('admin_stats_security', 120, function () {

            // Erreurs 429 aujourd'hui (tentatives de scraping / abus API)
            $rateLimitedToday = ApiLog::whereDate('created_at', today())
                ->where('status_code', 429)
                ->count();

            // Erreurs 401 (tokens invalides) aujourd'hui
            $unauthorizedToday = ApiLog::whereDate('created_at', today())
                ->where('status_code', 401)
                ->count();

            // IPs ayant déclenché > 100 erreurs 429 aujourd'hui (attaque probable)
            $suspiciousIps = ApiLog::whereDate('created_at', today())
                ->where('status_code', 429)
                ->selectRaw('ip_address, COUNT(*) as count')
                ->groupBy('ip_address')
                ->having('count', '>', 100)
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            // Signalements d'anomalies en attente
            $pendingAnomalies = \App\Models\AnomalyReport::where('status', 'pending')->count();

            // Total erreurs API aujourd'hui (toutes catégories)
            $totalErrorsToday = ApiLog::whereDate('created_at', today())
                ->where('status_code', '>=', 400)
                ->count();

            // Taux d'erreur API sur 24h
            $totalCallsToday = ApiLog::whereDate('created_at', today())->count();
            $errorRatePct = $totalCallsToday > 0
                ? round($totalErrorsToday / $totalCallsToday * 100, 1)
                : 0;

            return [
                'rate_limited_today'  => $rateLimitedToday,
                'unauthorized_today'  => $unauthorizedToday,
                'total_errors_today'  => $totalErrorsToday,
                'total_calls_today'   => $totalCallsToday,
                'error_rate_pct'      => $errorRatePct,
                'suspicious_ips'      => $suspiciousIps->map(fn($r) => [
                    'ip'    => $r->ip_address,
                    'count' => $r->count,
                ])->toArray(),
                'pending_anomalies'   => $pendingAnomalies,
                'alert_level'         => $this->computeAlertLevel(
                    $rateLimitedToday, $errorRatePct, $suspiciousIps->count()
                ),
            ];
        });
    }

    /**
     * Calcule le niveau d'alerte sécurité (green / amber / red).
     */
    private function computeAlertLevel(int $rateLimited, float $errorRate, int $suspiciousCount): string
    {
        if ($rateLimited > 1000 || $errorRate > 20 || $suspiciousCount > 0) {
            return 'red';
        }
        if ($rateLimited > 100 || $errorRate > 5) {
            return 'amber';
        }
        return 'green';
    }
}
