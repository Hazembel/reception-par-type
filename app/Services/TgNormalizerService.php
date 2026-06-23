<?php

namespace App\Services;

class TgNormalizerService
{
    /**
     * Normalise un numéro TG pour la recherche en base de données.
     *
     * Convertit les variantes courantes :
     *   "27-012-000-08-00004" → "27.012.000.08.00004"
     *   "27 012 000 08 00004" → "27.012.000.08.00004"
     *
     * Gère aussi le cas SearchController où uniquement les chiffres sont présents :
     *   si ≥ 14 chiffres contigus, reconstruit le format canonique XX.XXX.XXX.XX.XXXXX.
     */
    public static function normalize(string $tg): string
    {
        $tg = trim($tg);

        // Tentative de reconstruction depuis les chiffres bruts (saisie sans séparateurs)
        $digits = preg_replace('/[^0-9]/', '', $tg);
        if (strlen($digits) >= 14) {
            return substr($digits, 0, 2) . '.' .
                   substr($digits, 2, 3) . '.' .
                   substr($digits, 5, 3) . '.' .
                   substr($digits, 8, 2) . '.' .
                   substr($digits, 10);
        }

        // Normalisation des séparateurs alternatifs (tirets, underscores, espaces → points)
        $tg = preg_replace('/[\s\-_]+/', '.', $tg);
        $tg = preg_replace('/\.{2,}/', '.', $tg);
        return trim($tg, '.');
    }
}
