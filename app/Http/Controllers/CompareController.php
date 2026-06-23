<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Controller : CompareController
 * Route : GET /{locale}/compare
 */
class CompareController extends BaseController
{
    public function index(): View
    {
        return $this->renderView(
            'pages.compare',
            [],
            __('app.nav.compare') . ' | reception-par-type.ch',
            'Comparez les caractéristiques techniques, dimensions et impôts de différents véhicules homologués en Suisse.'
        );
    }
}
