<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Classe de base pour tous les contrôleurs de l'application.
 * Fournit les traits d'autorisation et de validation de Laravel.
 */
class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;
}
