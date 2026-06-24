<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware : AdminAccess
 *
 * Vérifie que l'utilisateur connecté a le niveau d'abonnement 8 (Entreprise/Admin).
 * Utilisé pour protéger toutes les routes /admin/*.
 *
 * En combinaison avec 'auth' et 'verified' dans le groupe de routes admin.
 *
 * Enregistrement dans bootstrap/app.php :
 *   $middleware->alias(['admin' => AdminAccess::class]);
 */
class AdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Double vérification (le middleware 'auth' devrait déjà garantir l'authentification)
        if (!$user) {
            return redirect()->route('login', ['locale' => app()->getLocale()])
                ->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        if (!$user->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs de la plateforme.');
        }

        // Fixe la locale en FR pour l'interface admin (pas de multilinguisme admin)
        app()->setLocale('fr');

        // En-tête de sécurité supplémentaire pour les pages admin
        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
