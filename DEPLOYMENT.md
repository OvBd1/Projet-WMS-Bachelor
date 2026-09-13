# LogiTrack — Déploiement et exploitation

Ce document décrit l'installation de LogiTrack avec Docker Compose, sa configuration, sa mise en production
et son exploitation courante. Toutes les commandes sont lancées à la racine du dépôt.

## Sommaire

1. [Architecture](#1-architecture)
2. [Prérequis](#2-prérequis)
3. [Configuration](#3-configuration)
4. [Démarrage](#4-démarrage)
5. [Premier administrateur](#5-premier-administrateur)
6. [Mise en production](#6-mise-en-production)
7. [Mise à jour et retour arrière](#7-mise-à-jour-et-retour-arrière)
8. [Sauvegarde et restauration](#8-sauvegarde-et-restauration)
9. [Développement et tests](#9-développement-et-tests)
10. [Commandes utiles](#10-commandes-utiles)
11. [Dépannage](#11-dépannage)

---

## 1. Architecture

```
                    ┌──────────────────────────────┐
  navigateur  ─────►│  web  (nginx, port 8080)     │
                    │   /          → Angular        │
                    │   /api       → PHP-FPM        │
                    │   /uploads   → volume images  │
                    └───────┬──────────────┬────────┘
                            │ FastCGI       │ lecture seule
                    ┌───────▼──────┐  ┌─────▼──────┐
                    │ php (FPM)    │  │  uploads   │
                    │ Symfony 7.4  │  └────────────┘
                    └───────┬──────┘
                            │                ┌──────────────────────────────┐
                    ┌───────▼──────┐          │ Base Adresse Nationale       │
                    │ db (MySQL 8) │   php ──►│ api-adresse.data.gouv.fr     │
                    └──────────────┘          └──────────────────────────────┘
```

| Service | Rôle | Image |
|---|---|---|
| `web` | nginx : sert le build Angular, relaie `/api` vers PHP-FPM, sert les images | construite depuis `frontend/` (cible `runtime`) |
| `php` | PHP-FPM 8.3 + Symfony, point d'entrée idempotent | construite depuis `backend/` (cible `app`) |
| `db` | MySQL 8 | `mysql:8.0` |

Un seul port est publié : `HTTP_PORT` (8080 par défaut). La base et PHP-FPM ne sont pas exposés.

| Volume | Contenu |
|---|---|
| `db_data` | données MySQL |
| `uploads` | images des articles |
| `jwt_keys` | paire de clés JWT |

## 2. Prérequis

- Docker Engine 24+ et le plugin Compose v2 (Docker Desktop sous Windows / macOS).
- Serveur : 2 vCPU, 2 Go de RAM, 10 Go de disque constituent un minimum raisonnable.
- Accès sortant HTTPS vers `api-adresse.data.gouv.fr` pour l'autocomplétion d'adresse (facultatif : sans lui, la saisie reste manuelle).

## 3. Configuration

```bash
cp .env.example .env
```

`.env` n'est **pas versionné** ; `.env.example` est le modèle. Variables lues par Docker Compose :

| Variable | Rôle | Valeur par défaut / consigne |
|---|---|---|
| `HTTP_PORT` | port publié de l'application | `8080` |
| `APP_ENV`, `APP_DEBUG` | environnement Symfony | `prod`, `0` |
| `APP_SECRET` | secret Symfony | **à générer** : `php -r "echo bin2hex(random_bytes(16));"` ou `openssl rand -hex 16` |
| `MYSQL_ROOT_PASSWORD` | mot de passe root MySQL | **à changer** |
| `MYSQL_DATABASE` | base applicative | `wms` |
| `MYSQL_USER`, `MYSQL_PASSWORD` | compte applicatif MySQL | **mot de passe à changer** |
| `CORS_ALLOW_ORIGIN` | origines autorisées (expression régulière) | `localhost` ; en production, le domaine public |
| `JWT_PASSPHRASE` | phrase de passe des clés JWT | **à générer** : `openssl rand -hex 32` |
| `ADRESSE_API_BASE_URL` | URL de la Base Adresse Nationale | `https://api-adresse.data.gouv.fr` |
| `ADRESSE_API_TIMEOUT` | délai maximal d'appel (secondes) | `3` |

> Changer `JWT_PASSPHRASE` après le premier démarrage rend les clés existantes inutilisables :
> supprimer alors le volume `jwt_keys` pour qu'elles soient régénérées (les utilisateurs devront se reconnecter).

## 4. Démarrage

### Mode production (images autonomes)

```bash
docker compose -f docker-compose.yml up --build -d
```

L'option `-f docker-compose.yml` ignore `docker-compose.override.yml` (mode développement).

### Ce que fait le point d'entrée du conteneur `php`

Le script `backend/docker/entrypoint.sh` est **idempotent** : il peut être relancé à chaque démarrage.

1. installe les dépendances Composer si elles manquent ;
2. attend que MySQL réponde ;
3. génère la paire de clés JWT si elle n'existe pas (volume `jwt_keys`) ;
4. applique les migrations Doctrine en attente ;
5. vide et réchauffe le cache, puis démarre PHP-FPM.

Aucune commande manuelle n'est nécessaire pour créer ou migrer la base.

### Images publiées (GitHub Container Registry)

Chaque tag `vX.Y.Z` publie `ghcr.io/ovbd1/logitrack-backend` et `ghcr.io/ovbd1/logitrack-frontend`
(étiquettes `X.Y.Z`, `X.Y` et `latest`). Pour déployer sans construire, créer un fichier
`docker-compose.images.yml` à côté de `docker-compose.yml` :

```yaml
services:
  php:
    image: ghcr.io/ovbd1/logitrack-backend:1.0.0
  web:
    image: ghcr.io/ovbd1/logitrack-frontend:1.0.0
```

```bash
docker compose -f docker-compose.yml -f docker-compose.images.yml pull php web
docker compose -f docker-compose.yml -f docker-compose.images.yml up -d --no-build
```

> Un paquet GHCR nouvellement publié est privé : le rendre public dans les paramètres du paquet
> ou s'authentifier avec `docker login ghcr.io` (jeton avec la portée `read:packages`).

## 5. Premier administrateur

Il n'existe **aucune inscription publique**. Sur une installation neuve, créer le premier administrateur :

```bash
docker compose exec php php bin/console app:create-admin admin@exemple.fr Nom Prénom
```

Le mot de passe (6 caractères minimum) est demandé en saisie masquée ; il peut aussi être passé
avec `--password=…` pour une installation automatisée. Les comptes suivants sont créés par un administrateur
depuis la page *Utilisateurs* ; un utilisateur non administrateur doit être rattaché à un dossier.

## 6. Mise en production

- **HTTPS obligatoire** : placer un reverse-proxy (nginx, Traefik, Caddy…) devant le port `HTTP_PORT`,
  avec un certificat TLS. Les jetons JWT ne doivent jamais circuler en clair.
- **Limiter les tentatives de connexion** : l'application ne limite pas le débit de `POST /api/auth/login`
  (limite connue). Le faire sur le reverse-proxy, par exemple avec nginx :

  ```nginx
  limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;

  location = /api/auth/login {
      limit_req zone=login burst=5 nodelay;
      proxy_pass http://127.0.0.1:8080;
  }
  ```

- **Secrets** : `APP_SECRET`, `JWT_PASSPHRASE` et les mots de passe MySQL propres à chaque environnement,
  jamais versionnés. Restreindre les droits du fichier `.env` (`chmod 600 .env`).
- **CORS** : `CORS_ALLOW_ORIGIN` limité au domaine public de l'application.
- **Sauvegardes** planifiées de la base et du volume `uploads` (voir § 8).
- **Mises à jour** régulières des images de base (`docker compose build --pull`).

## 7. Mise à jour et retour arrière

```bash
git fetch --tags
git checkout v1.1.0                                  # version cible
docker compose -f docker-compose.yml up --build -d   # les migrations sont appliquées au démarrage
```

**Sauvegarder la base avant toute mise à jour** (§ 8). Retour arrière :

1. revenir au tag précédent (`git checkout v1.0.0`) ou à l'image précédente ;
2. si la nouvelle version a appliqué des migrations, restaurer la sauvegarde prise avant la mise à jour
   (préférable à `doctrine:migrations:migrate prev`, qui ne restaure pas les données) ;
3. `docker compose -f docker-compose.yml up --build -d`.

## 8. Sauvegarde et restauration

```bash
# Sauvegarde de la base
docker compose exec -T db sh -c 'exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction "$MYSQL_DATABASE"' > sauvegarde-$(date +%F).sql

# Restauration
docker compose exec -T db sh -c 'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < sauvegarde-2026-09-13.sql

# Sauvegarde des images d'articles
docker run --rm -v projet-wms-bachelor_uploads:/data -v "$PWD":/backup alpine tar czf /backup/uploads.tar.gz -C /data .
```

Le nom du volume dépend du nom du projet Compose (`docker volume ls`).

## 9. Développement et tests

### Mode développement

```bash
docker compose up --build
```

`docker-compose.override.yml` est chargé automatiquement :

- le code `backend/` est monté dans le conteneur (modifications prises en compte immédiatement), `APP_ENV=dev` ;
- un conteneur Node exécute `ng serve` avec rechargement à chaud, relayé par nginx ;
- l'application reste accessible sur http://localhost:8080.

### Tests back-end

La base de test `<MYSQL_DATABASE>_test` est créée, avec les droits du compte applicatif, par
`docker/mysql/initdb/01-base-de-test.sh` **à la création du volume `db_data`**.

```bash
docker compose exec php vendor/bin/phpunit --testsuite unit          # 51 tests, sans base
docker compose exec php php bin/console doctrine:database:create --env=test --if-not-exists
docker compose exec php vendor/bin/phpunit                           # unitaires + fonctionnels
```

Sur un volume `db_data` créé avant l'ajout de ce script, appliquer les droits une fois :

```bash
docker compose exec -T db sh < docker/mysql/initdb/01-base-de-test.sh
```

Les tests fonctionnels redémarrent le noyau Symfony avant chaque requête (voir `tests/Functional/ApiTestCase.php`)
et n'appellent aucun service externe.

### Tests front-end

```bash
cd frontend
npm ci
npx ng test --watch=false
```

### Intégration continue

`.github/workflows/ci.yml` rejoue ces vérifications à chaque poussée, dont les **migrations sur base vierge**
suivies de `doctrine:schema:validate` : une migration générée contre une base locale désynchronisée y est détectée.

## 10. Commandes utiles

```bash
docker compose logs -f php                                      # journaux
docker compose exec php php bin/console debug:router            # routes
docker compose exec php php bin/console doctrine:migrations:status
docker compose exec php php bin/console doctrine:migrations:diff  # après modification d'entité (mode dev)
docker compose exec db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"'
docker compose down                                             # arrêt (données conservées)
docker compose down -v                                          # arrêt et suppression des données
```

> Toujours vérifier une nouvelle migration sur une base vierge : générée contre une base locale désynchronisée,
> elle peut contenir des instructions déjà exécutées par une migration précédente.

## 11. Dépannage

| Symptôme | Cause probable | Solution |
|---|---|---|
| Port 8080 déjà utilisé | autre service sur le port | changer `HTTP_PORT` dans `.env` |
| `502 Bad Gateway` sur `/api` | `php` pas encore prêt (migrations) | `docker compose logs php`, attendre « ready to handle connections » |
| Connexion impossible pour tous les comptes | clés JWT incohérentes avec `JWT_PASSPHRASE` | supprimer le volume `jwt_keys`, redémarrer |
| Aucune suggestion d'adresse | pas d'accès sortant ou service indisponible | vérifier l'accès à `ADRESSE_API_BASE_URL` ; la saisie manuelle reste possible |
| `Access denied … <base>_test` pendant les tests | volume `db_data` antérieur au script de droits | appliquer le script (§ 9) |
| Échec d'une migration au démarrage | base modifiée hors migrations | restaurer la sauvegarde, corriger la migration, la valider sur base vierge |
| Réinitialiser complètement | — | `docker compose down -v` puis `docker compose up --build` |
