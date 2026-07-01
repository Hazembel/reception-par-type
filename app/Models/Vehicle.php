<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Modèle Vehicle
 *
 * Représente une fiche technique véhicule issue des données TARGA de l'ASTRA
 * (voitures TG-Automobil.txt et motos TG-Moto.txt), enrichie par le fichier des
 * émissions (emissionen.txt) via le numéro de réception par type.
 *
 * Clé d'entrée métier : `numero_tg` (Numéro de Réception par Type).
 * Utilise un ULID comme PK pour sécuriser les URLs publiques (anti-énumération).
 *
 * @property string      $id
 * @property string      $numero_tg
 * @property string|null $vin_prefix         9 premiers caractères du VIN (non individualisant)
 * @property string|null $eu_type_approval   Numéro d'homologation européen
 * @property string      $marque
 * @property string      $modele
 * @property string|null $variante
 * @property string      $vehicle_type       'car' | 'motorcycle'
 * @property string      $slug
 * @property string|null $energie
 * @property int|null    $puissance_kw
 * @property int|null    $cylindree
 * @property string|null $boite_vitesse
 * @property int|null    $poids_vide
 * @property int|null    $poids_total
 * @property int|null    $poids_remorquable
 * @property int|null    $co2
 * @property string|null $code_emissions
 * @property string|null $pollution_norm     Norme antipollution détaillée (fichier émissions)
 * @property int|null    $nb_trous
 * @property int|null    $entraxe
 * @property int|null    $alesage
 * @property int|null    $deport_et
 * @property string|null $pneus_origine
 * @property bool        $is_active
 * @property \Illuminate\Support\Carbon|null $imported_at
 */
class Vehicle extends Model
{
    use HasFactory;
    use HasUlids;    // ULID = sécurité anti-énumération pour les URLs publiques
    use SoftDeletes;

    /**
     * Longueur du préfixe VIN conservé.
     *
     * On ne stocke QUE les 9 premiers caractères du VIN :
     *   - positions 1-3  : WMI (World Manufacturer Identifier)
     *   - positions 4-9  : VDS (Vehicle Descriptor Section)
     * Les positions 10-17 (VIS, dont le numéro de série individuel) ne figurent
     * pas dans le fichier ASTRA et ne doivent jamais être stockées : le préfixe
     * est ainsi partagé par toute une série et n'identifie aucun exemplaire.
     */
    public const VIN_PREFIX_LENGTH = 9;

    /** Valeurs autorisées pour la colonne vehicle_type. */
    public const TYPE_CAR        = 'car';
    public const TYPE_MOTORCYCLE = 'motorcycle';

    // ── Configuration Eloquent ────────────────────────────────────────────────

    protected $keyType   = 'string';
    public $incrementing = false;

    // ── Mass Assignment ───────────────────────────────────────────────────────
    protected $fillable = [
        'numero_tg',
        'vin_prefix',
        'eu_type_approval',
        'marque',
        'modele',
        'variante',
        'vehicle_type',
        'slug',
        'energie',
        'puissance_kw',
        'cylindree',
        'boite_vitesse',
        'poids_vide',
        'poids_total',
        'poids_remorquable',
        'co2',
        'consommation_mixte',
        'co2_wltp',
        'consommation_wltp',
        'consommation_el',
        'autonomie_min',
        'autonomie_max',
        'energie_label',
        'code_emissions',
        'pollution_norm',
        'nb_trous',
        'entraxe',
        'alesage',
        'deport_et',
        'pneus_origine',
        'is_active',
        'imported_at',
    ];

    // ── Casts ─────────────────────────────────────────────────────────────────
    protected function casts(): array
    {
        return [
            'puissance_kw'      => 'integer',
            'cylindree'         => 'integer',
            'poids_vide'        => 'integer',
            'poids_total'       => 'integer',
            'poids_remorquable' => 'integer',
            'co2'               => 'integer',
            'consommation_mixte' => 'decimal:2',
            'co2_wltp'          => 'integer',
            'consommation_wltp' => 'decimal:2',
            'consommation_el'   => 'decimal:2',
            'autonomie_min'     => 'integer',
            'autonomie_max'     => 'integer',
            'nb_trous'          => 'integer',
            'entraxe'           => 'integer',
            'alesage'           => 'integer',
            'deport_et'         => 'integer',
            'is_active'         => 'boolean',
            'imported_at'       => 'datetime',
        ];
    }

    // ── Hooks Eloquent ────────────────────────────────────────────────────────

    /**
     * Génère automatiquement le slug SEO avant la création, et normalise le
     * préfixe VIN à l'écriture (troncature + uppercase) par double sécurité.
     */
    protected static function booted(): void
    {
        static::creating(function (Vehicle $vehicle) {
            if (empty($vehicle->slug)) {
                $vehicle->slug = static::generateSlug($vehicle);
            }
            $vehicle->vin_prefix = static::normalizeVinPrefix($vehicle->vin_prefix);
        });

        static::updating(function (Vehicle $vehicle) {
            // Régénère le slug uniquement si marque/modele/variante/numero_tg changent
            if ($vehicle->isDirty(['marque', 'modele', 'variante', 'numero_tg'])) {
                $vehicle->slug = static::generateSlug($vehicle);
            }
            if ($vehicle->isDirty('vin_prefix')) {
                $vehicle->vin_prefix = static::normalizeVinPrefix($vehicle->vin_prefix);
            }
        });
    }

    /**
     * Génère un slug de base à partir d'un tableau de données brutes (sans requête DB).
     * Utilisé par les jobs d'import pour éviter l'overhead Eloquent sur les gros volumes.
     * Ne garantit PAS l'unicité — c'est la contrainte UNIQUE sur numero_tg qui l'assure.
     */
    public static function slugFromData(array $data): string
    {
        $tgClean = preg_replace('/[^a-zA-Z0-9]/', '', $data['numero_tg'] ?? '');
        $base    = implode(' ', array_filter([
            $data['marque']   ?? '',
            $data['modele']   ?? '',
            $data['variante'] ?? '',
            $tgClean,
        ]));

        return Str::slug($base) ?: 'vehicle-' . strtolower($tgClean ?: Str::random(8));
    }

    /**
     * Génère un slug unique à partir des champs d'identification.
     * Le numéro TG nettoyé est ajouté en suffixe pour garantir l'unicité.
     */
    public static function generateSlug(Vehicle $vehicle): string
    {
        // Nettoyage du numéro TG : suppression des points et espaces
        $tgClean = preg_replace('/[^a-zA-Z0-9]/', '', $vehicle->numero_tg ?? '');

        $base = implode(' ', array_filter([
            $vehicle->marque,
            $vehicle->modele,
            $vehicle->variante,
            $tgClean,
        ]));

        $slug = Str::slug($base);

        // Si le slug est vide (caractères non romanisables), on utilise le TG seul.
        if ($slug === '') {
            $slug = 'vehicle-' . strtolower($tgClean ?: Str::random(8));
        }

        // Garantie d'unicité : ajoute un compteur si le slug est pris par un AUTRE véhicule.
        // On exclut le véhicule lui-même pour éviter de changer le slug à chaque mise à jour,
        // ce qui briserait les URLs SEO déjà indexées.
        $original = $slug;
        $count    = 1;
        $query    = static::where('slug', $slug);
        if ($vehicle->exists && $vehicle->getKey()) {
            $query->where($vehicle->getKeyName(), '!=', $vehicle->getKey());
        }

        while ($query->exists()) {
            $slug  = "{$original}-{$count}";
            $count++;
            $query = static::where('slug', $slug);
            if ($vehicle->exists && $vehicle->getKey()) {
                $query->where($vehicle->getKeyName(), '!=', $vehicle->getKey());
            }
        }

        return $slug;
    }

    /**
     * Normalise une chaîne VIN vers le préfixe stockable :
     *   - met en majuscules,
     *   - retire tout caractère non alphanumérique,
     *   - tronque aux VIN_PREFIX_LENGTH (9) premiers caractères.
     *
     * Renvoie null si l'entrée est vide après nettoyage.
     */
    public static function normalizeVinPrefix(?string $vin): ?string
    {
        if ($vin === null) {
            return null;
        }

        $clean = preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($vin)));

        if ($clean === '' || $clean === null) {
            return null;
        }

        return substr($clean, 0, self::VIN_PREFIX_LENGTH);
    }

    // ── Route Model Binding ───────────────────────────────────────────────────

    /**
     * Surcharge getRouteKeyName pour utiliser le `slug` dans les URLs.
     * Sécurité : l'ULID reste la PK interne, le slug est l'identifiant public.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ── Accesseurs pratiques ──────────────────────────────────────────────────

    /**
     * Retourne la puissance en chevaux (CV) arrondie.
     * Conversion standard : 1 kW = 1.35962 CV
     */
    public function getPuissanceCvAttribute(): ?int
    {
        if ($this->puissance_kw === null) {
            return null;
        }

        return (int) round($this->puissance_kw * 1.35962);
    }

    /**
     * Retourne le titre de la fiche pour le SEO (meta title).
     */
    public function getSeoTitleAttribute(): string
    {
        return implode(' ', array_filter([
            $this->marque,
            $this->modele,
            $this->variante,
        ])) . ' - Fiche Technique';
    }

    /**
     * Indique si le véhicule est 100% électrique.
     * Codes énergie ASTRA : '03'=Électrique (numeric), 'E'=Électrique (letter), '14'=Plug-in hybrid treated as ZEV when co2=0.
     */
    public function isElectric(): bool
    {
        return in_array($this->energie, ['03', 'E', '14'], true);
    }

    /** Indique si la fiche concerne une moto. */
    public function isMotorcycle(): bool
    {
        return $this->vehicle_type === self::TYPE_MOTORCYCLE;
    }

    /** Indique si la fiche concerne une voiture. */
    public function isCar(): bool
    {
        return $this->vehicle_type === self::TYPE_CAR;
    }

    // ── Scopes de requête courants ────────────────────────────────────────────

    /**
     * Scope : uniquement les fiches actives et non supprimées.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope : filtrer par marque (insensible à la casse).
     */
    public function scopeByMarque(Builder $query, string $marque): Builder
    {
        return $query->whereRaw('LOWER(marque) = ?', [strtolower($marque)]);
    }

    /**
     * Scope : filtrer par type d'énergie.
     */
    public function scopeByEnergie(Builder $query, string $code): Builder
    {
        return $query->where('energie', $code);
    }

    /**
     * Scope : filtrer par type de véhicule ('car' | 'motorcycle').
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('vehicle_type', $type);
    }

    /**
     * Scope : recherche par préfixe VIN.
     *
     * La valeur saisie par l'utilisateur (qui peut être un VIN COMPLET de 17
     * caractères lu sur le permis de circulation) est SYSTÉMATIQUEMENT tronquée
     * à ses 9 premiers caractères, puis comparée à la colonne vin_prefix par une
     * égalité exacte (et non un LIKE), garantissant l'usage de l'index.
     *
     * Renvoie une requête qui ne matche rien si le VIN fourni est trop court /
     * invalide, afin de ne jamais retourner tout le catalogue par erreur.
     */
    public function scopeByVin(Builder $query, string $vin): Builder
    {
        $prefix = static::normalizeVinPrefix($vin);

        // Sécurité : un préfixe vide ou incomplet ne doit jamais matcher.
        if ($prefix === null || strlen($prefix) < self::VIN_PREFIX_LENGTH) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('vin_prefix', $prefix);
    }
}
