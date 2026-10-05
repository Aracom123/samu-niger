# Architecture technique — SAMU Niger

Référence : `Cahier_des_charges_SAMU_Niger_Plateforme_Web_Mobile.docx`

## Stack retenue

| Composant | Technologie |
|---|---|
| Backend / API | Laravel 11 (PHP 8.3) |
| Base de données | PostgreSQL 16 |
| Auth API | Laravel Sanctum (tokens), rôles/permissions via `spatie/laravel-permission` |
| Application mobile | Flutter (Dart) |
| Communication | REST API JSON sur HTTPS/TLS |
| Notifications | Firebase Cloud Messaging (push) — à confirmer |
| Cartographie | À définir (Google Maps / OpenStreetMap) |

## Arborescence du dépôt

```
SAMU/
├── backend/    Laravel API (source de vérité, sert le Web via API + éventuel frontend séparé)
├── mobile/     Application Flutter pour les équipes mobiles SAMU
└── docs/       Documentation d'architecture, specs, schémas
```

Le frontend Web (section 15 du cahier des charges) consomme la même API REST que l'application mobile. Le choix du framework frontend (Vue/React/Blade) reste "à préciser" selon le cahier des charges — non scaffoldé dans cette première passe.

## Modèle de données (entités principales — section 19)

Hiérarchie : `Region → StructureSanitaire → Capacite`

> **Choix v1 (simplification volontaire) :** le cahier des charges prévoit une hiérarchie
> `Region → StructureSanitaire → Service/Unité d'urgence → Capacité` (section 5), pensée pour le cas
> où une structure gère plusieurs services (ex: Urgences adultes / Urgences pédiatriques distinctes,
> cf. section 34). En pratique, chaque structure n'a qu'**un seul service d'urgence**, point d'entrée
> unique des patients orientés par le SAMU — c'est ce service qui réoriente ensuite en interne vers les
> services spécialisés, hors périmètre de cette plateforme. La capacité est donc rattachée **directement**
> à `StructureSanitaire` (relation 1-1, `Capacite` créée automatiquement à la création de la structure).
> La table `Capacite` reste néanmoins un modèle à part (avec son historique) précisément pour pouvoir
> réintroduire un niveau `Service` plus tard sans perte de données, si un hôpital multi-services l'exige :
> il suffira d'ajouter une table `services` et une colonne `service_id` sur `capacites`.

- **User** — comptes, rattaché à un `Role`, éventuellement à une `StructureSanitaire` ou `Region`
- **Role / Permission** — via spatie/laravel-permission
- **Region** — régions d'intervention (activable/désactivable, sans redéploiement)
- **TypeStructure** — Hôpital National / Hôpital de District / CSI / extensible
- **StructureSanitaire** — nom, code, type, région, département/commune/localité, géoloc, contact, responsable, statut ; possède **une** `Capacite` (son service d'urgence)
- **Capacite** — lits totaux/occupés/réservés/indisponibles par structure, disponibles = calculé
- **HistoriqueCapacite** — audit des changements de capacité (date, heure, utilisateur, ancienne/nouvelle valeur)
- **Patient** — identité (partielle si inconnue), sexe, âge, contact, motif, niveau d'urgence
- **Intervention** — cycle de vie complet (statuts section 10), horodatage à chaque étape
- **EquipeMobile** — équipe SAMU affectée à une intervention
- **Ambulance** — véhicule affecté
- **Orientation** — structure sélectionnée pour un patient, lien Intervention ↔ StructureSanitaire
- **ConfirmationAccueil** — réponse de la structure (acceptée/refusée + motif)
- **Notification** — alertes saturation, réception de patient, etc.
- **JournalAudit** — traçabilité des opérations sensibles

## Rôles & permissions (section 4)

- Administrateur national — accès complet
- Responsable SAMU — lecture globale interventions/statistiques
- Responsable régional — scope région
- Responsable de structure — gestion utilisateurs + capacité de sa structure
- Agent de structure — mise à jour capacité selon autorisations
- Équipe mobile SAMU — interventions affectées uniquement

Implémentation : middleware Laravel + policies par modèle, scoping par `region_id` / `structure_id` sur les requêtes.

## API — découpage par module (section 15)

- `/api/auth/*` — login, logout, refresh, changement mot de passe
- `/api/regions`
- `/api/types-structures`
- `/api/structures`
- `/api/capacites` (+ historique) — une capacité par structure
- `/api/patients`
- `/api/interventions` (création, affectation, changement de statut, clôture)
- `/api/orientations`
- `/api/dashboard` — agrégats temps réel
- `/api/rapports` — export PDF/Excel
- `/api/notifications`

## Points d'attention techniques

- **Calcul automatique** : `disponibles = total - occupés - réservés - indisponibles` (contrainte DB + recalcul applicatif)
- **Fraîcheur des données** : chaque capacité expose `updated_at` ; le frontend/mobile doit signaler une donnée "périmée" au-delà d'un seuil paramétrable
- **Mode hors-ligne mobile** : file d'attente locale (SQLite/Hive) + synchronisation différée avec état visible
- **Sécurité** : HTTPS obligatoire, hashage des mots de passe (bcrypt/argon2 natif Laravel), rate limiting API, logs d'audit sur toute écriture sensible
- **Évolutivité** : ajout de régions/structures/types sans changement de code (tables de référence, pas d'enum figé)

## Prochaines étapes après scaffolding initial

1. Migrations + modèles Eloquent pour toutes les entités ci-dessus
2. Seeders (régions du Niger, types de structures par défaut, rôles/permissions)
3. Endpoints CRUD de base + policies
4. Squelette Flutter : structure de dossiers, client API, gestion d'état, mode offline
5. Configuration `.env` PostgreSQL + tests de connexion
