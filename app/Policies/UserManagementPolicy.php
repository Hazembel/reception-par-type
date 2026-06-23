<?php

namespace App\Policies;

use App\Models\User;

/**
 * Policy : UserManagementPolicy
 *
 * Protège les actions d'administration sur les utilisateurs.
 * Seul un admin (subscription_level = 8) peut modifier les accès d'un autre utilisateur.
 * Un admin ne peut pas s'auto-modifier (protection contre l'escalade de privilèges).
 */
class UserManagementPolicy
{
    /**
     * L'utilisateur peut-il voir la liste des utilisateurs ?
     */
    public function viewAny(User $admin): bool
    {
        return $admin->subscription_level === 8;
    }

    /**
     * L'utilisateur peut-il modifier les droits d'un utilisateur cible ?
     */
    public function grantAccess(User $admin, User $target): bool
    {
        // Seul un admin peut modifier d'autres utilisateurs
        if ($admin->subscription_level !== 8) {
            return false;
        }

        // Un admin ne peut pas modifier son propre compte depuis cette interface
        // (protection contre l'escalade accidentelle ou forcée)
        if ($admin->id === $target->id) {
            return false;
        }

        // Un admin ne peut pas rétrograder un autre admin
        if ($target->subscription_level === 8) {
            return false;
        }

        return true;
    }

    /**
     * L'utilisateur peut-il voir les statistiques du dashboard ?
     */
    public function viewDashboard(User $admin): bool
    {
        return $admin->subscription_level === 8;
    }

    /**
     * L'utilisateur peut-il gérer les forfaits/tarifs VIP (niveaux 1 à 8) ?
     *
     * Défense en profondeur : même si le middleware `admin` protège déjà la
     * route, cette policy garantit qu'aucun utilisateur lambda ne peut modifier
     * les prix des forfaits, quelle que soit la façon dont la route est atteinte.
     */
    public function managePricing(User $admin): bool
    {
        return $admin->subscription_level === 8;
    }
}
