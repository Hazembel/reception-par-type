<?php

namespace App\Http\Controllers;

use App\Models\PricingPlan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Controller : PricingController
 * Route : GET /{locale}/pricing
 */
class PricingController extends BaseController
{
    public function index(): View
    {
        try {
            $plans = Cache::remember('public_pricing_plans', 3600, fn () => PricingPlan::public()->ordered()->get());
        } catch (\Throwable $e) {
            // DB unreachable — serve stale cache if available, otherwise empty collection.
            Log::warning('PricingController: DB unavailable, falling back to stale cache', [
                'error' => $e->getMessage(),
            ]);
            $plans = Cache::get('public_pricing_plans', new Collection());
        }

        return $this->renderView(
            'pages.pricing',
            ['plans' => $plans],
            __('app.nav.pricing') . ' | reception-par-type.ch',
            'Découvrez nos plans d\'abonnement pour accéder aux données techniques ASTRA officielles en Suisse. Tarifs adaptés aux particuliers et aux professionnels.'
        );
    }
}
