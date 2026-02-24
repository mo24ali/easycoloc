# 🏠 EasyColoc

EasyColoc est une application web de gestion de colocation permettant de suivre les dépenses communes et de répartir automatiquement les dettes entre membres.

L’objectif principal est d’éviter les calculs manuels et d’offrir une vision claire de **« qui doit quoi à qui »**.

---

# 📌 Contexte du projet

## Version actuelle

La version actuelle permet :

- Création et gestion des colocations
- Invitation via lien/token avec envoi email
- Ajout et suppression de dépenses avec catégories
- Calcul automatique des soldes et remboursements simplifiés
- Enregistrement des paiements (« Marquer payé »)
- Système de réputation selon le comportement financier
- Administration globale (statistiques, bannissement/débannissement)
- Filtrage des dépenses par mois

---

# 🎯 Objectifs

## 1. Objectifs fonctionnels

- Gérer des colocations (création, annulation, départ/retrait de membres)
- Suivre les dépenses partagées
- Calculer automatiquement les soldes individuels
- Afficher une vue simplifiée des remboursements

## 2. Objectifs techniques

- Architecture : Monolithique MVC (Laravel)
- SGBD : MySQL / PostgreSQL (migrations)
- ORM : Eloquent (`hasMany`, `belongsToMany`)
- Authentification : Laravel Breeze / Jetstream
- Gestion des rôles

---

# 👥 Acteurs et rôles

## Member
- Membre standard d’une colocation
- Peut ajouter des dépenses
- Peut voir son solde
- Peut quitter la colocation

## Owner
- Créateur de la colocation
- Peut inviter des membres
- Peut retirer un membre
- Peut annuler la colocation
- Gère les catégories

## Global Admin
- Accès aux statistiques globales
- Bannissement / débannissement des utilisateurs
- Peut être également Owner ou Member

> Le premier utilisateur inscrit est automatiquement promu **Admin Global**.

---

# 📦 Périmètre

## Inclus

- Authentification et profil utilisateur
- Promotion automatique du premier utilisateur en admin global
- Gestion des colocations (create, show, update, destroy, cancel)
- Invitations par token
- Restriction : une seule colocation active par utilisateur
- Gestion des dépenses (montant, date, catégorie, payeur)
- Gestion des catégories
- Calcul des balances et vue « qui doit à qui »
- Paiements simples (« Marquer payé »)
- Système de réputation (+1 / -1)
- Dashboard admin global
- Filtre des dépenses par mois

## Hors périmètre (Bonus)

- Paiement Stripe
- Notifications temps réel
- Calendrier
- Export de données

---

# 💸 Gestion des Dépenses

Chaque dépense contient :

- Titre
- Montant
- Date
- Catégorie
- Payeur

Après ajout :

- Recalcul automatique des soldes
- Mise à jour des remboursements simplifiés

---

# 📊 Balances et dettes

Calcul automatique basé sur :

- Total payé par utilisateur
- Part individuelle
- Solde net

Vue synthétique :

> Qui doit à qui

Réduction des dettes via l’action :

```
Marquer payé
```

---

# ⭐ Système de Réputation

- Départ ou annulation avec dette → -1
- Départ ou annulation sans dette → +1

### Cas spécifique :

Si un owner retire un membre ayant une dette :
- La dette est imputée à l’owner (ajustement interne).

---

# 🔄 Scénarios d’implémentation

## 1. Invitation

- Génération d’un token unique
- Envoi par email
- Acceptation ou refus
- Vérification :
  - Email correspondant
  - Pas de colocation active existante

## 2. Dépense commune

- Ajout d’une dépense
- Recalcul automatique des soldes
- Mise à jour des remboursements

## 3. Départ avec dette

- Application pénalité réputation
- Redistribution interne

## 4. Blocage multi-colocation

- Impossible de créer ou rejoindre une nouvelle colocation
  si un membership actif existe déjà.

---

# 🏗️ Architecture Technique

Architecture monolithique MVC Laravel :

- Models : User, Colocation, Expense, Category, Membership
- Controllers : Logique métier
- Blade : Interface utilisateur
- Eloquent ORM : Relations et requêtes
- Middleware & Policies : Gestion des permissions

---
