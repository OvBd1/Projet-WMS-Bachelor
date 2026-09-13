# LogiTrack — Warehouse Management System

Stack : Symfony 7.4 (API REST, PHP 8.3) + Angular 21 (SPA) + MySQL 8, exécutés avec Docker Compose.
Documentation : [README.md](README.md), [DEPLOYMENT.md](DEPLOYMENT.md), [GUIDE_UTILISATEUR.md](GUIDE_UTILISATEUR.md).

## Règles de travail (demandées par l'utilisateur)

- **Ne jamais pousser (`git push`) ni commiter sans feu vert explicite.**
- **Une branche dédiée par sujet** (`feat/…`, `fix/…`, `test/…`, `ci/…`, `docs/…`, `chore/…`, `refactor/…`), partant de `main` à jour ; ne pas empiler de sujets non liés.
- Messages de commit préfixés : `feat`, `fix`, `docs`, `chore`, `test`, `refactor`, `ci`.
- **Aucune mention de Claude** : pas de trailer `Co-Authored-By: Claude …` dans les commits, pas de ligne « Generated with Claude Code » dans les pull requests.
- Modèle GitFlow : `main` pour les versions livrées, `develop` pour l'intégration.

## Environnement

```bash
cp .env.example .env
docker compose up --build            # dev : code monté, ng serve, http://localhost:8080
docker compose -f docker-compose.yml up --build -d   # production (sans l'override)
docker compose exec php php bin/console app:create-admin <email> <nom> <prénom>
```

## Backend (`backend/`)

### Commandes

```bash
docker compose exec php php bin/console doctrine:migrations:diff
docker compose exec php php bin/console doctrine:migrations:migrate
docker compose exec php php bin/console debug:router
docker compose exec php vendor/bin/phpunit --testsuite unit        # sans base
docker compose exec php php bin/console doctrine:database:create --env=test --if-not-exists
docker compose exec php vendor/bin/phpunit                         # unit + functional
```

### Architecture

```
src/
├── Command/       app:create-admin (seul moyen de créer le premier administrateur)
├── Controller/    routes API — hydrate le DTO, valide, appelle le service, retourne json()
├── DTO/           objets de transfert, propriétés publiques avec #[Assert\...]
├── Doctrine/      DossierFilter (SQLFilter de cloisonnement)
├── Entity/        13 entités (attributs PHP 8)
├── EventListener/ DossierFilterActivator (active le filtre), JwtCreatedListener (id, nom, prénom dans le JWT)
├── Repository/
├── Service/       logique métier + normalize() ; DossierContext ; AdresseApiService
└── Traits/        DossierScopedTrait (dossier_id), AuditTrait (createdAt/By, updatedAt/By)
tests/
├── Unit/          doublures, MockHttpClient, sans base
├── Functional/    ApiTestCase : requêtes HTTP à travers le noyau, base de test reconstruite
└── Support/
```

Entités : Utilisateur, Dossier, Article, TypeConditionnement, Emplacement, TypeEmplacement, Stock, Tiers,
Reception, LigneReception, Commande, LigneCommande, TransfertEmplacement.

### Règles métier à respecter

- **Le stock est une conséquence, jamais une saisie.** Tout mouvement passe par
  `StockService::adjust(Article $article, Emplacement $emplacement, int $delta): Stock`, qui vérifie avant d'écrire
  (DomainException si la quantité deviendrait négative, aucun stock créé) et **ne fait pas le flush**.
  Toute nouvelle opération de stock doit passer par `adjust()` ; ne jamais réécrire la règle ailleurs.
  Il n'existe aucune route d'écriture directe sur `/api/stocks`.
- Réception : EN_ATTENTE (aucun effet) → VALIDEE (`adjust(+q)` par ligne) → ANNULEE (`adjust(-q)` si elle était validée).
- Transfert : `adjust(-q)` sur la source puis `adjust(+q)` sur la destination, un seul flush.
- `UNIQUE (article_id, emplacement_id)` sur `stock` ; référence article et code emplacement uniques **par dossier**.
- **Multi-dossiers** : cloisonnement au niveau de la persistance uniquement. Une entité cloisonnée utilise `DossierScopedTrait`
  et reçoit son dossier via `DossierContext::getCurrentOrThrow()` à la création. Utilisateur standard → son dossier ;
  administrateur → en-tête `X-Dossier-Id`.
- Création de comptes réservée à `ROLE_ADMIN` (`POST /api/utilisateurs`) ; pas de route d'inscription publique.
- Commande : numéro `CMD-%05d` attribué après le premier flush ; modifiable seulement EN_ATTENTE.

### Conventions

- Sérialisation : `normalize()` écrit à la main dans chaque service (pas de Serializer ni de groupes).
  Collections : `array_values($collection->toArray())` pour toujours produire un tableau JSON.
- Erreurs métier : `throw new \DomainException(...)` → le contrôleur renvoie 400 ou 409.
- Erreurs de validation : 422 avec `['errors' => [champ => message]]`.
- `/api/auth/login` géré par Lexik JWT (`json_login`) — pas de contrôleur. JWT RS256, clés dans `config/jwt/`.
- Mots de passe : `algorithm: argon2id`.
- Utiliser `\count()` et `\in_array()` (espace de noms global).
- API adresse : `ADRESSE_API_BASE_URL`, `ADRESSE_API_TIMEOUT` ; toute erreur → liste vide (dégradation silencieuse).

### Migrations

- **Toujours vérifier une migration sur une base vierge** (`doctrine:migrations:migrate` puis `doctrine:schema:validate`),
  ce que fait la CI. Un `doctrine:migrations:diff` contre une base locale désynchronisée produit des instructions déjà écrites.
- Ne jamais modifier une migration déjà exécutée ailleurs : la neutraliser ou en ajouter une nouvelle.

### Tests

- Piège connu : dans `tests/Functional/ApiTestCase.php`, le noyau est **redémarré avant chaque requête et chaque préparation
  de données** (`self::ensureKernelShutdown()` puis `createClient()` / `bootKernel()`). Sinon `DossierContext` conserve
  le dossier d'une requête précédente et Doctrine lève « a new entity was found through the relationship Stock#dossier ».
- Aucun appel externe en test : `MockHttpClient` en unitaire, `ADRESSE_API_BASE_URL` injoignable dans `.env.test`.

## Frontend (`frontend/`)

### Commandes

```bash
cd frontend
npm ci
npx ng test --watch=false   # Vitest
npx ng build
```

### Architecture

```
src/app/
├── core/
│   ├── guards/        auth, guest, admin, dossier (sélection du premier dossier pour un admin)
│   ├── interceptors/  jwt (Bearer), dossier (X-Dossier-Id pour un admin), error (logout sur 401, accueil sur 403)
│   ├── models/
│   └── services/      auth (signal isAuthenticated, localStorage `jwt_token`), confirm, adresse, dossier-context, …
├── shared/            confirm-dialog, icon (app-icon), adresse-autocomplete
├── features/          un dossier par écran, chargement à la demande
└── layouts/main-layout/  navigation, sélecteur de dossier, hôte unique de la modale de confirmation
```

### Conventions

- Composants autonomes, signaux, `ReactiveFormsModule`, chargement à la demande.
- `environment.apiUrl = '/api'` (même origine, relayé par nginx en dev comme en prod).
- Confirmations : `await this.confirm.ask({ title, message, confirmLabel, variant })` (jamais `window.confirm`).
- Abandon de saisie : méthode `cancelForm()` → `confirm.confirmDiscard(form)` ; `closeForm()` reste appelé après enregistrement.
- Boutons d'action de tableau : `btn btn-… btn-icon` + `<app-icon name="edit|trash|check|close|…" />`, avec `title` et `aria-label`.
- Images d'articles : envoi uniquement en mode édition (l'identifiant est requis), JPEG/PNG/WebP/GIF, 5 Mo maximum.

## Flux d'authentification

1. `POST /api/auth/login` → `{ token }` ; jeton stocké dans `localStorage`.
2. L'intercepteur JWT l'ajoute à chaque requête ; pour un administrateur, l'intercepteur dossier ajoute `X-Dossier-Id`.
3. 401 → déconnexion et retour à `/auth/login`.
4. `authGuard` protège l'application, `guestGuard` la page de connexion, `adminGuard` les écrans d'administration.
