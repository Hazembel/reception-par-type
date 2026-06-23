<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller : VehicleController
 *
 * Affiche la fiche technique d'un véhicule.
 * Lit les attributs d'accès injectés par CheckVehicleAccess middleware
 * pour décider quelles données envoyer à la vue.
 *
 * ANTI-CLOAKING STRICT :
 * Les données avancées (masses, jantes) ne sont JAMAIS envoyées à la vue
 * si can_view_advanced === false. Elles ne sont pas simplement "masquées"
 * côté front : elles ne sont jamais chargées depuis la BDD pour cet utilisateur.
 */
class VehicleController extends BaseController
{
    public function show(Request $request, Vehicle $vehicle): View
    {
        // ── Lecture de la décision d'accès du middleware ──────────────────────
        $canViewAdvanced = (bool) $request->attributes->get('can_view_advanced', false);
        $accessReason    = $request->attributes->get('access_reason', 'unknown');
        $vehicleAccess   = $request->attributes->get('vehicle_access', 'partial');

        // ── Chargement conditionnel des données ───────────────────────────────
        // Les données de base sont toujours chargées (motorisation, émissions).
        // Les données avancées ne sont chargées QUE si l'accès est autorisé.
        if ($canViewAdvanced) {
            // Chargement complet : toutes les colonnes
            $vehicle->loadMissing([]);
            // Le modèle Vehicle a déjà tous les champs — rien à faire
        } else {
            // Données avancées nullifiées avant envoi à la vue
            // → aucune donnée sensible dans le HTML source
            $vehicle->poids_vide        = null;
            $vehicle->poids_total       = null;
            $vehicle->poids_remorquable = null;
            $vehicle->nb_trous          = null;
            $vehicle->entraxe           = null;
            $vehicle->alesage           = null;
            $vehicle->deport_et         = null;
            $vehicle->pneus_origine     = null;
        }

        // ── Informations contextuelles pour l'affichage des CTA ──────────────
        $accessContext = $this->buildAccessContext($request, $accessReason);

        // ── Meta SEO dynamiques (via BaseController::vehicleMetaData) ─────────
        $meta = $this->vehicleMetaData(
            $vehicle->marque,
            $vehicle->modele,
            $vehicle->variante ?? '',
            $vehicle->numero_tg
        );

        // ── Hreflang params (Module 1) ────────────────────────────────────────
        $meta['hreflang_params'] = ['vehicle' => $vehicle->slug];

        return view('pages.vehicle.show', array_merge(
            ['vehicle' => $vehicle, 'canViewAdvanced' => $canViewAdvanced, 'accessContext' => $accessContext],
            $meta
        ));
    }

    /**
     * Construit le contexte d'accès pour les CTA dans la vue.
     * Contient les informations nécessaires pour afficher le bon message
     * et le bon bouton d'upgrade selon la raison du blocage.
     */
    private function buildAccessContext(Request $request, string $reason): array
    {
        return match($reason) {
            'level_1_free' => [
                'type'           => 'upgrade_cta',
                'message_key'    => 'access.level1_message',
                'show_token_cta' => true,
                'show_plan_cta'  => true,
                'token_price'    => 2,
            ],
            'subscription_expired' => [
                'type'        => 'expired',
                'message_key' => 'access.subscription_expired',
                'show_renew'  => true,
            ],
            'insufficient_tokens' => [
                'type'          => 'no_tokens',
                'message_key'   => 'access.no_tokens',
                'tokens_balance'=> $request->attributes->get('tokens_balance', 0),
                'show_topup'    => true,
            ],
            'pro_quota_exceeded' => [
                'type'          => 'quota_exceeded',
                'message_key'   => 'access.quota_exceeded',
                'monthly_limit' => $request->attributes->get('monthly_limit'),
                'show_upgrade'  => true,
                'upgrade_level' => 5,
            ],
            'already_unlocked_this_month' => [
                'type'        => 'unlocked',
                'message_key' => 'access.already_unlocked',
                'token_price' => null,
            ],
            'token_spent' => [
                'type'          => 'token_used',
                'message_key'   => 'access.token_spent',
                'tokens_balance'=> $request->attributes->get('tokens_balance'),
            ],
            'pro_quota_ok' => [
                'type'       => 'quota_ok',
                'remaining'  => $request->attributes->get('monthly_remaining'),
            ],
            default => [
                'type' => 'full',
            ],
        };
    }
}
