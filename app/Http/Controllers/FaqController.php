<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Controller : FaqController
 * Route : GET /{locale}/faq
 */
class FaqController extends BaseController
{
    public function index(): View
    {
        $locale = app()->getLocale();

        // Les données FAQ viennent des fichiers de langue (statiques, pas de BDD)
        // → pas de requête SQL → cache HTTP fort possible
        return $this->renderView(
            'pages.faq',
            [],
            __('help.faq.page_title') . ' | reception-par-type.ch',
            __('help.faq.page_description')
        );
    }
}
