<?php

namespace App\Http\Controllers;

use App\Models\PricingPlan;
use Illuminate\View\View;

/**
 * Controller : PricingController
 * Route : GET /{locale}/pricing
 */
class PricingController extends BaseController
{
    public function index(): View
    {
        $plans = PricingPlan::public()->ordered()->get();

        return $this->renderView(
            'pages.pricing',
            ['plans' => $plans],
            __('app.nav.pricing') . ' | reception-par-type.ch',
            'Découvrez nos plans d\'abonnement pour accéder aux données techniques ASTRA officielles en Suisse. Tarifs adaptés aux particuliers et aux professionnels.'
        );
    }
}
