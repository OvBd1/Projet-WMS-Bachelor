# LogiTrack — Warehouse Management System

Stack : Symfony 7.4 (API REST) + Angular 21 (SPA) + MySQL 8.

## Environnement

- PHP : `C:\PHP\php.exe` (prioritaire sur C:\xampp\php)
- Chaque commande PowerShell doit commencer par :
  ```powershell
  $env:PATH = [System.Environment]::GetEnvironmentVariable("PATH","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("PATH","User")
  $env:OPENSSL_CONF = "C:\PHP\extras\ssl\openssl.cnf"
  ```
- MySQL : `root:root@127.0.0.1:3306/wms`

## Backend (`backend/`)

### Commandes fréquentes

```powershell
# Démarrer le serveur
php -S localhost:8000 -t public

# Migrations
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate

# Cache
php bin/console cache:clear

# Routes
php bin/console debug:router
```

### Architecture

```
src/
├── Controller/   # Routes API — hydrate DTO, valide, appelle service, retourne json()
├── DTO/          # Objets de transfert avec contraintes Assert
├── Entity/       # 11 entités Doctrine (mapping PHP 8 attributes)
├── Repository/   # Requêtes Doctrine
└── Service/      # Logique métier, méthode normalize() pour la sérialisation
```

### Entités

| Entité | Clé |
|--------|-----|
| Utilisateur | email unique, role string, UserInterface |
| Article | reference unique (50), libelle, description, gestionDlc (bool), gestionNumeroSerie (bool), typeConditionnement (ManyToOne nullable), imagePath (VARCHAR 255 nullable) |
| TypeConditionnement | libelle (VARCHAR 100), OneToMany Article |
| Stock | quantite int, ManyToOne Article + Emplacement, UniqueConstraint(article_id, emplacement_id) |
| TypeEmplacement | libelle |
| Emplacement | code unique (50), ManyToOne TypeEmplacement |
| Reception | dateReception, ManyToOne Utilisateur, OneToMany LigneReception (cascade+orphanRemoval) |
| LigneReception | quantite, ManyToOne Reception+Article+Emplacement |
| Commande | dateCommande, statut (EN_ATTENTE/PREPAREE/EXPEDIEE/ANNULEE), OneToMany LigneCommande |
| LigneCommande | quantite, ManyToOne Commande+Article |
| TransfertEmplacement | dateTransfert, quantite, ManyToOne Utilisateur+Article+EmplacementSource+EmplacementDestination |

### Conventions

- DTOs : propriétés publiques avec `#[Assert\...]`
- Sérialisation : méthode `normalize()` dans chaque service (pas de Symfony Serializer, pas de groups)
- Erreurs métier : `throw new \DomainException(...)` → contrôleur retourne 400/409
- Erreurs de validation : 422 avec `['errors' => [field => message]]`
- `/api/auth/login` géré par Lexik JWT (json_login) — ne pas créer de contrôleur pour ça
- JWT : RS256, clés dans `config/jwt/`
- Utiliser `\count()` et `\in_array()` (namespace global) dans les contrôleurs

### Logique métier

- `POST /api/receptions` → incrémente `Stock(article, emplacement)` pour chaque ligne
- `POST /api/transferts` → décrémente stock source, incrémente stock destination (erreur si insuffisant)
- `StockService::adjust(Article, Emplacement, int $delta)` : trouve ou crée le Stock, applique le delta
- `POST /api/articles/{id}/image` → upload multipart, MIME jpeg/png/webp/gif, max 5 Mo, stocké dans `public/uploads/articles/`, chemin `/uploads/articles/filename.ext` en DB

## Frontend (`frontend/`)

### Commandes fréquentes

```powershell
cd frontend
ng serve          # dev server sur http://localhost:4200
ng build          # build prod
```

### Architecture

```
src/app/
├── core/
│   ├── guards/         auth.guard.ts, guest.guard.ts
│   ├── interceptors/   jwt.interceptor.ts (Bearer token), error.interceptor.ts (logout sur 401)
│   ├── models/         user.model.ts, article.model.ts, type-conditionnement.model.ts, ...
│   └── services/       auth.service.ts (signal isAuthenticated, localStorage)
│                       article.service.ts, type-conditionnement.service.ts, ...
├── shared/             composants réutilisables
├── features/           auth/login, dashboard, articles, stocks, emplacements,
│                       receptions, commandes, transferts,
│                       types-conditionnement, types-emplacement
└── layouts/
    ├── auth-layout/    wrapper simple
    └── main-layout/    navbar dropdown + router-outlet, bouton logout
```

### Conventions

- Standalone components (pas de NgModules)
- Lazy loading sur toutes les features
- `environment.ts` : `apiUrl = 'http://localhost:8000/api'`
- Formulaires : ReactiveFormsModule
- JWT stocké dans `localStorage` sous la clé `jwt_token`
- Intercepteur JWT injecte `Authorization: Bearer <token>` sur toutes les requêtes
- Images articles : `imageUrl(path) = environment.apiUrl.replace('/api','') + path`
- Upload image : uniquement en mode édition (l'ID article est requis pour nommer le fichier)

### Navbar (main-layout)

Dropdowns CSS pur au survol (pas de JS), 5 groupes :

| Trigger | Routes enfants |
|---------|----------------|
| Tableau de bord | `/dashboard` (lien direct) |
| Articles | `/articles`, `/types-conditionnement` |
| Commandes | `/commandes` |
| Réceptions | `/receptions` |
| Emplacements | `/emplacements`, `/types-emplacement` |
| Stock | `/stocks`, `/transferts` |

- `isGroupActive(paths: string[])` dans `MainLayoutComponent` → `[class.active]` sur `.nav-item`
- `routerLinkActive="is-active"` sur les liens dropdown (évite conflit avec `.nav-item.active`)
- Chevron SVG tourne 180° au survol via CSS

## Flux d'authentification

1. `POST /api/auth/login` → `{ token: "..." }`
2. Token stocké dans localStorage
3. Intercepteur JWT l'envoie sur chaque requête
4. Expiration / 401 → logout automatique + redirect `/auth/login`
5. Guard `authGuard` protège toutes les routes sous `/` (main-layout)
6. Guard `guestGuard` protège `/auth` (redirige si déjà connecté)
