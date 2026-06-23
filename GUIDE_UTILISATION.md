# 📖 Mode d'emploi du site — reception-par-type.ch

Ce guide explique **comment utiliser la plateforme** une fois installée et en ligne.
Il s'adresse aux exploitants du site (administrateurs) et décrit aussi le parcours des visiteurs.
Pour l'installation technique, voir `INSTALLATION.md`.

---

## Table des matières

1. [Comprendre la plateforme en 2 minutes](#1-comprendre-la-plateforme)
2. [Le parcours visiteur](#2-le-parcours-visiteur)
3. [Les niveaux d'abonnement](#3-les-niveaux-dabonnement)
4. [Utiliser le back-office (administration)](#4-le-back-office)
5. [Importer les données ASTRA](#5-importer-les-donnees-astra)
6. [Gérer les clients](#6-gerer-les-clients)
7. [Gérer les tarifs](#7-gerer-les-tarifs)
8. [Le programme d'affiliation](#8-affiliation)
9. [La facturation](#9-facturation)
10. [L'API pour les professionnels](#10-lapi)
11. [Questions fréquentes](#11-faq)

---

## 1. Comprendre la plateforme

**reception-par-type.ch** permet de consulter les **données techniques officielles** de tout véhicule homologué en Suisse, à partir de son **numéro de réception par type** (le « TG », en case 24 de la carte grise suisse).

La source est le fichier officiel **ASTRA / TARGA** de l'Office fédéral des routes. Le site est multilingue (français, allemand, italien, anglais) et fonctionne sur un modèle **freemium** : consultation de base gratuite, données avancées sur abonnement.

---

## 2. Le parcours visiteur

1. **Recherche** — Le visiteur saisit un numéro TG (ou une marque/modèle) dans la barre de recherche.
2. **Fiche véhicule** — Il accède à la fiche : les **données publiques** (marque, modèle, motorisation) sont visibles par tous.
3. **Données verrouillées** — Les **données avancées** (masses, charges remorquables, pneumatiques, jantes, fiscalité) sont floutées pour les visiteurs non abonnés, avec une invitation à passer à un forfait supérieur.
4. **Déblocage** — Selon son abonnement, le visiteur débloque la fiche complète (illimité) ou consomme un jeton.

> 🔒 **Sécurité** : les données verrouillées ne sont **jamais** présentes dans le code source de la page (elles sont retirées côté serveur). Impossible de les récupérer en inspectant le HTML — c'est une protection anti-scraping conforme aux recommandations Google.

---

## 3. Les niveaux d'abonnement

La plateforme propose **8 niveaux**, du gratuit à l'entreprise :

| Niveau | Profil type | Accès web | API |
|--------|-------------|-----------|-----|
| 1 | Visiteur gratuit | Données de base | ❌ |
| 2–3 | Particulier / passionné | Jetons à l'unité | ❌ |
| 4 | Usage régulier | Quota mensuel (ex. 500 fiches) | ❌ |
| 5 | Professionnel | Illimité web | ❌ |
| 6 | Business | Illimité web | ✅ 5 000 appels/mois |
| 7 | Business+ | Illimité web | ✅ 15 000 appels/mois |
| 8 | Entreprise / Admin | Illimité | ✅ Illimité |

Les prix et limites de chaque niveau se règlent depuis le back-office (voir §7).

---

## 4. Le back-office

### Accès
Rendez-vous sur **`/admin`** et connectez-vous avec un compte de **niveau 8** (créé pendant l'installation).

### Interface
Le back-office adopte une présentation de type **PrestaShop** : une barre latérale sombre à gauche regroupe les sections, et l'espace principal affiche les contenus en panneaux.

La barre latérale comporte :
- **Tableau de bord** — vue d'ensemble (chiffre d'affaires, clients, imports, sécurité)
- **Clients** — liste et fiches clients
- **Affiliation** — gestion des affiliés et de leurs commissions
- **Tarifs & Forfaits** — édition des 8 plans
- **Données ASTRA** — lancement et suivi des imports

---

## 5. Importer les données ASTRA

C'est l'étape qui alimente le site en véhicules. Sans import, les recherches ne renvoient rien.

1. Allez dans **Données ASTRA → Imports & historique**.
2. Vérifiez l'**état des fichiers sur disque** (le panneau de droite indique si le fichier principal est présent).
3. Déposez le fichier TARGA officiel dans `storage/app/astra/2000/`.
4. Choisissez le type d'import :
   - **Newsletter 5000** — mise à jour hebdomadaire, rapide
   - **Fichier principal 2000** — base mensuelle complète (peut durer 1 à 3 heures)
5. Cliquez sur **Démarrer l'import**.

L'historique en bas de page affiche chaque import avec son statut (terminé, en cours, partiel, échoué), le nombre de lignes insérées/mises à jour, et la durée. Un import échoué peut être **rejoué** d'un clic.

> 💡 Les imports volumineux sont traités par lots en arrière-plan (file d'attente). Assurez-vous qu'un *worker* tourne (`php artisan queue:work` — voir `INSTALLATION.md` §9).

> 🔁 **Idempotence** : réimporter le même fichier ne crée pas de doublons. Le système détecte les fichiers déjà traités via leur empreinte SHA-256.

---

## 6. Gérer les clients

Dans **Clients → Liste des clients** :
- **Filtrez** par nom, e-mail ou niveau d'abonnement.
- **Triez** en cliquant sur les en-têtes de colonnes.
- Cliquez sur **Modifier** pour ouvrir la fiche d'un client.

Sur la **fiche client**, vous disposez de 4 actions (chacune est tracée dans le journal d'audit) :
- **Modifier le niveau** d'abonnement
- **Créditer des jetons** (geste commercial, dédommagement…)
- **Prolonger l'abonnement** (7 / 30 / 90 / 365 jours)
- **Réinitialiser le compteur** mensuel de consultations

---

## 7. Gérer les tarifs

Dans **Tarifs & Forfaits**, chaque plan se déplie pour révéler ses réglages :
- Prix mensuel / annuel / au jeton (saisis en **centimes** : 4900 = 49.00 CHF)
- Limites de consultation web et d'appels API
- Fonctionnalités activables (export PDF, export CSV, accès API, comparateur, simulateur fiscal)
- Visibilité publique et activation du plan

Les modifications sont **immédiates** et le cache se met à jour automatiquement.

> ⚠️ Le niveau 8 (administrateur) ne peut pas être désactivé, par sécurité.

---

## 8. Affiliation

Le programme d'affiliation permet à des partenaires (garages, sites auto…) de toucher une commission.

**Côté partenaire :** depuis son compte, il rejoint le programme (espace *Affilié*), reçoit un **code** et un lien de parrainage. Quand un visiteur arrive via ce lien, un cookie de suivi est posé pour **30 jours** ; toute souscription pendant cette période est créditée au partenaire.

**Côté administration** (menu **Affiliation**) : chaque affilié se déplie pour afficher ses clics, ses gains en attente et approuvés. Vous pouvez :
- **Approuver** les commissions en attente
- **Payer** les commissions approuvées (en saisissant une référence de virement)
- **Suspendre / réactiver** un affilié

> Le taux de commission par défaut est de 10 % (réglable par affilié).

---

## 9. Facturation

À chaque paiement confirmé (via PayPal), le système :
1. Active automatiquement l'abonnement ou crédite les jetons du client.
2. Génère une **facture PDF aux normes suisses**, numérotée séquentiellement (`RPT-2026-00001`).
3. Envoie la facture par e-mail au client.
4. Enregistre la commission d'affiliation si un code de parrainage était présent.

Les clients retrouvent leurs factures dans leur espace personnel (*Mes factures*). La TVA est gérée automatiquement (taux suisse 8,1 % ou mention d'exonération selon votre configuration dans `config/billing.php`).

---

## 10. L'API

Les abonnés **niveau 6 et plus** disposent d'une API REST pour intégrer les données dans leurs propres outils (ERP, configurateur, etc.).

### Créer une clé
Depuis son compte, le client génère une clé API (préfixe `rpt_…`). **La clé complète n'est affichée qu'une seule fois** — elle doit être copiée immédiatement.

### Appeler l'API
Chaque requête envoie la clé dans l'en-tête :
```
Authorization: Bearer rpt_votreCleSecrete
```

Endpoints principaux :
- `GET /api/v1/vehicle/tg/{numero_tg}` — fiche technique
- `GET /api/v1/vehicle/tyres/{numero_tg}` — pneus + équivalences légales (±8 %)
- `GET /api/v1/vehicle/wheels/{numero_tg}` — jantes + compatibilités

### Limites
L'API applique des quotas par seconde, par minute et par mois selon le forfait. En cas de dépassement, elle renvoie une erreur **429** avec un en-tête `Retry-After` indiquant le délai d'attente.

---

## 11. FAQ

**Le visiteur ne trouve aucun véhicule.**
→ Vérifiez qu'un import ASTRA a bien été effectué (§5) et qu'il s'est terminé avec succès.

**Un client a payé mais n'a pas son abonnement.**
→ Vérifiez que le *worker* de file d'attente tourne et que le webhook PayPal est bien configuré (`PAYPAL_WEBHOOK_ID` dans `.env`). Le tableau de bord → Sécurité signale les anomalies.

**Comment changer la langue du site ?**
→ La langue est dans l'URL (`/fr/`, `/de/`, `/it/`, `/en/`). Le sélecteur de langue est proposé aux visiteurs ; les balises hreflang sont générées automatiquement pour le référencement.

**Les données avancées fuient-elles dans le code source ?**
→ Non. Elles sont retirées côté serveur avant l'envoi de la page. Seuls les abonnés autorisés les reçoivent.

**Comment passer le site en production ?**
→ Dans `.env` : `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, et configurez PayPal + SMTP. Voir `INSTALLATION.md` §Sécurité.

---

*Pour toute question technique (déploiement, configuration serveur, scheduler), reportez-vous au `README.md` et à `INSTALLATION.md`.*
