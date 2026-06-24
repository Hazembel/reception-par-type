<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modèle User
 *
 * Implémente MustVerifyEmail pour forcer la vérification d'email par lien.
 * Utilise des ULID comme clé primaire (via HasUlids) pour sécuriser les URLs
 * et prévenir l'énumération des comptes.
 *
 * @property string                  $id
 * @property string                  $name
 * @property string                  $email
 * @property \Carbon\Carbon|null     $email_verified_at
 * @property string                  $preferred_locale
 * @property int                     $subscription_level
 * @property int                     $web_tokens_balance
 * @property int                     $web_monthly_counter
 * @property \Carbon\Carbon|null     $subscribed_until
 * @property \Carbon\Carbon|null     $deleted_at
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;  // Sanctum : createToken(), tokens(), currentAccessToken()
    use HasFactory;
    use HasUlids;      // Génère automatiquement un ULID à la création
    use Notifiable;
    use SoftDeletes;   // Soft delete pour conformité RGPD et audit

    // ── Constantes Freemium ───────────────────────────────────────────────────
    public const ADMIN_LEVEL = 8;
    public const FREE_LEVEL  = 1;

    // ── Configuration Eloquent ────────────────────────────────────────────────

    /**
     * HasUlids gère déjà le casting de la PK, mais on déclare
     * explicitement pour la lisibilité et l'auto-complétion IDE.
     */
    protected $keyType = 'string';
    public $incrementing = false;

    // ── Champs remplissables (Mass Assignment Protection) ─────────────────────
    protected $fillable = [
        'name',
        'email',
        'password',
        'preferred_locale',
        'subscription_level',
        'web_tokens_balance',
        'web_monthly_counter',
        'subscribed_until',
    ];

    // ── Champs cachés (jamais sérialisés dans les réponses JSON/API) ──────────
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ── Casts automatiques ────────────────────────────────────────────────────
    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'subscribed_until'    => 'datetime',
            'password'            => 'hashed',         // Hash automatique à l'assignation
            'subscription_level'  => 'integer',
            'web_tokens_balance'  => 'integer',
            'web_monthly_counter' => 'integer',
        ];
    }

    // ── Helpers Freemium ──────────────────────────────────────────────────────

    /**
     * Vérifie si l'utilisateur possède un abonnement payant actif.
     * Combine le niveau ET la date d'expiration pour une vérification robuste.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription_level > 1
            && $this->subscribed_until !== null
            && $this->subscribed_until->isFuture();
    }

    /**
     * Détermine si l'utilisateur est administrateur de la plateforme.
     *
     * IMPORTANT : le statut admin dépend UNIQUEMENT du niveau (8), jamais d'un
     * abonnement payant actif. Utiliser canAccess(8) pour protéger l'admin était
     * un piège : cela exigeait un subscribed_until futur et pouvait verrouiller
     * un administrateur légitime hors du back-office. isAdmin() est la source de
     * vérité unique, cohérente avec UserManagementPolicy (=== 8).
     */
    public function isAdmin(): bool
    {
        return $this->subscription_level === self::ADMIN_LEVEL;
    }

    /**
     * Vérifie si l'utilisateur peut accéder à un niveau de contenu donné.
     * Exemple : $user->canAccess(3) retourne true pour les niveaux 3, 4, 5...
     */
    public function canAccess(int $requiredLevel): bool
    {
        // Le niveau 1 (gratuit) peut toujours accéder au contenu niveau 1
        if ($requiredLevel <= 1) {
            return true;
        }

        return $this->hasActiveSubscription()
            && $this->subscription_level >= $requiredLevel;
    }

    /**
     * Vérifie si l'utilisateur a encore des tokens disponibles.
     */
    public function hasTokens(): bool
    {
        return $this->web_tokens_balance > 0;
    }

    /**
     * Consomme N tokens et sauvegarde. Retourne false si solde insuffisant.
     */
    public function consumeTokens(int $amount = 1): bool
    {
        // Décrément CONDITIONNEL ATOMIQUE : la condition `web_tokens_balance >= amount`
        // est évaluée par la base de données au moment de l'UPDATE, pas en mémoire PHP.
        // Cela ferme la fenêtre de course où deux requêtes concurrentes liraient
        // toutes deux le même solde et décrémenteraient chacune (double-dépense).
        // decrement() avec un where renvoie le nombre de lignes affectées (0 ou 1).
        $affected = static::whereKey($this->getKey())
            ->where('web_tokens_balance', '>=', $amount)
            ->decrement('web_tokens_balance', $amount);

        if ($affected === 0) {
            return false; // Solde insuffisant — aucune ligne modifiée
        }

        // Synchroniser l'instance en mémoire avec la valeur réellement persistée.
        $this->web_tokens_balance = max(0, $this->web_tokens_balance - $amount);

        return true;
    }

    /**
     * Incrémente le compteur mensuel de consultations web.
     * Appelé à chaque recherche/consultation de fiche.
     */
    public function incrementMonthlyCounter(): void
    {
        $this->increment('web_monthly_counter');
    }

    /**
     * Retourne le nombre de consultations restantes ce mois
     * selon le plafond défini par niveau d'abonnement.
     */
    public function remainingMonthlyQuota(): int
    {
        $limits = config('freemium.monthly_limits', []);
        $limit  = $limits[$this->subscription_level] ?? 0;

        return max(0, $limit - $this->web_monthly_counter);
    }

    /**
     * Label lisible du niveau d'abonnement (utile dans les vues Blade).
     */
    public function subscriptionLabel(): string
    {
        return match ($this->subscription_level) {
            1       => 'Gratuit',
            2       => 'Starter',
            3       => 'Basic',
            4       => 'Pro',
            5       => 'Pro+',
            6       => 'Business',
            7       => 'Business+',
            8       => 'Entreprise',
            default => 'Inconnu',
        };
    }
}
