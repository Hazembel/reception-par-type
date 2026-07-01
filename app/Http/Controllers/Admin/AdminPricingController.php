<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\PricingPlan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller : AdminPricingController
 *
 * Gestion des plans tarifaires depuis l'interface admin.
 * Toute modification invalide le cache automatiquement (via l'observer du modèle).
 */
class AdminPricingController extends BaseController
{
    /**
     * GET /admin/pricing
     * Liste les 8 plans avec leurs prix et quotas.
     */
    public function index(): View
    {
        // Défense en profondeur : niveau 8 requis (en plus du middleware admin).
        $this->authorize('managePricing', User::class);

        $plans = PricingPlan::ordered()->get();

        return $this->renderView(
            'admin.pricing.index',
            compact('plans'),
            'Configuration des tarifs | Admin',
            ''
        );
    }

    /**
     * POST /admin/pricing
     * Crée un nouveau plan tarifaire.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('managePricing', User::class);

        $validated = $request->validate([
            'level'               => ['required', 'integer', 'min:1', 'max:99', 'unique:pricing_plans,level'],
            'name_fr'             => ['required', 'string', 'max:255'],
            'name_de'             => ['nullable', 'string', 'max:255'],
            'name_it'             => ['nullable', 'string', 'max:255'],
            'name_en'             => ['nullable', 'string', 'max:255'],
            'price_monthly_chf'   => ['required', 'integer', 'min:0', 'max:999900'],
            'price_yearly_chf'    => ['nullable', 'integer', 'min:0', 'max:9999900'],
            'token_price_chf'     => ['required', 'integer', 'min:0', 'max:10000'],
            'web_monthly_limit'   => ['required', 'integer', 'min:-1', 'max:1000000'],
            'api_monthly_limit'   => ['required', 'integer', 'min:-1', 'max:10000000'],
            'api_rate_per_minute' => ['required', 'integer', 'min:0', 'max:1000'],
            'can_export_pdf'      => ['boolean'],
            'can_export_csv'      => ['boolean'],
            'can_use_api'         => ['boolean'],
            'can_compare'         => ['boolean'],
            'can_use_tax_calc'    => ['boolean'],
            'is_public'           => ['boolean'],
            'is_active'           => ['boolean'],
            'description_fr'      => ['nullable', 'string', 'max:500'],
            'stripe_price_id'     => ['nullable', 'string', 'max:50', 'regex:/^price_[a-zA-Z0-9_]+$/'],
            'display_order'       => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['display_order'] ??= $validated['level'];
        $plan = PricingPlan::create($validated);

        \Illuminate\Support\Facades\Log::channel('admin_audit')->info('Pricing plan created', [
            'admin_id'   => auth()->id(),
            'plan_level' => $plan->level,
        ]);

        return redirect()
            ->route('admin.pricing.index')
            ->with('success', "✅ Plan \"{$plan->name_fr}\" (niveau {$plan->level}) créé.");
    }

    /**
     * DELETE /admin/pricing/{plan}
     * Supprime un plan tarifaire.
     */
    public function destroy(PricingPlan $plan): RedirectResponse
    {
        $this->authorize('managePricing', User::class);

        $name  = $plan->name_fr;
        $level = $plan->level;
        $plan->delete();

        \Illuminate\Support\Facades\Log::channel('admin_audit')->info('Pricing plan deleted', [
            'admin_id'   => auth()->id(),
            'plan_level' => $level,
        ]);

        return redirect()
            ->route('admin.pricing.index')
            ->with('success', "🗑️ Plan \"{$name}\" (niveau {$level}) supprimé.");
    }

    /**
     * POST /admin/pricing/{plan}
     * Met à jour un plan tarifaire (formulaire inline).
     */
    public function update(Request $request, PricingPlan $plan): RedirectResponse
    {
        // Défense en profondeur : seul un admin niveau 8 peut modifier un tarif.
        $this->authorize('managePricing', User::class);

        $validated = $request->validate([
            'name_fr'             => ['required', 'string', 'max:255'],
            'name_de'             => ['nullable', 'string', 'max:255'],
            'name_it'             => ['nullable', 'string', 'max:255'],
            'name_en'             => ['nullable', 'string', 'max:255'],
            'price_monthly_chf'   => ['required', 'integer', 'min:0', 'max:999900'],
            'price_yearly_chf'    => ['nullable', 'integer', 'min:0', 'max:9999900'],
            'token_price_chf'     => ['required', 'integer', 'min:0', 'max:10000'],
            'web_monthly_limit'   => ['required', 'integer', 'min:-1', 'max:1000000'],
            'api_monthly_limit'   => ['required', 'integer', 'min:-1', 'max:10000000'],
            'api_rate_per_minute' => ['required', 'integer', 'min:0', 'max:1000'],
            'can_export_pdf'      => ['boolean'],
            'can_export_csv'      => ['boolean'],
            'can_use_api'         => ['boolean'],
            'can_compare'         => ['boolean'],
            'can_use_tax_calc'    => ['boolean'],
            'is_public'           => ['boolean'],
            'is_active'           => ['boolean'],
            'description_fr'      => ['nullable', 'string', 'max:500'],
            'stripe_price_id'     => ['nullable', 'string', 'max:50', 'regex:/^price_[a-zA-Z0-9_]+$/'],
            'display_order'       => ['nullable', 'integer', 'min:0'],
        ]);

        // Protection : le niveau 8 ne peut pas être désactivé ni rendu public
        if ($plan->level === 8) {
            $validated['is_active'] = true;
            $validated['is_public'] = false;
        }

        // Protection : le niveau 1 (gratuit) reste toujours à 0 CHF
        if ($plan->level === 1) {
            $validated['price_monthly_chf'] = 0;
            $validated['price_yearly_chf']  = 0;
        }

        $plan->update($validated);
        // Cache invalidé automatiquement par l'observer (PricingPlan::saved)

        \Illuminate\Support\Facades\Log::channel('admin_audit')->info('Pricing plan updated', [
            'admin_id' => auth()->id(),
            'plan_level' => $plan->level,
            'changes' => $plan->getChanges(),
        ]);

        return redirect()
            ->route('admin.pricing.index')
            ->with('success', "✅ Plan \"{$plan->name_fr}\" mis à jour. Cache invalidé.");
    }
}
