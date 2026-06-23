# 🚀 Guide d'installation — reception-par-type.ch

Ce guide vous accompagne pas-à-pas, de l'extraction de l'archive jusqu'au premier lancement.
Comptez **5 à 10 minutes** sur une machine déjà équipée de PHP, Composer et MySQL.

---

## 1. Prérequis

Avant de commencer, vérifiez que vous disposez de :

| Outil | Version min. | Vérifier avec |
|-------|-------------|---------------|
| PHP | 8.2 | `php -v` |
| Composer | 2.5 | `composer -V` |
| MySQL / MariaDB | 8.0 / 10.6 | `mysql --version` |
| Node.js + npm | 18 / 9 | `node -v && npm -v` |
| Redis *(recommandé)* | 6.0 | `redis-cli ping` |

**Extensions PHP requises :** `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `intl`, `zip`, `bcmath`, `curl`.

Vérifiez-les en une commande :
```bash
php -m | grep -E "pdo_mysql|mbstring|openssl|fileinfo|intl|zip|bcmath|curl"
```

---

## 2. Extraire l'archive

```bash
unzip reception-par-type.zip
cd reception-par-type
```

---

## 3. Installer les dépendances

```bash
# Dépendances PHP (Laravel, Sanctum, dompdf…)
composer install

# Dépendances front-end (Tailwind, Alpine.js) puis compilation
npm install
npm run build
```

> 💡 En développement, utilisez `npm run dev` à la place de `npm run build` pour le rechargement à chaud.

---

## 4. Créer la base de données

Connectez-vous à MySQL et créez une base vide :

```sql
CREATE DATABASE reception_par_type
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

---

## 5. Configurer l'environnement

```bash
cp .env.example .env
```

Ouvrez `.env` et renseignez **au minimum** la connexion base de données :

```dotenv
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=reception_par_type
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

Si vous n'avez **pas** Redis, basculez ces trois lignes sur `file` / `sync` :
```dotenv
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

---

## 6. Lancer l'installateur automatique

Une seule commande fait tout le reste (clé d'app, migrations, seeders, compte admin, caches, dossiers de stockage) :

```bash
php artisan app:install
```

L'assistant vous guide avec des retours colorés et vous demande les informations du **compte administrateur**.

> Pour une installation **non interactive** (CI/CD), définissez `ADMIN_EMAIL` / `ADMIN_PASSWORD` dans `.env` puis lancez :
> ```bash
> php artisan app:install --force
> ```

---

## 7. Lancer le serveur

```bash
php artisan serve
```

Rendez-vous sur :
- **Site** : http://localhost:8000
- **Administration** : http://localhost:8000/admin (connectez-vous avec le compte admin créé à l'étape 6)

---

## 8. Importer les données ASTRA *(optionnel mais nécessaire pour des résultats)*

1. Déposez le fichier officiel TARGA dans :
   ```
   storage/app/astra/2000/TG-Automobil.txt
   ```
2. Lancez l'import (le fichier complet peut prendre 1 à 3 h) :
   ```bash
   php artisan astra:import --type=main --force
   ```

---

## 9. Configurer le planificateur *(production)*

Pour les imports automatiques (mensuel + hebdomadaire), ajoutez le cron Laravel :

```bash
crontab -e
```
Puis collez (adaptez le chemin) :
```cron
* * * * * cd /chemin/vers/reception-par-type && php artisan schedule:run >> /dev/null 2>&1
```

Lancez aussi un worker de file d'attente pour les factures et imports :
```bash
php artisan queue:work redis --queue=imports-heavy,imports,invoices,default --timeout=18000
```

---

## ✅ Vérifier que tout fonctionne

```bash
# Lancer la suite de tests
php artisan test
```

En cas de souci, consultez la section *Dépannage* du `README.md`.

---

## 🔐 Important — Sécurité après installation

1. **Changez immédiatement le mot de passe admin** si vous avez utilisé le mot de passe par défaut.
2. En production, dans `.env` :
   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   SESSION_SECURE_COOKIE=true
   ```
3. Configurez les clés PayPal (`PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`, `PAYPAL_WEBHOOK_ID`) et le SMTP avant d'accepter des paiements réels.
