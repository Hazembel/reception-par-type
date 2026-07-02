<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\PricingPlan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Controller : AdminUserController
 *
 * Interface d'administration des utilisateurs :
 *  - Recherche
 *  - Modification du niveau d'abonnement (cadeau commercial)
 *  - Ajustement du solde de jetons
 *  - Prolongation de l'abonnement
 */
class AdminUserController extends BaseController
{
    // ── Liste & Recherche ─────────────────────────────────────────────────────

    /**
     * GET /admin/users
     * Affiche la liste des utilisateurs avec moteur de recherche.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $search = trim($request->input('q', ''));
        $level  = $request->input('level');
        $sort   = $request->input('sort', 'created_at');
        $dir    = $request->input('dir', 'desc');

        // Colonnes de tri autorisées (liste blanche contre injection via URL)
        $allowedSorts = ['name', 'email', 'subscription_level', 'web_tokens_balance', 'created_at'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $query = User::query()
            ->select(['id', 'name', 'email', 'email_verified_at', 'subscription_level',
                      'web_tokens_balance', 'web_monthly_counter', 'subscribed_until', 'created_at', 'deleted_at'])
            ->where('subscription_level', '!=', User::ADMIN_LEVEL) // Exclure les admins
            ->withTrashed(); // Inclut les comptes supprimés pour l'audit

        // Recherche par nom ou email
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name',  'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filtre par niveau (niveau 8 exclu — ce sont les admins)
        if ($level !== null && $level !== '' && (int) $level !== User::ADMIN_LEVEL) {
            $query->where('subscription_level', (int) $level);
        }

        $users = $query->orderBy($sort, $dir)->paginate(25)->withQueryString();

        // Statistiques de la page pour l'en-tête (clients uniquement, hors admins)
        $stats = [
            'total'    => User::where('subscription_level', '!=', User::ADMIN_LEVEL)->count(),
            'verified' => User::where('subscription_level', '!=', User::ADMIN_LEVEL)->whereNotNull('email_verified_at')->count(),
            'active'   => User::where('subscription_level', '!=', User::ADMIN_LEVEL)->where('subscribed_until', '>', now())->count(),
        ];

        $plans = PricingPlan::allCached()->keyBy('level');

        return $this->renderView(
            'admin.users.index',
            compact('users', 'search', 'level', 'sort', 'dir', 'stats', 'plans'),
            'Gestion des utilisateurs | Admin',
            ''
        );
    }

    /**
     * GET /admin/users/admins
     * Liste des comptes administrateurs (niveau 8).
     */
    public function admins(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $search = trim($request->input('q', ''));

        $query = User::query()
            ->select(['id', 'name', 'email', 'email_verified_at', 'subscription_level',
                      'web_tokens_balance', 'web_monthly_counter', 'subscribed_until', 'created_at', 'deleted_at'])
            ->where('subscription_level', User::ADMIN_LEVEL)
            ->withTrashed();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name',  'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();
        $sort  = 'created_at';
        $dir   = 'desc';
        $level = (string) User::ADMIN_LEVEL;
        $stats = [
            'total'    => User::where('subscription_level', User::ADMIN_LEVEL)->count(),
            'verified' => User::where('subscription_level', User::ADMIN_LEVEL)->whereNotNull('email_verified_at')->count(),
            'active'   => 0,
        ];
        $plans = PricingPlan::allCached()->keyBy('level');

        return $this->renderView(
            'admin.users.index',
            compact('users', 'search', 'level', 'sort', 'dir', 'stats', 'plans'),
            'Administrateurs | Admin',
            ''
        );
    }

    /**
     * GET /admin/users/{user}
     * Fiche détaillée d'un utilisateur.
     */
    public function show(User $user): View
    {
        $this->authorize('viewAny', User::class);

        $plans = PricingPlan::allCached();

        // Historique des imports déverrouillés ce mois-ci
        $unlockedCount = \App\Models\UserUnlockedVehicle::where('user_id', $user->id)
            ->thisMonth()
            ->count();

        return $this->renderView(
            'admin.users.show',
            compact('user', 'plans', 'unlockedCount'),
            "Utilisateur : {$user->name} | Admin",
            ''
        );
    }

    // ── Créer un utilisateur ─────────────────────────────────────────────────

    /**
     * POST /admin/users
     * Crée un compte utilisateur directement depuis l'admin (email pré-vérifié).
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'email'              => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'password'           => ['required', Password::min(8)],
            'subscription_level' => ['required', 'integer', 'min:1', 'max:7'],
        ]);

        $user = User::create([
            'name'               => $validated['name'],
            'email'              => $validated['email'],
            'password'           => Hash::make($validated['password']),
            'subscription_level' => (int) $validated['subscription_level'],
            'email_verified_at'  => now(), // Admin-created accounts are pre-verified
        ]);

        $this->logAdminAction(auth()->id(), $user->id, 'create_user', [
            'name'  => $user->name,
            'email' => $user->email,
            'level' => $user->subscription_level,
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', "✅ Utilisateur {$user->name} créé avec succès.");
    }

    // ── Supprimer / Restaurer un utilisateur ─────────────────────────────────

    /**
     * DELETE /admin/users/{user}
     * Suppression douce (soft delete) — conforme RGPD.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('grantAccess', $user);

        $name = $user->name;
        $user->delete();

        $this->logAdminAction(auth()->id(), $user->id, 'delete_user', [
            'name'  => $name,
            'email' => $user->email,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "✅ Compte de {$name} supprimé (soft delete).");
    }

    /**
     * POST /admin/users/{user}/restore
     * Restaure un compte soft-deleted.
     */
    public function restore(string $id): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        $this->logAdminAction(auth()->id(), $user->id, 'restore_user', [
            'name'  => $user->name,
            'email' => $user->email,
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', "✅ Compte de {$user->name} restauré.");
    }

    // ── Modification des droits ───────────────────────────────────────────────

    /**
     * POST /admin/users/{user}/grant
     * Modifie le niveau, les jetons ou l'abonnement d'un utilisateur.
     * Action "cadeau commercial" — loguée dans le journal d'audit.
     */
    public function grant(Request $request, User $user): RedirectResponse
    {
        $this->authorize('grantAccess', $user);

        $validated = $request->validate([
            'action'              => ['required', 'in:set_level,add_tokens,extend_subscription,reset_counter'],
            'subscription_level'  => ['required_if:action,set_level', 'integer', 'min:1', 'max:7'],
            'tokens_to_add'       => ['required_if:action,add_tokens', 'integer', 'min:1', 'max:1000'],
            'extend_days'         => ['required_if:action,extend_subscription', 'integer', 'min:1', 'max:730'],
            'note'                => ['nullable', 'string', 'max:255'],
        ]);

        $adminId = auth()->id();
        $action  = $validated['action'];

        DB::transaction(function () use ($validated, $user, $action, $adminId) {
            switch ($action) {

                // ── Changer le niveau d'abonnement ────────────────────────
                case 'set_level':
                    $newLevel = (int) $validated['subscription_level'];
                    $oldLevel = $user->subscription_level;

                    $user->update([
                        'subscription_level' => $newLevel,
                        // Si on upgrade, on fixe une date de fin d'un mois
                        'subscribed_until'   => $newLevel > 1
                            ? now()->addMonth()
                            : null,
                    ]);

                    $this->logAdminAction($adminId, $user->id, 'set_level', [
                        'old_level' => $oldLevel,
                        'new_level' => $newLevel,
                        'note'      => $validated['note'] ?? null,
                    ]);
                    break;

                // ── Ajouter des jetons ─────────────────────────────────────
                case 'add_tokens':
                    $amount = (int) $validated['tokens_to_add'];
                    $user->increment('web_tokens_balance', $amount);

                    $this->logAdminAction($adminId, $user->id, 'add_tokens', [
                        'amount' => $amount,
                        'new_balance' => $user->fresh()->web_tokens_balance,
                        'note'   => $validated['note'] ?? null,
                    ]);
                    break;

                // ── Prolonger l'abonnement ─────────────────────────────────
                case 'extend_subscription':
                    $days    = (int) $validated['extend_days'];
                    $current = $user->subscribed_until && $user->subscribed_until->isFuture()
                        ? $user->subscribed_until
                        : now();

                    $newDate = $current->addDays($days);
                    $user->update(['subscribed_until' => $newDate]);

                    $this->logAdminAction($adminId, $user->id, 'extend_subscription', [
                        'days_added'  => $days,
                        'new_end'     => $newDate->toDateString(),
                        'note'        => $validated['note'] ?? null,
                    ]);
                    break;

                // ── Reset du compteur mensuel ──────────────────────────────
                case 'reset_counter':
                    $old = $user->web_monthly_counter;
                    $user->update(['web_monthly_counter' => 0]);

                    $this->logAdminAction($adminId, $user->id, 'reset_counter', [
                        'old_counter' => $old,
                        'note'        => $validated['note'] ?? null,
                    ]);
                    break;
            }
        });

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', '✅ Modification appliquée avec succès.');
    }

    // ── Journal d'audit ───────────────────────────────────────────────────────

    /**
     * Enregistre une action admin dans le journal d'audit.
     * Stocké dans la table admin_audit_log (à créer si besoin, ici en JSON simple).
     */
    private function logAdminAction(
        string $adminId,
        string $targetUserId,
        string $action,
        array  $context
    ): void {
        \Illuminate\Support\Facades\Log::channel('admin_audit')->info('Admin action', [
            'admin_id'   => $adminId,
            'target_id'  => $targetUserId,
            'action'     => $action,
            'context'    => $context,
            'ip'         => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp'  => now()->toIso8601String(),
        ]);
    }
}
