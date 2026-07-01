# LogiTrack — Déploiement Docker

Toute l'application (frontend Angular, API Symfony, MySQL) tourne en conteneurs.
Un seul point d'entrée : **http://localhost:8080**.

## Architecture

```
                    ┌──────────────────────────────┐
  navigateur  ─────►│  web  (nginx :8080)          │
                    │   /          → Angular        │
                    │   /api       → PHP-FPM        │
                    │   /uploads   → volume images  │
                    └───────┬──────────────┬────────┘
                            │ fastcgi       │ volume
                    ┌───────▼──────┐  ┌─────▼──────┐
                    │ php (FPM)    │  │  uploads   │
                    │ Symfony 7.4  │  └────────────┘
                    └───────┬──────┘
                            │
                    ┌───────▼──────┐
                    │ db (MySQL 8) │  volume db_data
                    └──────────────┘
```

| Service | Rôle | Image |
|---------|------|-------|
| `web`   | nginx : sert Angular + reverse-proxy API/uploads | build `frontend/` |
| `php`   | PHP-FPM 8.3 + Symfony | build `backend/` |
| `db`    | MySQL 8 | `mysql:8.0` |

Volumes persistants : `db_data` (base), `uploads` (images articles), `jwt_keys` (clés JWT).

## Prérequis

- Docker Desktop (Windows) démarré.
- Rien d'autre : PHP, Composer, Node, MySQL locaux ne sont plus nécessaires.

## Configuration

Les variables sont dans `.env` (à la racine, déjà créé — non versionné).
Modèle versionné : `.env.example`. Adapter si besoin les mots de passe MySQL,
le port `HTTP_PORT`, etc.

## Démarrage — mode DEV (hot-reload) — par défaut

`docker compose` charge automatiquement `docker-compose.override.yml`.

```powershell
docker compose up --build
```

- Frontend : `ng serve` avec HMR → modifier un fichier Angular recharge le navigateur.
- Backend : code monté en volume → modifier un `.php` est pris en compte immédiatement.
- Accès : http://localhost:8080

## Démarrage — mode PROD-like

Images auto-suffisantes (Angular buildé statique, PHP optimisé), sans l'override :

```powershell
docker compose -f docker-compose.yml up --build -d
```

## Ce qui se passe au premier démarrage

L'`entrypoint` du conteneur `php` :
1. attend que MySQL soit prêt,
2. génère les clés JWT (`config/jwt/`) si absentes,
3. applique les migrations Doctrine,
4. vide/réchauffe le cache.

> Il n'y a donc **pas** de commande manuelle à lancer. La base est créée et
> migrée automatiquement.

## Commandes utiles

```powershell
# Logs
docker compose logs -f php
docker compose logs -f web

# Console Symfony dans le conteneur
docker compose exec php php bin/console debug:router
docker compose exec php php bin/console doctrine:migrations:migrate

# Nouvelle migration après modif d'entité
docker compose exec php php bin/console doctrine:migrations:diff

# Accès MySQL
docker compose exec db mysql -uwms -pwms wms

# Arrêt
docker compose down

# Arrêt + suppression des données (reset complet)
docker compose down -v
```

## Connexion / compte admin

Un utilisateur se crée via `POST /api/auth/register`
(`email`, `password` min. 6 caractères, `role` = `ROLE_USER` ou `ROLE_ADMIN`) :

```powershell
curl -X POST http://localhost:8080/api/auth/register `
  -H "Content-Type: application/json" `
  --% -d "{\"email\":\"admin@logitrack.test\",\"password\":\"admin1234\",\"role\":\"ROLE_ADMIN\"}"
```

Le login (`POST /api/auth/login`, champs `email` + `password`) renvoie
`{ "token": "..." }` (JWT).

### Compte admin déjà créé

| Champ | Valeur |
|-------|--------|
| Email | `admin@logitrack.test` |
| Mot de passe | `admin1234` |
| Rôle | `ROLE_ADMIN` |

> Ce compte est stocké dans le volume `db_data`. Il persiste tant que tu ne fais
> pas `docker compose down -v`. Après un reset (`down -v`), relance la commande
> `register` ci-dessus pour le recréer.

## Dépannage

| Symptôme | Solution |
|----------|----------|
| Port 8080 occupé | Changer `HTTP_PORT` dans `.env` |
| `web` répond 502 sur /api | Le conteneur `php` n'est pas prêt : `docker compose logs php` |
| Modif Angular non prise en compte (Windows) | Le `--poll` est activé ; sinon `docker compose restart frontend` |
| Reset base | `docker compose down -v` puis `up --build` |
| Rebuild après changement de dépendances | `docker compose build --no-cache` |
