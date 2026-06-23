<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelBaseController;
use Illuminate\View\View;

/**
 * Controller de Base : BaseController
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Toutes les variables meta SEO par défaut sont préparées ici.
 * Chaque controller enfant hérite de ces méthodes et peut les surcharger
 * page par page avec des données spécifiques au véhicule ou à la recherche.
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Pattern utilisé : les variables sont partagées avec toutes les vues
 * via $this->shareMetaToViews() ou passées individuellement par page.
 */
abstract class BaseController extends LaravelBaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    // ── Meta par défaut par locale ────────────────────────────────────────────

    /**
     * Titres SEO par défaut, par locale.
     * Format recommandé par Google : "[Sujet] - [Nom du site]" (< 60 caractères)
     *
     * @var array<string, string>
     */
    protected array $defaultMetaTitles = [
        'fr' => 'Fiches Techniques Automobiles ASTRA | reception-par-type.ch',
        'de' => 'Fahrzeug Typengenehmigung ASTRA | reception-par-type.ch',
        'it' => 'Schede Tecniche Auto ASTRA | reception-par-type.ch',
        'en' => 'ASTRA Vehicle Type Approval | reception-par-type.ch',
    ];

    /**
     * Descriptions SEO par défaut, par locale.
     * Recommandation Google : entre 150 et 160 caractères.
     *
     * @var array<string, string>
     */
    protected array $defaultMetaDescriptions = [
        'fr' => 'Consultez les données techniques officielles de l\'ASTRA par numéro de réception par type (TG). Poids, motorisation, jantes, émissions CO2 et bien plus.',
        'de' => 'Offizielle ASTRA Fahrzeugdaten nach Typengenehmigungsnummer (TG). Gewicht, Motor, Felgen, CO2-Emissionen und mehr auf einen Blick.',
        'it' => 'Consultate i dati tecnici ufficiali ASTRA per numero di omologazione (TG). Peso, motore, cerchi, emissioni CO2 e molto altro.',
        'en' => 'Access official ASTRA vehicle technical data by type approval number (TG). Weight, engine specs, wheels, CO2 emissions and more.',
    ];

    // ── Méthodes d'initialisation ─────────────────────────────────────────────

    /**
     * Retourne le tableau de variables meta pour la locale courante.
     *
     * Appelé dans chaque méthode de controller pour préparer les vues.
     * Les valeurs peuvent être surchargées pour des pages spécifiques.
     *
     * @param  string|null $title        Surcharge du meta_title (null = défaut)
     * @param  string|null $description  Surcharge du meta_description (null = défaut)
     * @param  array       $extra        Variables supplémentaires à passer à la vue
     * @return array<string, mixed>
     */
    protected function metaData(
        ?string $title       = null,
        ?string $description = null,
        array   $extra       = []
    ): array {
        $locale = app()->getLocale();

        // Validation défensive : la locale doit être dans notre liste blanche
        $supportedLocales = ['fr', 'de', 'it', 'en'];
        if (!in_array($locale, $supportedLocales, true)) {
            $locale = 'fr';
        }

        $metaTitle       = $title       ?? $this->defaultMetaTitles[$locale]       ?? $this->defaultMetaTitles['fr'];
        $metaDescription = $description ?? $this->defaultMetaDescriptions[$locale] ?? $this->defaultMetaDescriptions['fr'];

        return array_merge([
            'meta_title'       => $metaTitle,
            'meta_description' => $metaDescription,
            'meta_locale'      => $locale,
            'meta_site_name'   => config('app.name', 'reception-par-type.ch'),
            'meta_url'         => request()->url(),
            'meta_robots'      => 'index, follow', // Valeur par défaut ; surcharger pour noindex
        ], $extra);
    }

    /**
     * Partage les variables meta avec toutes les vues (alternative à metaData()).
     * Utilise View::share() pour injecter globalement dans le layout Blade.
     *
     * À appeler dans le constructeur du controller enfant si nécessaire.
     */
    protected function shareMetaToViews(
        ?string $title       = null,
        ?string $description = null
    ): void {
        $meta = $this->metaData($title, $description);

        foreach ($meta as $key => $value) {
            view()->share($key, $value);
        }
    }

    // ── Helpers de rendu ─────────────────────────────────────────────────────

    /**
     * Génère une réponse de vue avec les meta SEO automatiquement fusionnées.
     *
     * Raccourci pratique pour les controllers enfants :
     *   return $this->renderView('vehicles.show', ['vehicle' => $vehicle], 'Titre Custom');
     *
     * @param  string               $view        Nom de la vue Blade
     * @param  array                $data        Variables de la vue (hors meta)
     * @param  string|null          $title       Meta title spécifique à la page
     * @param  string|null          $description Meta description spécifique
     * @return \Illuminate\View\View
     */
    protected function renderView(
        string  $view,
        array   $data        = [],
        ?string $title       = null,
        ?string $description = null
    ): View {
        $meta = $this->metaData($title, $description);

        return view($view, array_merge($data, $meta));
    }

    /**
     * Prépare les meta pour une fiche véhicule (page type la plus fréquente).
     *
     * @param  string $marque
     * @param  string $modele
     * @param  string $variante
     * @param  string $numerotg
     * @return array<string, string>
     */
    protected function vehicleMetaData(
        string $marque,
        string $modele,
        string $variante,
        string $numerotg
    ): array {
        $locale = app()->getLocale();

        $vehicleName = trim("{$marque} {$modele} {$variante}");

        $titles = [
            'fr' => "Fiche Technique {$vehicleName} | TG {$numerotg} | reception-par-type.ch",
            'de' => "Technische Daten {$vehicleName} | TG {$numerotg} | reception-par-type.ch",
            'it' => "Scheda Tecnica {$vehicleName} | TG {$numerotg} | reception-par-type.ch",
            'en' => "Technical Data {$vehicleName} | TG {$numerotg} | reception-par-type.ch",
        ];

        $descriptions = [
            'fr' => "Fiche technique officielle ASTRA pour {$vehicleName} (N° TG : {$numerotg}). Poids, motorisation, jantes, émissions et données homologuées.",
            'de' => "Offizielle ASTRA Daten für {$vehicleName} (TG-Nr.: {$numerotg}). Gewicht, Motor, Felgen, Emissionen und homologierte Daten.",
            'it' => "Dati tecnici ufficiali ASTRA per {$vehicleName} (N° TG: {$numerotg}). Peso, motore, cerchi, emissioni e dati omologati.",
            'en' => "Official ASTRA technical data for {$vehicleName} (TG No.: {$numerotg}). Weight, engine, wheels, emissions and approved specifications.",
        ];

        return $this->metaData(
            $titles[$locale]       ?? $titles['fr'],
            $descriptions[$locale] ?? $descriptions['fr']
        );
    }
}
