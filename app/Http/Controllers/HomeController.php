<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Controller : HomeController
 *
 * Affiche la page d'accueil localisée (hero + recherche + valeur ajoutée).
 * Les meta SEO sont générées par locale via BaseController.
 */
class HomeController extends BaseController
{
    public function index(): View
    {
        return $this->renderView(
            'pages.home',
            [],
            __('app.seo.home_title'),
            __('app.seo.home_description'),
        );
    }
}
