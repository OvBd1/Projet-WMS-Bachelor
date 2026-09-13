# LogiTrack — Gestion d'entrepôt (WMS)

[![CI](https://github.com/OvBd1/Projet-WMS-Bachelor/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/OvBd1/Projet-WMS-Bachelor/actions/workflows/ci.yml)

LogiTrack est une application web de gestion d'entrepôt : référentiel d'articles et d'emplacements,
réceptions de marchandises, commandes, transferts entre emplacements et consultation des stocks,
pour plusieurs sociétés (« dossiers ») cloisonnées dans une même installation.

Projet fil rouge du titre professionnel **Concepteur Développeur d'Applications** (RNCP 37873),
réalisé de janvier à juin 2026 en six jalons.

| Document | Public |
|---|---|
| [DEPLOYMENT.md](DEPLOYMENT.md) | installation, configuration, mise en production, exploitation |
| [GUIDE_UTILISATEUR.md](GUIDE_UTILISATEUR.md) | opérateurs d'entrepôt et administrateurs |
| [CLAUDE.md](CLAUDE.md) | conventions de développement du dépôt |

---

## Fonctionnalités

- **Référentiels** : articles (DLC, numéro de série, type de conditionnement, image), emplacements et types, tiers (fournisseurs, clients) avec autocomplétion d'adresse.
- **Réceptions** : en attente → validée (entrée en stock) → annulée (sortie du stock), DLC et numéros de série par ligne.
- **Commandes** : numéro automatique (`CMD-00001`), date d'expédition, statuts EN_ATTENTE / PREPAREE / EXPEDIEE / ANNULEE.
- **Stocks et transferts** : consultation filtrable ; transfert d'un emplacement à un autre, refusé si le stock est insuffisant.
- **Multi-dossiers** : chaque donnée métier appartient à un dossier ; un utilisateur ne voit que le sien, un administrateur choisit le dossier actif.
- **Administration** : dossiers et comptes utilisateurs, réservés aux administrateurs (pas d'inscription publique).

## Pile technique

| Couche | Technologie |
|---|---|
| Front-end | Angular 21 (composants autonomes, signaux, formulaires réactifs), TypeScript |
| Back-end | Symfony 7.4 (LTS) sur PHP 8.3 — API REST |
| Données | MySQL 8, Doctrine ORM 3, 13 entités, migrations versionnées |
| Authentification | JWT RS256 (LexikJWTAuthenticationBundle), mots de passe hachés en Argon2id |
| Exécution | Docker Compose — `db` (MySQL), `php` (PHP-FPM), `web` (nginx) ; un seul port publié : **8080** |
| Tests | PHPUnit 11 (back-end), Vitest + jsdom (front-end) |
| CI/CD | GitHub Actions : `ci.yml` à chaque poussée, `release.yml` sur tag `v*.*.*` → GitHub Container Registry |
| API tierce | Base Adresse Nationale (`api-adresse.data.gouv.fr`) |

## Démarrage rapide

Prérequis : Docker (Docker Desktop sous Windows). PHP, Composer, Node et MySQL locaux ne sont pas nécessaires.

```bash
cp .env.example .env          # puis renseigner APP_SECRET, JWT_PASSPHRASE et les mots de passe MySQL
docker compose up --build     # mode développement (rechargement à chaud)
```

Au premier démarrage, le conteneur `php` attend MySQL, génère les clés JWT, applique les migrations et vide le cache.
Il n'existe aucun compte : créer le premier administrateur (mot de passe demandé en saisie masquée) :

```bash
docker compose exec php php bin/console app:create-admin admin@exemple.fr Nom Prénom
```

Application : **http://localhost:8080**. Mode production, images publiées, sauvegardes : voir [DEPLOYMENT.md](DEPLOYMENT.md).

## Architecture

```
navigateur ──► web (nginx :8080) ── /        → application Angular (build statique)
                     │             ── /api     → php (PHP-FPM, Symfony) ──► db (MySQL 8)
                     │             ── /uploads → volume des images d'articles
                     └─ l'API appelle la Base Adresse Nationale (serveur à serveur)
```

```
backend/
├── src/Controller/   routes API : hydrate le DTO, valide, appelle le service, renvoie du JSON
├── src/DTO/          objets de transfert avec contraintes de validation
├── src/Entity/       13 entités Doctrine (attributs PHP 8)
├── src/Service/      logique métier et sérialisation (normalize())
├── src/Doctrine/     DossierFilter (cloisonnement SQL)
├── src/Traits/       DossierScopedTrait, AuditTrait
├── migrations/       migrations Doctrine versionnées
└── tests/            Unit/ (sans base) et Functional/ (HTTP + base de test)
frontend/src/app/
├── core/             services, intercepteurs (JWT, dossier, erreurs), gardes, modèles
├── shared/           modale de confirmation, icônes, autocomplétion d'adresse
├── features/         un dossier par écran, chargé à la demande
└── layouts/          mise en page principale (navigation, sélecteur de dossier)
```

### Décisions structurantes

- **Le stock est une conséquence, jamais une saisie.** Tout mouvement (réception, transfert) passe par un point unique, `StockService::adjust(Article, Emplacement, int $delta)`, qui **vérifie avant d'écrire** (aucune quantité négative) et **ne valide pas la transaction** : l'appelant compose plusieurs mouvements (sortie + entrée d'un transfert) dans un seul `flush()`. La page *Stocks* est en lecture seule.
- **Contrainte en base** : `UNIQUE (article_id, emplacement_id)` sur `stock` ; la référence article est unique **par dossier**.
- **Multi-dossiers au niveau de la persistance** : `DossierContext` résout le dossier de la requête et active `DossierFilter`, qui ajoute `dossier_id = ?` à toute requête sur une entité utilisant `DossierScopedTrait`. Ni les contrôleurs ni l'interface ne filtrent.
- **Sérialisation écrite à la main** (`normalize()` champ par champ) : un mot de passe ne peut pas fuiter par oubli d'un groupe de sérialisation.
- **API tierce derrière le pare-feu** : `GET /api/adresses/search` relaie la Base Adresse Nationale (`AdresseApiService`, `HttpClientInterface`), configurée par `ADRESSE_API_BASE_URL` et `ADRESSE_API_TIMEOUT`. En cas de panne : liste vide, saisie manuelle possible.

## Tests

| Niveau | Tests | Outil |
|---|---|---|
| Unitaires back-end | 51 | PHPUnit, doublures, sans base ni réseau |
| Fonctionnels back-end | 33 | PHPUnit, requêtes HTTP réelles à travers le noyau, base de test reconstruite |
| Front-end | 43 | Vitest (services, intercepteurs, gardes, composants) |

```bash
# Back-end, dans le conteneur (mode développement)
docker compose exec php vendor/bin/phpunit --testsuite unit
docker compose exec php php bin/console doctrine:database:create --env=test --if-not-exists
docker compose exec php vendor/bin/phpunit

# Front-end
cd frontend && npm ci && npx ng test --watch=false
```

Aucun test n'appelle de service externe : l'API adresse est remplacée par `MockHttpClient` (unitaires)
et pointe vers une adresse injoignable en environnement de test (fonctionnels).

## Intégration et livraison continues

- **`ci.yml`** (chaque poussée, chaque pull request vers `main`/`develop`) : validation Composer, conteneur de services, **migrations sur base vierge et schéma identique au mapping**, tests unitaires et fonctionnels (MySQL 8 en service), tests et build Angular, construction des images Docker.
- **`release.yml`** (tag `v*.*.*`) : publication des images `logitrack-backend` et `logitrack-frontend` sur GitHub Container Registry, puis release GitHub.

## Sécurité

- Authentification sans état par JWT RS256 ; mots de passe Argon2id ; message d'erreur de connexion neutre.
- Autorisations côté serveur : `ROLE_ADMIN` exigé pour les dossiers et les comptes ; cloisonnement des données par dossier.
- Aucune inscription publique : premier administrateur en ligne de commande, comptes suivants créés par un administrateur.
- **Limites connues** : pas de limitation des tentatives de connexion côté application (à placer sur le reverse-proxy, voir [DEPLOYMENT.md](DEPLOYMENT.md)), pas de journal de sécurité dédié, pas de tests de bout en bout du navigateur.

## Conventions Git

- `main` : versions livrées ; `develop` : intégration ; une branche par sujet (`feat/…`, `fix/…`, `test/…`, `ci/…`, `docs/…`, `chore/…`, `refactor/…`).
- Messages de commit préfixés : `feat`, `fix`, `docs`, `chore`, `test`, `refactor`, `ci`.
- Intégration par pull request, après passage de l'intégration continue.
