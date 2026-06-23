# réception-par-type.ch

> **Plateforme SaaS de consultation des données techniques automobiles officielles de l'ASTRA (OFROU) — Suisse**

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11-red)](https://laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3-38bdf8)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3-8bc34a)](https://alpinejs.dev)
[![License](https://img.shields.io/badge/Licence-Propriétaire-gray)]()

---

## 📋 Table des matières

1. [Présentation](#présentation)
2. [Fonctionnalités — Les 9 modules](#fonctionnalités--les-9-modules)
3. [Prérequis techniques](#prérequis-techniques)
4. [Installation rapide](#installation-rapide)
5. [Installation pas-à-pas](#installation-pas-à-pas)
6. [Configuration .env](#configuration-env)
7. [Laravel Scheduler — Imports ASTRA](#laravel-scheduler--imports-astra)
8. [Structure du projet](#structure-du-projet)
9. [Commandes utiles](#commandes-utiles)
10. [Architecture technique](#architecture-technique)
11. [Déploiement en production](#déploiement-en-production)

---

## Présentation

**réception-par-type.ch** est un SaaS suisse B2C et B2B permettant de consulter les données techniques homologuées des véhicules à moteur immatriculés en Suisse, en utilisant le **numéro de réception par type (TG — Typengenehmigung)** comme clé d'entrée principale.

Les données proviennent des fichiers **TARGA** publiés par l'**ASTRA (Office fédéral des routes / OFROU)** et sont synchronisées automatiquement chaque mois (fichier complet) et chaque semaine (newsletter des nouvelles homologations).

### Pour qui ?

| Cible | Usage |
|-------|-------|
| Particuliers | Vérifier les données homologuées de leur véhicule avant achat de pièces |
| Garagistes | Validation rapide des jantes, pneus et masses lors d'un diagnostic |
| Importateurs | Accès en masse aux données via API REST B2B |
| Services cantonaux | Consultation des données OFROU pour les contrôles MFK |

---

## Fonctionnalités — Les 9 modules

### Module 1 — Fondations & Multilinguisme
- Identifiants ULID (anti-énumération des URLs)
- Middleware `SetLocale` avec validation stricte (HTTP 400 sur locale invalide)
- URLs localisées `/fr/`, `/de/`, `/it/`, `/en/`
- Balises `hreflang` automatiques (SEO international Suisse)
- Modèle Freemium 8 niveaux (Gratuit → Entreprise)

### Module 2 — Import ASTRA Automatisé
- `ImportAstraMainJob` : lecture streaming par chunks de 300 Mo sans pic mémoire
- `ImportAstraNewsletterJob` : import incrémentiel des nouvelles homologations
- Idempotence par hash SHA-256 (rejeu sans doublons)
- Planificateur : mensuel le 10 à 02h00 / hebdomadaire le lundi à 06h00
- Interface admin `/admin/import` avec historique et bouton retry

### Module 3 — UI/UX Haut de Gamme
- Design system "Nuit Alpine × Précision Helvétique" (Tailwind CSS)
- Dark mode automatique (préférence système + toggle utilisateur)
- Navbar glassmorphism avec effet blur
- Hero animé avec compteur kilométrique SVG signature
- Compteurs Alpine.js (easing exponentiel, format suisse `500'000`)
- Glassmorphism sur les cartes bloquées (données jamais dans le DOM)
- Skeleton Loader 4 variantes (`animate-pulse` + shimmer)

### Module 4 — Aide Contextuelle & SEO
- Composant `<x-tooltip>` (survol desktop, clic mobile, accessible)
- Page FAQ avec accordéon Alpine.js + **JSON-LD FAQPage** (Rich Snippets Google)
- Guide pas-à-pas 3 étapes + sidebar sticky (IntersectionObserver)
- Traductions FR / DE / IT / EN

### Module 5 — Contrôle d'Accès & SEO Avancé
- Middleware `CheckVehicleAccess` : matrice complète niveaux 1–8
  - Niveau 1 : données nullifiées PHP (anti-cloaking Google)
  - Niveaux 2–3 : table pivot jetons avec transaction atomique
  - Niveau 4 : quota mensuel 500 fiches
  - Niveaux 5–8 : accès illimité (fast path)
- `SearchRequest` : validation regex stricte (anti-injection SQL)
- Meta SEO dynamiques par locale pour chaque page résultat
- Sitemap XML multilingue avec `<xhtml:link hreflang>` par blocs de 40 000 URLs
- Disclaimer OFROU + modal "Signaler une anomalie" (rate-limited)

### Module 6 — Intelligence Métier
- `TyreService` : décodage ETRTO, diamètre `(L × S/100 × 2) + (Ø × 25.4)`, alternatives légales ASA ±8%
- `WheelService` : compatibilité cross-véhicule (PCD / alésage / ET), cache 24h
- `CantonalTaxService` : 12 cantons suisses (ZH, VD, GE, BE, VS, FR, TI, SG, LU, AG, BS, NE, JU)

### Module 7 — API B2B Sécurisée
- Authentification Laravel Sanctum (tokens SHA-256, jamais en clair)
- Clés préfixées `rpt_` (détectables par GitHub Secret Scanning)
- Double rate limiting Redis : par seconde + par minute
- Quota mensuel contractuel (niveaux 6/7/8)
- Endpoints : `GET /api/v1/vehicle/tg/{tg}`, `/tyres/{tg}`, `/wheels/{tg}`
- Filtrage strict des champs (jamais d'ID interne, slug, timestamps BDD)

### Module 8 — Administration
- Dashboard avec 4 blocs et TTL de cache différenciés
- Configuration dynamique des tarifs via `pricing_plans` (centimes entiers)
- Gestion utilisateurs : upgrade niveau, ajout jetons, prolongation abonnement
- Journal d'audit complet pour toutes les actions admin
- Alertes sécurité (IPs suspectes, taux d'erreur API)

### Module 9 — Affiliation & Facturation Suisse
- Programme d'affiliation avec codes personnalisés (ex: `GARAGE10`)
- Middleware `TrackAffiliate` : cookie 30 jours HttpOnly + déduplication IP
- Hook PayPal : vérification signature HMAC-SHA256 → commission automatique
- Factures PDF A4 conformes normes suisses (TVA art. 10 LTVA ou 8.1%)
- Numérotation séquentielle `RPT-YYYY-NNNNN` avec verrouillage BDD
- Envoi automatique par e-mail + stockage privé 7 ans

---

## Prérequis techniques

### Serveur

| Composant | Version minimale | Recommandé |
|-----------|-----------------|------------|
| PHP | 8.2 | 8.3 |
| MySQL / MariaDB | 8.0 / 10.6 | MySQL 8.1 |
| Redis | 6.0 | 7.0 |
| Composer | 2.5 | 2.7+ |
| Node.js | 18 LTS | 20 LTS |
| npm | 9 | 10 |

### Extensions PHP requises

```
pdo  pdo_mysql  mbstring  openssl  fileinfo  intl  zip  bcmath  curl
```

### Extensions PHP recommandées

```
redis  (pour le cache et les queues)
```

### Dépendances Composer notables

```
laravel/framework:^11      Laravel 11
laravel/sanctum:^4         Authentification API
dompdf/dompdf:^2           Génération PDF factures
```

### Dépendances npm notables

```
alpinejs:^3                Réactivité front-end
@alpinejs/focus            Accessibilité modales
@alpinejs/collapse         Accordéons FAQ
```

---

## Installation rapide

```bash
# 1. Cloner et installer les dépendances
git clone https://github.com/votre-org/reception-par-type.git
cd reception-par-type
composer install
npm install && npm run build

# 2. Configurer l'environnement
cp .env.example .env
# → Editez .env : DB_*, MAIL_*, PAYPAL_*, etc.

# 3. Lancer l'installateur automatique
php artisan app:install

# 4. Démarrer
php artisan serve
```

→ **http://localhost:8000** — Interface web
→ **http://localhost:8000/admin** — Interface admin

---

## Installation pas-à-pas

### Étape 1 — Récupérer le code source

```bash
# Via Git
git clone https://github.com/votre-org/reception-par-type.git
cd reception-par-type

# Ou via archive ZIP
unzip reception-par-type.zip
cd reception-par-type
```

### Étape 2 — Installer les dépendances PHP

```bash
composer install --optimize-autoloader
```

> **Note** : L'installation peut prendre 1-3 minutes selon votre connexion.

### Étape 3 — Installer les dépendances front-end

```bash
npm install
npm run build        # Production
# ou
npm run dev          # Développement (HMR activé)
```

### Étape 4 — Configurer l'environnement

```bash
cp .env.example .env
```

Éditez `.env` avec votre éditeur préféré et configurez au minimum :
- La connexion à la base de données (`DB_*`)
- L'URL de l'application (`APP_URL`)

```bash
# Générer la clé d'application
php artisan key:generate
```

### Étape 5 — Créer la base de données

```sql
-- Dans MySQL
CREATE DATABASE reception_par_type CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Étape 6 — Lancer l'installateur

```bash
php artisan app:install
```

L'installateur effectue automatiquement :
- ✓ Vérification des prérequis PHP
- ✓ Création des migrations (toutes les tables)
- ✓ Seeding des plans tarifaires (8 niveaux)
- ✓ Création du compte administrateur
- ✓ Création des répertoires de stockage
- ✓ Lien symbolique `public/storage`
- ✓ Vidage de tous les caches

### Étape 7 — Lancer le serveur

```bash
# Développement
php artisan serve

# Avec queue worker (nécessaire pour les imports et factures)
php artisan queue:work redis --queue=imports-heavy,imports,invoices,default --timeout=18000
```

### Étape 8 — Importer les données ASTRA (première fois)

```bash
# 1. Déposez le fichier TG-Automobil.txt dans :
#    storage/app/astra/2000/TG-Automobil.txt

# 2. Lancez l'import (attention : 1-3h pour 300 Mo)
php artisan astra:import --type=main --force
```

> **Téléchargement des fichiers ASTRA** : Disponibles sur le portail de l'OFROU
> après inscription professionnelle : https://www.astra.admin.ch/typengenehmigung

---

## Configuration .env

### Base de données

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reception_par_type
DB_USERNAME=votre_user
DB_PASSWORD=votre_mot_de_passe
```

### Cache & Queues (Redis recommandé)

```dotenv
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

> **Fallback sans Redis** : remplacez `redis` par `file` pour le développement local.
> Les performances seront réduites mais fonctionnelles.

### E-mail (SMTP)

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=user@example.com
MAIL_PASSWORD=votre_mot_de_passe_smtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@reception-par-type.ch
MAIL_FROM_NAME="réception-par-type.ch"
```

**Services recommandés** : Mailgun (transactionnel), OVH (hébergement CH), Gmail (tests dev)

Pour les tests locaux sans SMTP :
```dotenv
MAIL_MAILER=log   # E-mails écrits dans storage/logs/laravel.log
```

### PayPal (Sandbox pour les tests)

```dotenv
PAYPAL_MODE=sandbox              # Changez en 'live' pour la production
PAYPAL_CLIENT_ID=AaBb...         # Dashboard PayPal Developer → My Apps
PAYPAL_CLIENT_SECRET=EeFf...
PAYPAL_WEBHOOK_ID=WH-xxxx        # Créez un webhook sur dashboard.paypal.com
                                 # URL : https://votre-domaine.ch/webhooks/paypal
                                 # Événements : PAYMENT.CAPTURE.COMPLETED, PAYMENT.CAPTURE.REFUNDED
```

> **Test PayPal Sandbox** :
> 1. Créez un compte sur https://developer.paypal.com
> 2. Créez une app Sandbox dans "My Apps & Credentials"
> 3. Utilisez les comptes de test générés automatiquement

### Facturation suisse

```dotenv
BILLING_COMPANY="Votre Société Sàrl"
BILLING_ADDRESS="Votre adresse"
BILLING_POSTAL="CH-XXXX"
BILLING_CITY="Votre ville"
BILLING_UID=""               # CHE-XXX.XXX.XXX (laissez vide si pas encore immatriculé)
BILLING_VAT_EXEMPT=true      # true si CA < 100 000 CHF/an (art. 10 LTVA)
BILLING_VAT_RATE=810         # 810 = 8.10% (taux normal 2024) — ignoré si exempt
```

### Chemins ASTRA

```dotenv
ASTRA_PATH_2000=/var/www/astra/2000/     # Dossier des fichiers mensuels
ASTRA_PATH_5000=/var/www/astra/5000/     # Dossier de la newsletter hebdo
ASTRA_MAIN_FILE=/var/www/astra/2000/TG-Automobil.txt
ASTRA_CHUNK_SIZE=1000                    # Lignes par chunk (augmenter si RAM > 8 Go)
```

---

## Laravel Scheduler — Imports ASTRA

### Configuration du cron (serveur Linux)

```bash
# Ouvrir le crontab
crontab -e

# Ajouter cette ligne (remplacez /var/www/reception-par-type par votre chemin)
* * * * * cd /var/www/reception-par-type && php artisan schedule:run >> /dev/null 2>&1
```

### Planification configurée dans `routes/console.php`

| Tâche | Fréquence | Heure | Verrou |
|-------|-----------|-------|--------|
| Import newsletter ASTRA (5000) | Tous les lundis | 06:00 | 10 min |
| Import principal ASTRA (2000) | Le 10 de chaque mois | 02:00 | 5 heures |
| Alerte si newsletter manquée | Tous les vendredis | 18:00 | — |
| Purge logs d'import > 6 mois | Le 1er du mois | 03:00 | — |
| Reset compteurs API mensuels | Le 1er du mois | 00:05 | — |
| Génération sitemap XML | Tous les lundis | 04:00 | — |

### Démarrage manuel des imports

```bash
# Newsletter hebdomadaire (quelques minutes)
php artisan astra:import --type=newsletter

# Fichier principal mensuel (1-3 heures selon taille)
php artisan astra:import --type=main

# Forcer le re-traitement d'un fichier déjà importé
php artisan astra:import --type=main --force

# Importer un fichier spécifique
php artisan astra:import --type=main --file=/chemin/vers/fichier.txt

# Dry-run (compte les URLs sans écrire)
php artisan sitemap:generate --dry-run
```

### Workers de queue (processus en arrière-plan)

Configurez **Supervisor** pour maintenir les workers actifs :

```ini
# /etc/supervisor/conf.d/reception-par-type.conf

[program:rpt-queue-default]
command=php /var/www/reception-par-type/artisan queue:work redis --queue=invoices,default --timeout=120 --tries=3
directory=/var/www/reception-par-type
autostart=true
autorestart=true
numprocs=2
stderr_logfile=/var/log/rpt-queue-default.log

[program:rpt-queue-heavy]
command=php /var/www/reception-par-type/artisan queue:work redis --queue=imports-heavy --timeout=18000 --tries=2 --sleep=60
directory=/var/www/reception-par-type
autostart=true
autorestart=true
numprocs=1
stderr_logfile=/var/log/rpt-queue-heavy.log
```

```bash
# Recharger Supervisor après modification
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

---

## Structure du projet

```
reception-par-type/
├── app/
│   ├── Console/Commands/          # Commandes Artisan (InstallCommand, ProcessAstraImports, GenerateSitemap)
│   ├── Events/                    # PaymentSucceeded
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/             # Dashboard, Pricing, Users, Import, Affiliates
│   │   │   ├── Account/           # Affiliate, InvoiceController
│   │   │   └── Api/V1/            # VehicleApiController, ApiKeyController
│   │   ├── Middleware/            # SetLocale, CheckVehicleAccess, EnsureApiKeyHasQuota,
│   │   │                          # AdminAccess, TrackAffiliate
│   │   └── Requests/              # SearchRequest
│   ├── Jobs/                      # ImportAstraMainJob, ImportAstraNewsletterJob,
│   │                              # GenerateSwissInvoice
│   ├── Listeners/                 # RecordAffiliateCommission, CreateInvoiceOnPayment
│   ├── Mail/                      # InvoiceMail
│   ├── Models/                    # User, Vehicle, VehicleTranslation, ImportLog,
│   │                              # PricingPlan, UserUnlockedVehicle, ApiLog,
│   │                              # Affiliate, AffiliateClick, AffiliateEarning, Invoice
│   ├── Policies/                  # UserManagementPolicy
│   ├── Providers/                 # VehicleServicesProvider
│   └── Services/                  # TyreService, WheelService, CantonalTaxService,
│                                  # AstraFileParser
│
├── config/
│   ├── freemium.php               # Limites et tarifs freemium (référence → pricing_plans)
│   ├── astra.php                  # Chemins et paramètres imports
│   ├── api_plans.php              # Plans API B2B
│   └── billing.php                # Coordonnées facturation suisse
│
├── database/
│   ├── migrations/                # 11 migrations (users, vehicles, imports, pricing, etc.)
│   └── seeders/
│       ├── PricingPlanSeeder.php  # 8 niveaux tarifaires
│       └── VehicleTranslationSeeder.php  # Codes ASTRA → libellés multilingues
│
├── resources/
│   ├── css/app.css                # Design system + keyframes
│   ├── js/app.js                  # Alpine.js + plugins
│   ├── lang/{fr,de,it,en}/        # Traductions : app.php, help.php, access.php
│   └── views/
│       ├── layouts/app.blade.php
│       ├── components/            # tooltip, locked-card, skeleton-loader, vehicle-card, etc.
│       ├── pages/                 # home, search, vehicle/show, faq, guide
│       ├── admin/                 # dashboard, pricing, users, import, affiliates
│       ├── account/               # affiliate, invoices
│       └── invoices/pdf.blade.php # Template facture PDF
│
├── routes/
│   ├── web.php                    # Routes localisées /{locale}/...
│   ├── api.php                    # Routes API /api/v1/...
│   ├── admin.php                  # Routes /admin/...
│   ├── console.php                # Scheduler
│   └── webhooks.php               # PayPal webhook
│
├── storage/app/
│   ├── astra/2000/                # Fichiers ASTRA mensuels (TG-Automobil.txt)
│   ├── astra/5000/                # Newsletter hebdomadaires
│   └── invoices/                  # Factures PDF (accès privé)
│
├── .env.example                   # Template de configuration
├── artisan                        # CLI Laravel
├── composer.json
├── package.json
├── tailwind.config.js
└── vite.config.js
```

---

## Commandes utiles

### Installation & Maintenance

```bash
# Installation complète
php artisan app:install

# Repartir de zéro (attention : efface toutes les données)
php artisan app:install --fresh

# Mode CI/CD (pas d'interaction, admin par défaut)
php artisan app:install --force

# Vider tous les caches
php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan cache:clear
```

### Imports ASTRA

```bash
php artisan astra:import --type=newsletter    # Newsletter hebdo
php artisan astra:import --type=main          # Fichier principal (~300 Mo)
php artisan astra:import --type=all           # Les deux
php artisan astra:import --type=main --force  # Forcer même si déjà importé
```

### SEO

```bash
php artisan sitemap:generate                  # Génère le sitemap XML multilingue
php artisan sitemap:generate --dry-run        # Compte les URLs sans écrire
```

### Tests

```bash
php artisan test                              # Tous les tests
php artisan test --filter Module6             # Tests du Module 6
php artisan test --filter Module7ApiTest      # Tests API
php artisan test --coverage                   # Avec couverture de code
```

---

## Architecture technique

### Décisions architecturales clés

| Décision | Justification |
|----------|---------------|
| **ULID** au lieu d'UUID4 | Tri chronologique + non-devinable + URL-safe |
| **`in_array(..., true)`** pour les locales | Comparaison stricte (type + valeur) → HTTP 400 |
| **Données nullifiées PHP** (anti-cloaking) | Conformité Google SGE/Panda — aucune donnée sensible dans le HTML |
| **`DB::transaction()`** sur les jetons | Atomicité — impossibilité de consommer 2 fois |
| **Chunks de 2000** + `fopen/fgets` | Mémoire constante < 50 Mo pour des fichiers de 300 Mo |
| **`upsert()` massif** au lieu de `updateOrCreate()` | 10-15× plus rapide sur les gros imports |
| **Centimes entiers** pour les prix | Zéro problème d'arrondi flottant |
| **SHA-256 des IPs** dans les logs | RGPD — non-réversible mais dédupliquable |
| **Cache 1h sur `PricingPlan`** | Lecture BDD à chaque requête évitée, invalidation sur `saved` |

---

## Déploiement en production

### Checklist avant mise en ligne

```bash
# 1. Variables .env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true

# 2. Optimisation
composer install --no-dev --optimize-autoloader
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 3. Base de données
php artisan migrate --force

# 4. Cron actif
crontab -l | grep schedule:run  # Doit afficher la ligne

# 5. Supervisor lancé
sudo supervisorctl status

# 6. HTTPS configuré (Nginx / Apache / Caddy)
# SESSION_SECURE_COOKIE=true dans .env
```

### Configuration Nginx recommandée

```nginx
server {
    listen 443 ssl http2;
    server_name reception-par-type.ch www.reception-par-type.ch;

    root /var/www/reception-par-type/public;
    index index.php;

    # Sécurité
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Interdit l'accès aux dossiers sensibles
    location ~ /\. { deny all; }
    location ~ /storage/app/invoices { deny all; }  # Factures → accès via Controller
}
```

---

## Support & Contact

- **Documentation API** : https://reception-par-type.ch/api/docs
- **Source des données** : [OFROU/ASTRA](https://www.astra.admin.ch)
- **Signalement d'anomalie** : Bouton "Signaler" sur chaque fiche technique

---

*Données indicatives basées sur les fichiers officiels TARGA de l'OFROU.
Aucune responsabilité juridique ou financière n'est engagée en cas d'erreur ou de dommage résultant de la commande de pièces incompatibles. Consultez toujours un professionnel qualifié.*

*© 2024 reception-par-type.ch — Tous droits réservés*

---

## 🔬 Audit final — correctifs de robustesse (production)

Une passe d'audit dédiée aux cas limites et à la stabilité production a apporté les renforcements suivants :

### Module 2 — Nettoyage disque garanti
Les jobs d'import (`ImportAstraMainJob`, `ImportAstraNewsletterJob`) suppriment désormais le fichier source via un bloc **`try/finally`** : le fichier est retiré du disque **même si l'import plante** en cours de traitement. Indispensable sur une infra à espace limité (un fichier TARGA de 300 Mo ne reste jamais sur le disque). Le flag `deleteAfterImport` (true par défaut) permet de conserver le fichier en débogage.

### Module 6 — TyreService blindé
Le décodage des dimensions rejette les entrées invalides (chaîne vide, espaces seuls, format texte, valeurs aberrantes hors bornes physiques) via `InvalidArgumentException`, tout en tolérant les variations d'espacement valides et le préfixe US « P ». Les diamètres sont arrondis à 2 décimales.

### Modules 5 & 8 — Fuseau horaire suisse
L'application est calée sur **`Europe/Zurich`** (`config/app.php`, surchargeable via `APP_TIMEZONE`). La réinitialisation des compteurs et quotas mensuels bascule donc bien à **minuit, heure de Zurich**, et non à minuit UTC.

### Module 9 — Idempotence anti double-paiement
Une table **`processed_payments`** (avec contrainte `UNIQUE` sur `paypal_order_id`) sert de garde : `ProcessedPayment::claim()` réserve l'ordre de façon atomique au tout début du traitement. Un même webhook PayPal envoyé deux fois (retry réseau, race condition) **ne crédite jamais deux fois les jetons** ni ne prolonge deux fois l'abonnement. Les factures et commissions disposaient déjà de leurs propres gardes (`Invoice::exists`, `AffiliateEarning::exists`).

### Suite de tests augmentée
Nouveaux tests : suppression du fichier source après un import **qui plante**, blindage complet du `TyreService` (10 cas limites), **double webhook PayPal** (non double-crédit), cohérence du fuseau horaire suisse, et mocks PayPal couvrant les deux formats (`CAPTURE.COMPLETED` et `SALE.COMPLETED`).

---

## 🔬 Audit Zéro Erreur — LOT 2 (sécurité, quotas, API)

### Étanchéité des données — ✅ validée
Le masquage des données payantes est en **défense en profondeur** : les 8 champs sensibles sont nullifiés côté serveur dans `VehicleController` **avant** le rendu, ET rendus uniquement sous `@if($canViewAdvanced)` dans la vue (sinon `<x-locked-card>` squelette). Aucune fuite possible par inspection d'élément.

### Faille corrigée — double-dépense de jetons
`User::consumeTokens()` faisait une vérification en mémoire puis un `decrement()` : deux requêtes concurrentes pouvaient débloquer deux fiches avec un seul jeton. Désormais **décrément conditionnel atomique** (`WHERE web_tokens_balance >= amount`), et `CheckVehicleAccess` **respecte la valeur de retour** (pas de déverrouillage si le jeton n'a pas été débité).

### Faille corrigée — accès admin aux fonctions financières
`AdminPricingController` (modification des tarifs) et `AdminAffiliateController` (approbation/paiement des commissions) n'avaient **aucune autorisation au niveau contrôleur** — seul le middleware les protégeait. Ajout d'une **policy `managePricing`** et de gardes `isAdmin()` sur toutes les actions (défense en profondeur).

### Faille corrigée — logique admin incohérente
`AdminImportController` utilisait `canAccess(8)`, qui exige un `subscribed_until` futur : un administrateur légitime sans abonnement payant aurait été **verrouillé hors du back-office**. Introduction de `User::isAdmin()` (niveau 8 strict, sans condition d'abonnement), source de vérité unique alignée sur la Policy. Tous les contrôleurs admin utilisent désormais cette logique.

### Rate Limiter — ✅ robuste
Triple couche (par seconde, par minute via cache atomique, quota mensuel), blocage **instantané** (`tooManyAttempts()` avant `hit()`), réponse 429 avec `Retry-After` et `limit_type`. Note : le quota mensuel (compteur dénormalisé) peut être légèrement dépassé sous très forte concurrence — le rate-limit/seconde borne ce risque ; comportement volontaire (facturation après réponse 2xx).

---

## 🔬 Audit Zéro Erreur — LOT 3 (tuyauterie financière)

### Faille corrigée — idempotence PayPal sous course
La barrière du webhook testait `Invoice::where('paypal_order_id')->exists()`. Or la facture est générée par un **job asynchrone** : sous double-webhook rapproché (retry réseau PayPal), le 2e webhook arrivait avant que la facture du 1er ne soit créée → l'événement était **redispatché**. Correctif : la garde d'idempotence est désormais **`ProcessedPayment::claim($orderId)` synchrone et atomique, en amont du dispatch** dans `PayPalWebhookController`. L'événement `PaymentSucceeded` n'est émis qu'une fois par ordre, même sous course. Le `claim()` redondant a été retiré d'`ActivateSubscription`.

### Renforcement — doublons impossibles au niveau base
Ajout d'une contrainte **`unique` sur `invoices.paypal_order_id`** (il n'y avait qu'un index). `affiliate_earnings.paypal_order_id` et `processed_payments.paypal_order_id` étaient déjà uniques. `GenerateSwissInvoice` et `RecordAffiliateCommission` interceptent désormais la `QueryException` d'unicité : une exécution concurrente ne crée jamais de doublon ni ne fait planter le job.

### Conformité des factures suisses — ✅ validée
Numéro séquentiel `RPT-YYYY-NNNNN` généré sous `lockForUpdate()` (pas de collision), montants **en centimes entiers** (HT + TVA = TTC garanti, aucun centime perdu), TVA 8.1 % ou mention d'exonération **art. 10 al. 2 let. a LTVA**, devise CHF, format suisse `1'234.50`, coordonnées vendeur + IDE/UID, adresse acheteur, référence PayPal, conditions de paiement. Correction mineure : cohérence `total = prix unitaire arrondi × quantité` dans les lignes (suppression d'un double arrondi).

### Cookies d'affiliation & commission — ✅ validés
Cookie `rpt_ref` : **30 jours**, `HttpOnly`, `Secure` en production, `SameSite=Lax`, code validé en base, IP hachée SHA-256 (RGPD), déduplication par jour. Commission : `round(montant_cts × taux_permille / 1000)` — exacte, en centimes entiers, taux par défaut 100 ‰ (10 %).

---

## 🆕 Évolution — Recherche VIN, fichier Moto & couplage Émissions

### 1. Recherche par VIN sécurisée (préfixe 9 caractères) + homologation EU
Nouvelles colonnes sur `vehicles` (migration `000014`) : `vin_prefix` (CHAR 9, indexé), `eu_type_approval` (indexé). On ne stocke **jamais** le VIN complet : seul le préfixe non individualisant (WMI + VDS) est conservé. La recherche (`SearchController` mode `vin`) **tronque systématiquement** la saisie — y compris un VIN complet de 17 caractères lu sur le permis de circulation — à ses 9 premiers caractères via `Vehicle::normalizeVinPrefix()`, puis applique une **égalité exacte** sur `vin_prefix` (index, pas de LIKE). Un VIN trop court ne renvoie aucun résultat (sécurité anti-catalogue).

### 2. Intégration du fichier Moto (TG-Moto.txt)
Nouvelle colonne `vehicle_type` (`enum('car','motorcycle')`, défaut `car`, indexée). Le **même** `ImportAstraMainJob` traite les deux fichiers grâce à un paramètre `vehicleType` au constructeur — pas de duplication. Commande : `php artisan astra:import --type=moto`. Le type est transmis au `CantonalTaxService` pour différencier les barèmes (les motos sont souvent taxées à la cylindrée).

### 3. Couplage avec le fichier des émissions (emissionen.txt)
Nouveau job `ImportAstraEmissionsJob` : lecture en streaming, **couplage par numéro de réception (TG)** — il met à JOUR les fiches existantes (CO₂, `pollution_norm`, `code_emissions`) sans jamais créer de coquille vide ; une ligne au TG inconnu est comptée comme « skipped ». Nettoyage disque garanti par **`try/finally`** (fermeture du handle + suppression du fichier source même en cas de crash). Commande : `php artisan astra:import --type=emissions`. L'import complet `--type=all` enchaîne **voitures → motos → émissions** dans cet ordre (les émissions doivent suivre les fiches TG).

Fichiers nouveaux/modifiés : migration `000014`, `Vehicle` (modèle), `AstraFileParser`, `ImportAstraMainJob`, `ImportAstraEmissionsJob` (nouveau), `SearchController`, `SearchRequest`, `ProcessAstraImports`, `config/astra.php`, `VehicleFactory`.
