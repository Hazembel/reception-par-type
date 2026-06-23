<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle VehicleTranslation
 *
 * Dictionnaire de traduction pour les codes techniques de l'ASTRA.
 * Ce modèle est principalement utilisé en lecture via le cache (config/DB).
 *
 * @property int    $id
 * @property string $category
 * @property string $code
 * @property string $label_fr
 * @property string $label_de
 * @property string $label_it
 * @property string $label_en
 * @property int    $sort_order
 */
class VehicleTranslation extends Model
{
    protected $fillable = [
        'category',
        'code',
        'label_fr',
        'label_de',
        'label_it',
        'label_en',
        'sort_order',
    ];

    // ── Helper statique ───────────────────────────────────────────────────────

    /**
     * Retourne le libellé traduit pour un code et une catégorie donnés.
     *
     * Utilise le cache pour éviter de requêter la BDD à chaque affichage.
     * Le cache est invalidé lors de la mise à jour des traductions.
     *
     * @param  string $category  Ex: "energie", "boite_vitesse"
     * @param  string $code      Ex: "01", "A"
     * @param  string $locale    Ex: "fr", "de", "it", "en"
     * @return string            Libellé traduit ou le code brut si introuvable
     */
    public static function translate(string $category, string $code, string $locale = 'fr'): string
    {
        // Validation défensive : seules les locales supportées sont acceptées
        $supportedLocales = ['fr', 'de', 'it', 'en'];
        if (!in_array($locale, $supportedLocales, true)) {
            $locale = 'fr';
        }

        $cacheKey = "vt_{$category}_{$code}_{$locale}";

        return cache()->remember($cacheKey, now()->addHours(24), function () use ($category, $code, $locale) {
            $translation = static::where('category', $category)
                ->where('code', $code)
                ->first();

            if (!$translation) {
                // Retourne le code brut si pas de traduction (fail gracefully)
                return strtoupper($code);
            }

            $field = "label_{$locale}";

            return $translation->{$field} ?? $translation->label_fr ?? $code;
        });
    }

    /**
     * Retourne toutes les traductions d'une catégorie pour un select HTML.
     * Format : ['code' => 'label_traduit', ...]
     *
     * @param  string $category
     * @param  string $locale
     * @return array<string, string>
     */
    public static function forSelect(string $category, string $locale = 'fr'): array
    {
        $cacheKey = "vt_select_{$category}_{$locale}";

        return cache()->remember($cacheKey, now()->addHours(24), function () use ($category, $locale) {
            $field = "label_{$locale}";

            return static::where('category', $category)
                ->orderBy('sort_order')
                ->orderBy($field)
                ->pluck($field, 'code')
                ->toArray();
        });
    }
}
