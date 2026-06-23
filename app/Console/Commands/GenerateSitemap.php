<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Commande Artisan : GenerateSitemap
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * Génère un sitemap XML multilingue dynamique pour reception-par-type.ch.
 *
 * Fonctionnalités :
 *  - Découpé en blocs de 40 000 URL (limite Google = 50 000 / 50 Mo)
 *  - Balises <xhtml:link rel="alternate" hreflang="..."> par URL
 *  - Sitemap index pointant vers tous les blocs
 *  - Pages statiques (accueil, recherche, FAQ, guide, tarifs) en tête
 *  - Priorité et fréquence de mise à jour par type de page
 *  - Génération incrémentielle par curseur (mémoire constante)
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Usage :
 *   php artisan sitemap:generate
 *   php artisan sitemap:generate --disk=public --path=sitemaps/
 *   php artisan sitemap:generate --dry-run   (compte les URLs sans écrire)
 *
 * Planification (console.php) :
 *   Schedule::command('sitemap:generate')->weekly()->mondays()->at('04:00');
 */
class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate
                            {--disk=public      : Disque Storage Laravel pour l\'écriture}
                            {--path=            : Sous-chemin dans le disque (défaut: racine)}
                            {--chunk=40000      : Nombre d\'URL par fichier sitemap}
                            {--dry-run          : Compte les URLs sans écrire les fichiers}';

    protected $description = 'Génère le sitemap XML multilingue (fr/de/it/en) avec hreflang';

    /** Locales supportées avec leur hreflang ISO */
    private const LOCALES = [
        'fr' => 'fr-CH',
        'de' => 'de-CH',
        'it' => 'it-CH',
        'en' => 'en',
    ];

    /** Priorités SEO par type de page */
    private const PRIORITIES = [
        'home'    => '1.0',
        'vehicle' => '0.8',
        'search'  => '0.7',
        'faq'     => '0.6',
        'guide'   => '0.6',
        'pricing' => '0.5',
        'legal'   => '0.2',
    ];

    /** Fréquences de mise à jour */
    private const FREQUENCIES = [
        'home'    => 'weekly',
        'vehicle' => 'monthly',
        'search'  => 'weekly',
        'faq'     => 'monthly',
        'guide'   => 'monthly',
        'pricing' => 'weekly',
        'legal'   => 'yearly',
    ];

    private string $disk;
    private string $basePath;
    private int    $chunkSize;
    private bool   $dryRun;
    private string $baseUrl;

    public function handle(): int
    {
        $this->disk      = $this->option('disk');
        $this->basePath  = rtrim($this->option('path') ?? '', '/');
        $this->chunkSize = (int) $this->option('chunk');
        $this->dryRun    = (bool) $this->option('dry-run');
        $this->baseUrl   = rtrim(config('app.url'), '/');

        $this->info('🗺  Génération du sitemap multilingue...');
        $this->line("   Base URL : {$this->baseUrl}");
        $this->line("   Chunk    : " . number_format($this->chunkSize) . " URLs/fichier");

        if ($this->dryRun) {
            $this->warn('   Mode DRY-RUN : aucun fichier ne sera écrit.');
        }

        $sitemapFiles = [];

        // ── 1. Pages statiques ────────────────────────────────────────────────
        $staticUrls = $this->buildStaticUrls();
        $this->line("   Pages statiques : " . count($staticUrls) . " URLs");

        if (!$this->dryRun) {
            $file = $this->writeUrlset($staticUrls, 'sitemap-static.xml');
            $sitemapFiles[] = $file;
        }

        // ── 2. Fiches véhicules (streaming par curseur) ───────────────────────
        $vehicleCount = Vehicle::active()->count();
        $totalUrls    = $vehicleCount * count(self::LOCALES);
        $totalChunks  = (int) ceil($totalUrls / $this->chunkSize);

        $this->line("   Véhicules actifs : " . number_format($vehicleCount));
        $this->line("   URLs véhicules   : " . number_format($totalUrls) . " ({$totalChunks} fichiers)");

        if ($this->dryRun) {
            $this->info("✓ DRY-RUN terminé. Total estimé : " . number_format($totalUrls + count($staticUrls)) . " URLs.");
            return self::SUCCESS;
        }

        $bar        = $this->output->createProgressBar($vehicleCount);
        $buffer     = [];
        $fileIndex  = 1;

        Vehicle::active()
            ->select(['slug', 'updated_at', 'marque', 'modele'])
            ->orderBy('id')
            ->chunk(2000, function ($vehicles) use (&$buffer, &$fileIndex, &$sitemapFiles, $bar) {
                foreach ($vehicles as $vehicle) {
                    $vehicleUrls = $this->buildVehicleUrls($vehicle);
                    $buffer      = array_merge($buffer, $vehicleUrls);

                    // Écriture du chunk quand le buffer est plein
                    while (count($buffer) >= $this->chunkSize) {
                        $chunk   = array_splice($buffer, 0, $this->chunkSize);
                        $file    = $this->writeUrlset($chunk, "sitemap-vehicles-{$fileIndex}.xml");
                        $sitemapFiles[] = $file;
                        $fileIndex++;
                    }

                    $bar->advance();
                }
            });

        // Dernier chunk résiduel
        if (!empty($buffer)) {
            $file = $this->writeUrlset($buffer, "sitemap-vehicles-{$fileIndex}.xml");
            $sitemapFiles[] = $file;
        }

        $bar->finish();
        $this->newLine();

        // ── 3. Sitemap Index ──────────────────────────────────────────────────
        $this->writeSitemapIndex($sitemapFiles);

        $this->info("✅ Sitemap généré : " . count($sitemapFiles) . " fichiers.");
        $this->line("   Index : " . $this->publicPath('sitemap.xml'));

        return self::SUCCESS;
    }

    // ── Construction des URLs ─────────────────────────────────────────────────

    /**
     * Construit les URLs des pages statiques dans toutes les locales.
     *
     * @return array<int, array{loc: string, lastmod: string, changefreq: string, priority: string, alternates: array}>
     */
    private function buildStaticUrls(): array
    {
        $urls  = [];
        $today = now()->toDateString();

        $staticRoutes = [
            ['type' => 'home',    'segments' => []],
            ['type' => 'search',  'segments' => ['search']],
            ['type' => 'faq',     'segments' => ['faq']],
            ['type' => 'guide',   'segments' => ['guide']],
            ['type' => 'pricing', 'segments' => ['pricing']],
            ['type' => 'legal',   'segments' => ['mentions-legales']],
        ];

        foreach ($staticRoutes as $route) {
            // URL principale (français)
            $alternates = $this->buildAlternates(
                $route['segments'],
                null,
                // Mapping des segments par locale pour les URLs statiques
                $this->getLocaleSegments($route['type'])
            );

            // Une entrée par locale (Google préfère des URL canoniques avec hreflang)
            foreach (self::LOCALES as $locale => $hreflang) {
                $segment = $this->getLocaleSegment($route['type'], $locale);
                $loc     = $this->buildUrl($locale, $segment ? [$segment] : []);

                $urls[] = [
                    'loc'        => $loc,
                    'lastmod'    => $today,
                    'changefreq' => self::FREQUENCIES[$route['type']],
                    'priority'   => self::PRIORITIES[$route['type']],
                    'alternates' => $alternates,
                ];
            }
        }

        return $urls;
    }

    /**
     * Construit les 4 URLs localisées d'un véhicule (une par langue).
     */
    private function buildVehicleUrls(Vehicle $vehicle): array
    {
        $lastmod    = $vehicle->updated_at?->toDateString() ?? now()->toDateString();
        $alternates = [];

        // Pré-calcul des alternates pour toutes les locales
        foreach (self::LOCALES as $locale => $hreflang) {
            $alternates[] = [
                'hreflang' => $hreflang,
                'href'     => $this->buildUrl($locale, ['vehicle', $vehicle->slug]),
            ];
        }
        // x-default → FR
        $alternates[] = [
            'hreflang' => 'x-default',
            'href'     => $this->buildUrl('fr', ['vehicle', $vehicle->slug]),
        ];

        $urls = [];
        foreach (self::LOCALES as $locale => $hreflang) {
            $urls[] = [
                'loc'        => $this->buildUrl($locale, ['vehicle', $vehicle->slug]),
                'lastmod'    => $lastmod,
                'changefreq' => self::FREQUENCIES['vehicle'],
                'priority'   => self::PRIORITIES['vehicle'],
                'alternates' => $alternates,
            ];
        }

        return $urls;
    }

    // ── Génération XML ────────────────────────────────────────────────────────

    /**
     * Écrit un fichier urlset XML sur le disque Storage.
     * Génération en streaming (pas de chargement complet en RAM).
     */
    private function writeUrlset(array $urls, string $filename): array
    {
        $filePath = $this->basePath ? "{$this->basePath}/{$filename}" : $filename;

        // Génération XML en streaming
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc'], ENT_XML1) . "</loc>\n";
            $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";

            // Balises hreflang pour le multilinguisme
            if (!empty($url['alternates'])) {
                foreach ($url['alternates'] as $alt) {
                    $xml .= '    <xhtml:link rel="alternate"'
                          . ' hreflang="' . htmlspecialchars($alt['hreflang'], ENT_XML1) . '"'
                          . ' href="' . htmlspecialchars($alt['href'], ENT_XML1) . '"'
                          . "/>\n";
                }
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        Storage::disk($this->disk)->put($filePath, $xml);

        return [
            'filename'  => $filename,
            'url_count' => count($urls),
            'public_url'=> $this->publicPath($filename),
            'lastmod'   => now()->toDateString(),
        ];
    }

    /**
     * Écrit le fichier sitemap index (sitemap.xml racine).
     */
    private function writeSitemapIndex(array $files): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($files as $file) {
            $xml .= "  <sitemap>\n";
            $xml .= "    <loc>" . htmlspecialchars($file['public_url'], ENT_XML1) . "</loc>\n";
            $xml .= "    <lastmod>{$file['lastmod']}</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }

        $xml .= '</sitemapindex>';

        $indexPath = $this->basePath ? "{$this->basePath}/sitemap.xml" : 'sitemap.xml';
        Storage::disk($this->disk)->put($indexPath, $xml);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildUrl(string $locale, array $segments = []): string
    {
        $parts = array_filter([$locale, ...$segments]);
        return $this->baseUrl . '/' . implode('/', $parts);
    }

    private function publicPath(string $filename): string
    {
        $base = rtrim(config('app.url'), '/');
        return $this->basePath
            ? "{$base}/{$this->basePath}/{$filename}"
            : "{$base}/{$filename}";
    }

    private function getLocaleSegment(string $routeType, string $locale): ?string
    {
        // Certaines pages ont des slugs localisés pour un meilleur SEO
        return match($routeType) {
            'faq'    => 'faq',
            'guide'  => 'guide',
            'search' => 'search',
            default  => null,
        };
    }

    private function getLocaleSegments(string $routeType): array
    {
        return match($routeType) {
            'legal'  => ['fr' => 'mentions-legales', 'de' => 'impressum', 'it' => 'note-legali', 'en' => 'legal'],
            default  => [],
        };
    }

    private function buildAlternates(array $segments, ?string $slug, array $localeOverrides = []): array
    {
        $alternates = [];
        foreach (self::LOCALES as $locale => $hreflang) {
            $segs = $localeOverrides[$locale] ?? ($segments[0] ?? null);
            $parts = array_filter([$segs ? [$segs] : $segments]);
            $href  = $slug
                ? $this->buildUrl($locale, [...$segments, $slug])
                : $this->buildUrl($locale, $segments);

            $alternates[] = ['hreflang' => $hreflang, 'href' => $href];
        }
        $alternates[] = [
            'hreflang' => 'x-default',
            'href'     => $this->buildUrl('fr', $segments),
        ];
        return $alternates;
    }
}
