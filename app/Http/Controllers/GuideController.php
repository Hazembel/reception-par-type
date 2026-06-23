<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Controller : GuideController
 * Route : GET /{locale}/guide
 */
class GuideController extends BaseController
{
    public function index(): View
    {
        return $this->renderView(
            'pages.guide',
            [],
            __('help.guide.page_title') . ' | reception-par-type.ch',
            __('help.guide.page_description')
        );
    }
}
