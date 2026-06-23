<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Models\Vehicle;
use App\Services\TgNormalizerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller : SearchController
 *
 * Gère la page de recherche, les résultats paginés et la génération
 * dynamique des balises SEO meta par locale.
 *
 * Trois modes de recherche, détectés automatiquement :
 *   - 'vin'  : l'utilisateur saisit un VIN (jusqu'à 17 caractères du permis de
 *              circulation). Le VIN est OBLIGATOIREMENT tronqué à ses 9 premiers
 *              caractères, puis comparé en égalité exacte à la colonne vin_prefix.
 *   - 'tg'   : numéro de réception par type (chiffres + points/tirets).
 *   - 'text' : recherche libre sur marque / modèle / variante.
 */
class SearchController extends BaseController
{
    // ── Page de recherche (formulaire vide) ───────────────────────────────────

    public function index(): View
    {
        return $this->renderView(
            'pages.search',
            ['results' => null, 'query' => ''],
            $this->searchMetaTitle(),
            $this->searchMetaDescription()
        );
    }

    // ── Traitement de la recherche ─────────────────────────────────────────────

    public function results(SearchRequest $request): View
    {
        $query     = (string) $request->validated('query', '');
        $queryType = $this->detectQueryType($query);
        $perPage   = (int) $request->validated('per_page', 25);
        $locale    = app()->getLocale();

        // Construction de la requête Eloquent (uniquement Prepared Statements).
        $builder = Vehicle::active()
            ->select([
                'id', 'numero_tg', 'vin_prefix', 'eu_type_approval',
                'marque', 'modele', 'variante', 'vehicle_type',
                'slug', 'energie', 'puissance_kw', 'co2',
                'code_emissions', 'pollution_norm',
                'is_active', 'imported_at',
            ]);

        switch ($queryType) {
            case 'vin':
                // ── RECHERCHE PAR VIN ──────────────────────────────────────────
                // Le scope byVin() tronque SYSTÉMATIQUEMENT la saisie à 9 caractères
                // (via Vehicle::normalizeVinPrefix) puis applique une égalité exacte
                // sur vin_prefix (usage de l'index, pas de LIKE). Si le VIN fourni
                // est trop court, le scope ne renvoie aucun résultat (sécurité).
                $builder->byVin($query);
                break;

            case 'tg':
                // ── RECHERCHE PAR NUMÉRO TG ────────────────────────────────────
                $normalizedTg = TgNormalizerService::normalize($query);
                $builder->where(function ($q) use ($normalizedTg) {
                    $q->where('numero_tg', $normalizedTg)
                      ->orWhere('numero_tg', 'like', $normalizedTg . '%');
                });
                break;

            default:
                // ── RECHERCHE TEXTE (marque / modèle / variante) ───────────────
                // Les bindings PDO protègent contre les injections SQL.
                $terms = explode(' ', trim($query));
                foreach ($terms as $term) {
                    if (mb_strlen($term) >= 2) {
                        $builder->where(function ($q) use ($term) {
                            $q->where('marque',    'like', '%' . $term . '%')
                              ->orWhere('modele',   'like', '%' . $term . '%')
                              ->orWhere('variante', 'like', '%' . $term . '%');
                        });
                    }
                }
                break;
        }

        // ── Filtres avancés ────────────────────────────────────────────────────
        if ($request->filled('vehicle_type')) {
            $type = $request->validated('vehicle_type');
            if (in_array($type, [Vehicle::TYPE_CAR, Vehicle::TYPE_MOTORCYCLE], true)) {
                $builder->ofType($type);
            }
        }
        if ($request->filled('energie')) {
            $builder->where('energie', $request->validated('energie'));
        }
        if ($request->filled('puissance_min')) {
            $builder->where('puissance_kw', '>=', (int) $request->validated('puissance_min'));
        }
        if ($request->filled('puissance_max')) {
            $builder->where('puissance_kw', '<=', (int) $request->validated('puissance_max'));
        }
        if ($request->filled('co2_max')) {
            $builder->where('co2', '<=', (int) $request->validated('co2_max'));
        }

        $results = $builder
            ->orderBy('marque')
            ->orderBy('modele')
            ->paginate($perPage)
            ->withQueryString();

        // ── Meta SEO dynamiques basées sur les résultats ──────────────────────
        [$metaTitle, $metaDescription] = $this->buildSearchResultsMeta($query, $results->total(), $locale);

        return $this->renderView(
            'pages.search',
            [
                'results'   => $results,
                'query'     => $query,
                'queryType' => $queryType,
            ],
            $metaTitle,
            $metaDescription
        );
    }

    // ── Autocomplétion AJAX (rate-limited, JSON) ──────────────────────────────

    public function autocomplete(Request $request): JsonResponse
    {
        $term = trim($request->input('q', ''));

        if (mb_strlen($term) < 2 || mb_strlen($term) > 50) {
            return response()->json([]);
        }

        // Sanitisation stricte pour l'autocomplétion.
        if (!preg_match('/^[\p{L}\p{N}\s\.\-]{2,50}$/u', $term)) {
            return response()->json([]);
        }

        $suggestions = Vehicle::active()
            ->select(['marque', 'modele'])
            ->where(function ($q) use ($term) {
                $q->where('marque', 'like', $term . '%')
                  ->orWhere('modele', 'like', '%' . $term . '%');
            })
            ->distinct()
            ->limit(8)
            ->get()
            ->map(fn ($v) => [
                'label'  => $v->marque . ' ' . $v->modele,
                'marque' => $v->marque,
            ]);

        return response()->json($suggestions);
    }

    // ── Détection du type de requête ──────────────────────────────────────────

    /**
     * Détermine le mode de recherche : 'vin', 'tg' ou 'text'.
     *
     * Ordre de priorité :
     *   1. VIN  : 11 à 17 caractères alphanumériques, SANS séparateur de TG
     *             (point), contenant à la fois des lettres et des chiffres, et
     *             ne comportant aucune des lettres interdites du VIN (I, O, Q).
     *   2. TG   : majorité de chiffres et présence de séparateurs (./-/espace).
     *   3. text : tout le reste.
     */
    private function detectQueryType(string $query): string
    {
        $trimmed = trim($query);
        $compact = preg_replace('/\s+/', '', $trimmed);

        // 1. Détection VIN : alphanumérique pur (pas de point ni tiret), longueur
        //    11-17, lettres ET chiffres présents, sans I/O/Q (règle ISO 3779).
        if (
            preg_match('/^[A-HJ-NPR-Z0-9]{11,17}$/i', $compact)
            && preg_match('/[A-HJ-NPR-Z]/i', $compact)   // au moins une lettre
            && preg_match('/\d/', $compact)               // au moins un chiffre
        ) {
            return 'vin';
        }

        // 2. Détection TG : forte proportion de chiffres.
        $digits = preg_match_all('/\d/', $trimmed);
        $total  = mb_strlen($trimmed);
        if ($digits > 0 && $total > 0 && ($digits / $total) > 0.5) {
            return 'tg';
        }

        // 3. Sinon recherche texte.
        return 'text';
    }

    // ── Génération dynamique des méta SEO ─────────────────────────────────────

    /**
     * @return array{string, string} [metaTitle, metaDescription]
     */
    private function buildSearchResultsMeta(
        string $query,
        int    $totalResults,
        string $locale
    ): array {
        $query = htmlspecialchars(mb_substr($query, 0, 60), ENT_QUOTES, 'UTF-8');
        $count = number_format($totalResults, 0, '.', '\'');

        $titles = [
            'fr' => "Résultats pour \"{$query}\" — {$count} fiches ASTRA | reception-par-type.ch",
            'de' => "Ergebnisse für \"{$query}\" — {$count} ASTRA-Datenblätter | reception-par-type.ch",
            'it' => "Risultati per \"{$query}\" — {$count} schede ASTRA | reception-par-type.ch",
            'en' => "Results for \"{$query}\" — {$count} ASTRA data sheets | reception-par-type.ch",
        ];

        $descriptions = [
            'fr' => "Consultez les {$count} fiches techniques officielles de l'ASTRA correspondant à \"{$query}\". Données homologuées : motorisation, masses, jantes, émissions CO₂.",
            'de' => "{$count} offizielle ASTRA-Typendatenblätter für \"{$query}\". Homologierte Daten: Motorisierung, Gewichte, Felgen, CO₂-Emissionen.",
            'it' => "Consultate le {$count} schede tecniche ufficiali ASTRA per \"{$query}\". Dati omologati: motorizzazione, masse, cerchi, emissioni CO₂.",
            'en' => "{$count} official ASTRA type approval data sheets for \"{$query}\". Approved data: engine specs, weight, wheels, CO₂ emissions.",
        ];

        return [
            $titles[$locale]       ?? $titles['fr'],
            $descriptions[$locale] ?? $descriptions['fr'],
        ];
    }

    private function searchMetaTitle(): string
    {
        return match (app()->getLocale()) {
            'de'    => 'Fahrzeugdaten nach Typengenehmigung suchen | reception-par-type.ch',
            'it'    => 'Cerca dati veicolo per numero di omologazione | reception-par-type.ch',
            'en'    => 'Search Vehicle Data by Type Approval Number | reception-par-type.ch',
            default => 'Rechercher par numéro de réception par type | reception-par-type.ch',
        };
    }

    private function searchMetaDescription(): string
    {
        return match (app()->getLocale()) {
            'de'    => 'Suchen Sie Fahrzeugtechnische Daten anhand der Typengenehmigungsnummer (TG) aus den offiziellen ASTRA-TARGA-Dateien. Gewicht, Motor, Felgen, CO₂.',
            'it'    => 'Cercate dati tecnici veicolo tramite il numero di omologazione (TG) dai file TARGA ufficiali ASTRA. Peso, motore, cerchi, CO₂.',
            'en'    => 'Search vehicle technical data by type approval number (TG) from official ASTRA TARGA files. Weight, engine, wheels, CO₂ emissions.',
            default => 'Recherchez les données techniques de tout véhicule homologué en Suisse par son numéro TG (case 24 de la carte grise), via les fichiers officiels ASTRA-TARGA.',
        };
    }
}
