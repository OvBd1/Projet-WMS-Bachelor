# LogiTrack — Guide utilisateur

Ce guide explique comment utiliser LogiTrack au quotidien. Il s'adresse aux opérateurs d'entrepôt et aux administrateurs. Les libellés cités entre guillemets ou en gras sont ceux affichés à l'écran.

## Sommaire

1. [Présentation](#1-présentation)
2. [Connexion et navigation](#2-connexion-et-navigation)
3. [Tableau de bord](#3-tableau-de-bord)
4. [Référentiels](#4-référentiels)
5. [Réceptions](#5-réceptions)
6. [Commandes](#6-commandes)
7. [Stocks et transferts](#7-stocks-et-transferts)
8. [Administration](#8-administration)
9. [Comportements communs](#9-comportements-communs)
10. [Questions fréquentes](#10-questions-fréquentes)

---

## 1. Présentation

LogiTrack est une application web de gestion d'entrepôt. Elle permet de :

- tenir à jour le catalogue des **articles** et la liste des **emplacements** de stockage ;
- enregistrer les **réceptions** de marchandises, qui alimentent le stock ;
- déplacer du stock d'un emplacement à un autre grâce aux **transferts** ;
- saisir et suivre les **commandes** ;
- consulter à tout moment le **stock** par article et par emplacement.

### La notion de dossier

LogiTrack peut gérer plusieurs entreprises dans la même installation. Chaque entreprise est un **dossier**. Toutes les données (articles, emplacements, tiers, réceptions, commandes, stocks, transferts) appartiennent à un dossier. Les données d'un dossier ne sont jamais visibles depuis un autre dossier.

### Les rôles

| | Utilisateur (ROLE_USER) | Administrateur (ROLE_ADMIN) |
|---|---|---|
| Dossier de travail | Uniquement le dossier auquel son compte est rattaché | Celui qu'il choisit dans le sélecteur de dossier |
| Articles, emplacements, tiers, types | Oui | Oui |
| Réceptions, commandes, transferts, stocks | Oui | Oui |
| Menu **Dossiers** (créer, modifier) | Non | Oui |
| Menu **Utilisateurs** (créer des comptes) | Non | Oui |

Il n'existe pas d'inscription libre : les comptes sont créés par un administrateur. Le tout premier administrateur d'une nouvelle installation est créé par l'équipe technique lors de l'installation (voir DEPLOYMENT.md).

---

## 2. Connexion et navigation

### Se connecter

1. Ouvrez l'adresse de l'application dans votre navigateur. L'écran **Connexion** s'affiche.
2. Saisissez votre **Email** et votre **Mot de passe**.
3. Cliquez sur **Se connecter**.

En cas d'erreur, le message « Email ou mot de passe incorrect. » s'affiche. Une fois connecté, vous arrivez sur le tableau de bord.

### Se déconnecter

1. Cliquez sur votre nom (ou vos initiales) en haut à droite de l'écran.
2. Le menu profil affiche votre adresse email ; cliquez sur **Déconnexion**.

Vous revenez à l'écran de connexion.

### Fin de session

Une session ne dure pas indéfiniment. Lorsqu'elle a expiré, la prochaine action vous ramène automatiquement à l'écran de connexion : reconnectez-vous simplement. Si vous tentez d'ouvrir une page à laquelle votre rôle ne donne pas accès, vous êtes renvoyé au tableau de bord.

### La barre de navigation

La barre du haut contient, de gauche à droite :

- le logo **LogiTrack** (retour au tableau de bord) ;
- les menus :

| Menu | Contenu |
|---|---|
| Tableau de bord | Accès direct |
| Réceptions | Accès direct |
| Commandes | Accès direct |
| Stock | **Stocks**, **Transferts** |
| Articles | **Articles**, **Types de conditionnement** |
| Tiers | Accès direct |
| Emplacements | **Emplacements**, **Types d'emplacement** |
| Dossiers | Accès direct (administrateurs uniquement) |
| Utilisateurs | Accès direct (administrateurs uniquement) |

Sur un écran large, les sous-menus s'ouvrent au survol de la souris. Le menu de la section en cours est mis en évidence.

- à droite, pour un administrateur, le **sélecteur de dossier** (voir ci-dessous) ;
- tout à droite, le **menu profil** (initiales, nom, email, **Déconnexion**).

### Le sélecteur de dossier (administrateurs)

Le sélecteur affiche la raison sociale du dossier actif, ou « Sélectionner un dossier » si aucun n'est choisi.

1. Cliquez sur le sélecteur.
2. Choisissez un dossier dans la liste (raison sociale suivie du code entre parenthèses).
3. La page se recharge : toutes les données affichées sont désormais celles de ce dossier.

À la connexion, si aucun dossier n'a encore été choisi, le premier dossier de la liste est sélectionné automatiquement. Un utilisateur standard ne voit pas ce sélecteur : il travaille toujours dans son propre dossier.

### Affichage sur mobile et tablette

Sur un écran étroit, les menus sont regroupés derrière un bouton à trois traits (« Ouvrir le menu »). Touchez-le pour afficher la navigation, puis touchez un menu pour déplier ses sous-menus. Le menu se referme dès que vous changez de page, ou en touchant la zone grisée à côté.

---

## 3. Tableau de bord

La page **Dashboard** affiche six cartes avec le nombre d'enregistrements du dossier actif : **Articles**, **Emplacements**, **Stocks**, **Réceptions**, **Commandes**, **Transferts**. Cliquer sur une carte ouvre la liste correspondante.

Le bloc **Accès rapide** propose quatre boutons : **Nouvelle réception**, **Nouvelle commande**, **Transfert de stock** et **Gérer les articles**. Chacun ouvre la page de liste concernée, d'où vous pouvez lancer la création.

---

## 4. Référentiels

Les référentiels sont les données de base à créer avant de travailler : types de conditionnement, articles, types d'emplacement, emplacements et tiers. Toutes les pages de liste fonctionnent de la même façon :

- un bouton de création en haut à droite ouvre un formulaire dans une fenêtre ;
- chaque ligne propose une icône crayon (**Éditer** / **Modifier**) et une icône corbeille (**Supprimer**) ;
- les champs marqués d'un astérisque (*) sont obligatoires ; le bouton **Enregistrer** reste inactif tant qu'ils ne sont pas remplis ;
- toute suppression demande une confirmation et est irréversible.

### 4.1 Types de conditionnement

Menu **Articles > Types de conditionnement**. Un type décrit la façon dont un article est conditionné (palette, carton, unité…).

- Création : bouton **+ Nouveau type**, champ **Libellé *** (100 caractères maximum).
- Suppression refusée si le type est utilisé : « Suppression impossible (type utilisé par des articles). »

### 4.2 Articles

Menu **Articles > Articles**. La liste affiche l'image, la **Référence**, le **Libellé**, le **Conditionnement** et deux indicateurs Oui/Non : **DLC** et **N° Série**.

**Créer un article**

1. Cliquez sur **+ Nouvel article**.
2. Renseignez :
   - **Référence *** (50 caractères maximum, unique dans le dossier) ;
   - **Libellé *** ;
   - **Description** (facultatif) ;
   - **Type de conditionnement** (facultatif, « — Aucun — » par défaut) ;
   - **Gestion DLC** : à cocher si une date limite de consommation doit être saisie à chaque réception ;
   - **Gestion numéro de série** : à cocher si un numéro de série doit être saisi à chaque réception.
3. Cliquez sur **Enregistrer**.

Si le type de conditionnement voulu n'existe pas, cliquez sur **+ Nouveau type** à côté du champ (voir [création rapide](#création-rapide)).

Si la référence est déjà prise, le message « Une référence identique existe déjà. » s'affiche.

**Ajouter une image**

L'image ne peut être ajoutée qu'à un article déjà créé :

1. Cliquez sur l'icône **Éditer** de l'article.
2. Dans le champ **Image**, choisissez un fichier (JPEG, PNG, WebP ou GIF, 5 Mo maximum). L'image actuelle, s'il y en a une, est affichée au-dessus.
3. Cliquez sur **Enregistrer**. La nouvelle image remplace l'ancienne.

Si l'article est enregistré mais que l'envoi de l'image échoue, le message « Article sauvegardé, mais l'upload d'image a échoué. » s'affiche. Dans la liste, cliquez sur une miniature pour l'afficher en grand, puis cliquez à côté pour la fermer.

**Supprimer un article** : refusé s'il est lié à des stocks ou des commandes (« Suppression impossible (article lié à des stocks ou commandes). »).

### 4.3 Types d'emplacement

Menu **Emplacements > Types d'emplacement**. Un type classe les emplacements (allée, rayon, zone froide…).

- Création : bouton **+ Nouveau type**, champ **Libellé *** (100 caractères maximum).
- Suppression refusée si le type est utilisé : « Suppression impossible (type utilisé par des emplacements). »

### 4.4 Emplacements

Menu **Emplacements > Emplacements**. La liste affiche le **Code**, le **Type** et la **Description**.

**Créer un emplacement**

1. Cliquez sur **+ Emplacement**.
2. Renseignez le **Code *** (par exemple A1-01, 50 caractères maximum, unique dans le dossier), le **Type d'emplacement *** et, si besoin, une **Description**.
3. Cliquez sur **Enregistrer**.

Le bouton **+ Nouveau type** à côté du champ type crée un type d'emplacement sans quitter le formulaire. Le bouton **+ Type** en haut de la page permet aussi de créer un type directement.

Messages possibles : « Ce code d'emplacement existe déjà. » ; à la suppression, « Suppression impossible (emplacement utilisé dans des stocks ou réceptions). »

### 4.5 Tiers

Menu **Tiers**. Les tiers sont vos fournisseurs, clients ou autres partenaires. La liste affiche le **Code**, le **Nom**, le **Type** et l'**Adresse**.

**Créer un tiers**

1. Cliquez sur **+ Nouveau tiers**.
2. Renseignez :
   - **Code *** (50 caractères maximum, unique dans le dossier) ;
   - **Type *** : FOURNISSEUR, CLIENT ou AUTRE ;
   - **Nom *** (raison sociale) ;
   - l'adresse, facultative : **Rue**, **Code postal**, **Ville**, **Pays**. Le champ Rue propose des suggestions (voir [autocomplétion d'adresse](#autocomplétion-dadresse)).
3. Cliquez sur **Enregistrer**.

Messages possibles : « Code obligatoire (max 50 car.) », « Nom obligatoire », « Ce code de tiers existe déjà. » ; à la suppression, « Suppression impossible (tiers utilisé dans des commandes ou réceptions). »

---

## 5. Réceptions

Une réception enregistre l'arrivée de marchandises. Elle contient une ou plusieurs lignes (article, emplacement, quantité).

### Cycle de vie

| Statut | Effet sur le stock | Actions possibles (page de détail) |
|---|---|---|
| EN_ATTENTE | Aucun | **✓ Valider la réception**, **✎ Modifier**, **Annuler la réception**, **Supprimer** |
| VALIDEE | Les quantités des lignes ont été ajoutées au stock | **Annuler la réception** (retire ces quantités du stock) |
| ANNULEE | Aucun (le stock ajouté a été retiré si la réception était validée) | **Supprimer** |

Points à retenir :

- créer une réception ne modifie pas le stock ; seule la **validation** l'augmente ;
- seule une réception EN_ATTENTE peut être modifiée ;
- une réception VALIDEE ne peut pas être supprimée : il faut l'annuler.

### Créer une réception

1. Menu **Réceptions**, bouton **+ Nouvelle réception**.
2. En-tête :
   - **Fournisseur / Tiers** : facultatif (« — Aucun tiers — »). Le lien **+ Nouveau tiers** crée un tiers sans quitter le formulaire ;
   - **Date de réception** : la date du jour est proposée.
3. Section **Lignes de réception** : une première ligne est déjà présente. Pour chaque ligne, choisissez l'**Article**, l'**Emplacement** et la **Qté** (au moins 1).
4. Si l'article gère la DLC, le champ **DLC *** apparaît ; s'il gère le numéro de série, le champ **N° série *** apparaît. Ils sont alors obligatoires.
5. **+ Ligne** ajoute une ligne ; l'icône croix (« Retirer la ligne ») en supprime une. Une réception garde toujours au moins une ligne.
6. Cliquez sur **Enregistrer**. La réception apparaît dans la liste avec le statut EN_ATTENTE.

### Consulter une réception

La liste affiche le numéro (**#**), la **Date**, le **Fournisseur / Tiers**, le **Statut**, le nombre de **Lignes** et **Créée par**. Cliquez sur le numéro (par exemple #12) pour ouvrir le détail.

La page de détail présente :

- **Informations générales** : date, statut, créateur, dates et auteurs de création, de validation et de modification, nombre de lignes ;
- **Tiers** : nom et type du tiers associé, ou « Aucun tiers associé » ;
- **Lignes de réception** : article, libellé, emplacement, quantité, DLC et numéro de série. Une DLC dépassée apparaît en rouge, une DLC à moins de 30 jours en orange, les autres en vert.

Le bouton **← Retour** ramène à la liste.

### Valider une réception

1. Ouvrez une réception EN_ATTENTE.
2. Cliquez sur **✓ Valider la réception**.
3. Confirmez avec **Valider** dans la fenêtre « Le stock sera mis à jour. ».

Le statut passe à VALIDEE et la quantité de chaque ligne est ajoutée au stock de l'article sur l'emplacement indiqué. Les champs « Validée le » et « Validée par » apparaissent.

### Modifier une réception

1. Ouvrez une réception EN_ATTENTE et cliquez sur **✎ Modifier**.
2. Onglet **En-tête** : modifiez le **Fournisseur / Tiers** ou la **Date de réception *** (« Date obligatoire » si vide).
3. Onglet **Lignes** (le nombre de lignes est indiqué) :
   - **+ Ajouter une ligne** ouvre une fenêtre (**Article ***, **Emplacement ***, **Quantité ***, et **DLC *** / **N° série *** selon l'article), validée par **Ajouter** ;
   - l'icône crayon modifie une ligne (bouton **Mettre à jour**) ;
   - l'icône corbeille retire une ligne, après confirmation (**Retirer**).
4. Cliquez sur **Enregistrer** en haut de la page. Les changements ne sont pris en compte qu'à ce moment-là.

Une réception doit contenir au moins une ligne : sinon le message « Ajoutez au moins une ligne à la réception. » s'affiche.

### Annuler une réception

1. Ouvrez la réception et cliquez sur **Annuler la réception**.
2. Confirmez. Pour une réception validée, la fenêtre précise que « Le stock sera décrémenté en conséquence. »

Le statut passe à ANNULEE. Si la réception était validée, ses quantités sont retirées du stock. Cette opération est refusée si le stock ne contient plus assez de marchandise (par exemple après un transfert vers un autre emplacement) : voir la [FAQ](#10-questions-fréquentes).

### Supprimer une réception

Possible pour une réception EN_ATTENTE ou ANNULEE : bouton **Supprimer**, puis confirmation. La suppression est irréversible.

---

## 6. Commandes

### Statuts

| Statut | Signification |
|---|---|
| EN_ATTENTE | Commande saisie, encore modifiable |
| PREPAREE | Commande préparée |
| EXPEDIEE | Commande expédiée |
| ANNULEE | Commande annulée |

Le changement de statut d'une commande **n'a aucun effet sur le stock** : il sert au suivi. Seules les réceptions et les transferts modifient le stock.

### Créer une commande

1. Menu **Commandes**, bouton **+ Nouvelle commande**.
2. Renseignez, si besoin, le **Client / Tiers** et la **Date d'expédition** (tous deux facultatifs).
3. Section **Lignes de commande** : pour chaque ligne, choisissez l'**Article** et la **Quantité** (au moins 1). **+ Ligne** ajoute une ligne, l'icône croix la retire (au moins une ligne reste présente).
4. Cliquez sur **Enregistrer**.

La commande est créée au statut EN_ATTENTE, datée automatiquement, et reçoit un numéro lisible attribué automatiquement, par exemple **CMD-00001**.

### Consulter la liste

La liste affiche le **Numéro**, la **Date**, la date d'**Expédition**, le **Statut**, le **Client / Tiers** et l'**Utilisateur**. Cliquez sur le numéro pour ouvrir la commande.

### Modifier une commande

Une commande n'est modifiable que tant qu'elle est EN_ATTENTE.

1. Ouvrez la commande depuis la liste.
2. Onglet **En-tête** : **Date de commande *** , **Client / Tiers**, **Date d'expédition** (laisser ce champ vide efface la date).
3. Onglet **Lignes** : **+ Ajouter une ligne** (**Article ***, **Quantité ***), icône crayon pour modifier, icône corbeille pour retirer (avec confirmation).
4. Cliquez sur **Enregistrer**. Vous revenez à la liste.

Une commande sans ligne ne peut pas être enregistrée (« Ajoutez au moins une ligne à la commande. »).

Pour une commande dans un autre statut, la page affiche les informations en lecture seule (**Informations générales**, **Client / Tiers**, **Lignes de commande**) et le bouton **Enregistrer** n'apparaît pas.

### Changer le statut

1. Ouvrez la commande et cliquez sur **Changer le statut**.
2. La fenêtre rappelle le statut actuel. Cliquez sur le nouveau statut (le statut actuel est grisé).
3. Le changement est immédiat ; **Fermer** referme la fenêtre sans rien changer.

Tous les statuts sont proposés, quel que soit le statut actuel.

### Supprimer une commande

Bouton **Supprimer** sur la page de la commande, puis confirmation. La suppression est irréversible.

---

## 7. Stocks et transferts

### Principe

Le stock n'est jamais saisi à la main. Il évolue uniquement par les mouvements :

| Opération | Effet sur le stock |
|---|---|
| Validation d'une réception | + quantité de chaque ligne sur l'emplacement de la ligne |
| Annulation d'une réception validée | − quantité de chaque ligne sur l'emplacement de la ligne |
| Transfert | − quantité sur l'emplacement source, + quantité sur l'emplacement destination |

Un stock ne peut jamais devenir négatif.

### Consulter les stocks

Menu **Stock > Stocks**. La page est en lecture seule et affiche, pour chaque couple article / emplacement, l'**Article**, la **Référence**, l'**Emplacement** et la **Quantité**.

Le champ « Filtrer par article ou emplacement… » filtre la liste pendant la saisie, sur le libellé de l'article, sa référence ou le code de l'emplacement.

Tant qu'aucune réception n'a été validée, la page indique « Les stocks apparaissent après une réception. »

### Effectuer un transfert

Menu **Stock > Transferts**. La liste affiche l'historique : numéro, **Date** (avec l'heure), **Article**, **Quantité**, **Source → Destination** et **Utilisateur**.

1. Cliquez sur **+ Nouveau transfert**.
2. Choisissez l'**Article ***, l'**Emplacement source ***, l'**Emplacement destination *** et la **Quantité *** (au moins 1).
3. Cliquez sur **Transférer**.

Le transfert est refusé dans deux cas :

- source et destination identiques : « La source et la destination doivent être différentes. » ;
- stock insuffisant sur l'emplacement source : un message indique l'article, l'emplacement, la quantité disponible et la quantité demandée.

Un transfert enregistré ne peut être ni modifié ni supprimé. Pour le corriger, faites un transfert dans l'autre sens.

---

## 8. Administration

Les menus **Dossiers** et **Utilisateurs** ne sont visibles que par les administrateurs.

### 8.1 Dossiers

La liste affiche le **Code**, la **Raison sociale** et l'**Adresse** de chaque dossier.

**Créer un dossier**

1. Cliquez sur **+ Nouveau dossier**.
2. Renseignez le **Code *** (par exemple DOS1, 50 caractères maximum), la **Raison sociale *** et, si besoin, l'adresse (**Rue** avec suggestions, **Code postal**, **Ville**, **Pays**).
3. Cliquez sur **Enregistrer**.

Pour modifier un dossier, cliquez sur l'icône **Modifier** de sa ligne. L'écran ne propose pas de supprimer un dossier.

Le nouveau dossier apparaît ensuite dans le sélecteur de dossier de la barre de navigation.

### 8.2 Utilisateurs

La liste affiche l'**Email**, le **Nom**, le **Rôle** et le **Dossier** de chaque compte. Un administrateur sans dossier est indiqué « — (tous les dossiers) ».

**Créer un compte**

1. Cliquez sur **+ Nouvel utilisateur**.
2. Renseignez :
   - **Email *** (adresse valide, non déjà utilisée) ;
   - **Prénom *** et **Nom *** ;
   - **Mot de passe *** (6 caractères minimum) ;
   - **Rôle *** : ROLE_USER (utilisateur) ou ROLE_ADMIN (administrateur) ;
   - **Dossier *** : affiché et obligatoire uniquement pour ROLE_USER.
3. Cliquez sur **Enregistrer**.

Communiquez ensuite l'email et le mot de passe à la personne concernée.

Messages possibles : « Un dossier est requis pour un utilisateur non-admin. », « Cet email est déjà utilisé. ». L'écran ne propose ni modification ni suppression de compte.

---

## 9. Comportements communs

### Fenêtres de confirmation

Une fenêtre de confirmation s'ouvre avant chaque action sensible : suppression (article, type, emplacement, tiers, réception, commande), retrait d'une ligne, validation ou annulation d'une réception. Elle rappelle ce qui va se passer. Cliquez sur le bouton d'action (**Supprimer**, **Valider**, **Retirer**…) pour confirmer, ou sur **Annuler** (ou à côté de la fenêtre) pour renoncer.

### Abandon de saisie

Si vous fermez une fenêtre de formulaire (bouton **Annuler** ou clic à côté) après avoir modifié des champs, LogiTrack demande « Les modifications non enregistrées seront perdues. Fermer quand même ? ». Cliquez sur **Abandonner** pour fermer, ou sur **Annuler** pour reprendre la saisie. Si rien n'a été modifié, la fenêtre se ferme directement.

### Création rapide

Certains formulaires permettent de créer l'élément manquant sans les quitter :

| Depuis le formulaire | Lien | Élément créé |
|---|---|---|
| Nouvelle réception | **+ Nouveau tiers** | Tiers (Code, Type, Nom) |
| Article | **+ Nouveau type** | Type de conditionnement |
| Emplacement | **+ Nouveau type** | Type d'emplacement |

Après **Créer** (ou **Enregistrer**), l'élément créé est automatiquement sélectionné dans le formulaire d'origine. Pour un tiers créé ainsi, l'adresse peut être complétée plus tard depuis la page **Tiers**.

### Autocomplétion d'adresse

Dans les formulaires **Tiers** et **Dossiers**, le champ **Rue** propose des adresses issues de la Base Adresse Nationale :

1. Tapez au moins 3 caractères (numéro et nom de rue).
2. Une liste de suggestions apparaît. Cliquez sur une adresse, ou utilisez les flèches haut/bas puis Entrée.
3. La rue, le code postal, la ville et le pays sont remplis automatiquement.

La touche Échap ferme la liste. La saisie manuelle reste toujours possible : si aucune suggestion ne convient ou si le service d'adresses est indisponible, remplissez simplement les champs à la main.

### Messages et états des pages

- Pendant le chargement, un message « Chargement… » s'affiche.
- Une liste vide affiche un message explicatif (par exemple « Aucun article »).
- Les erreurs apparaissent dans un bandeau rouge, en haut de la page ou dans le formulaire concerné.
- Un champ obligatoire mal rempli est encadré en rouge après être passé dessus.

---

## 10. Questions fréquentes

**Je ne trouve pas où saisir une quantité en stock.**
C'est normal : le stock ne se saisit pas. Créez une réception puis validez-la, ou faites un transfert entre emplacements.

**J'ai créé une réception mais le stock n'a pas changé.**
La réception est au statut EN_ATTENTE. Ouvrez-la et cliquez sur **✓ Valider la réception**.

**Je ne peux pas modifier une réception validée.**
Seules les réceptions EN_ATTENTE sont modifiables. Annulez la réception validée (le stock est retiré), puis créez une nouvelle réception correcte et validez-la.

**Je ne peux pas supprimer une réception.**
Une réception validée ne peut pas être supprimée. Annulez-la d'abord ; elle pourra ensuite être supprimée.

**Mon transfert est refusé avec un message « Stock insuffisant ».**
L'emplacement source ne contient pas assez de cet article. Vérifiez la quantité disponible dans **Stock > Stocks** (le message indique la quantité disponible et la quantité demandée), puis réduisez la quantité ou choisissez un autre emplacement source.

**L'annulation d'une réception validée est refusée (« Stock insuffisant »).**
Une partie de la marchandise reçue a quitté l'emplacement (par exemple par un transfert). Ramenez d'abord la quantité nécessaire sur cet emplacement par un transfert, puis annulez à nouveau.

**Le bouton Enregistrer reste grisé.**
Un champ obligatoire (*) n'est pas rempli ou contient une valeur invalide (quantité inférieure à 1, DLC ou numéro de série manquant pour un article qui les gère…).

**Je ne vois pas les données d'un autre dossier.**
Chaque dossier est cloisonné. Un utilisateur standard ne voit que son dossier. Un administrateur doit choisir le dossier voulu dans le sélecteur de la barre de navigation.

**Je ne vois pas les menus Dossiers et Utilisateurs.**
Ils sont réservés aux administrateurs. Adressez-vous à un administrateur pour créer un dossier ou un compte.

**Je ne peux plus modifier une commande.**
Elle n'est plus au statut EN_ATTENTE. Seul le statut peut encore être changé avec **Changer le statut**.

**Impossible de supprimer un article, un emplacement, un type ou un tiers.**
L'élément est encore utilisé (dans des stocks, réceptions, commandes, articles ou emplacements). Le message affiché précise la raison.

**L'image de mon article n'est pas acceptée.**
Vérifiez le format (JPEG, PNG, WebP ou GIF) et la taille (5 Mo maximum). Pensez à enregistrer d'abord l'article : l'image ne s'ajoute qu'en modification.

**J'ai été renvoyé à l'écran de connexion.**
Votre session a expiré. Reconnectez-vous avec votre email et votre mot de passe.

**Les suggestions d'adresse n'apparaissent pas.**
Tapez au moins 3 caractères. Si rien n'apparaît, le service d'adresses est peut-être indisponible : saisissez l'adresse à la main.
