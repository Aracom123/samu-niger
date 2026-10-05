# SAMU Niger — Plateforme de gestion des urgences

Plateforme Web et mobile de gestion des urgences, d'orientation des patients et de suivi en temps réel
de la disponibilité des lits, pour le SAMU Niger. Voir [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) et
le cahier des charges d'origine pour le détail fonctionnel.

## Structure du dépôt

```
backend/   API Laravel 11 (PHP 8.5) — source de vérité, PostgreSQL
mobile/    Application Flutter pour les équipes mobiles SAMU
docs/      Architecture, spécifications
```

## Prérequis locaux

- PHP 8.3+, Composer
- PostgreSQL 16
- Flutter (stable)

Sur cette machine, ces outils ont été installés via Homebrew :
`brew install php composer postgresql@16` et `brew install --cask flutter`.

## Backend (Laravel)

```bash
cd backend
composer install
cp .env.example .env   # puis ajuster DB_* si besoin
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### Base de données

⚠️ Sur cette machine de développement, un serveur PostgreSQL 14 système tourne déjà sur le port 5432
(utilisé par un autre projet). L'instance PostgreSQL 16 installée pour SAMU a donc été configurée sur
le **port 5433** pour éviter tout conflit :

- `backend/.env` (local, non versionné) : `DB_PORT=5433`, `DB_DATABASE=samu_niger`, `DB_USERNAME=aracom`
- `backend/.env.example` (référence pour un déploiement standard) : port 5432 par défaut

Démarrer/arrêter le service local :

```bash
brew services start postgresql@16
brew services stop postgresql@16
```

### Compte de test

Après `php artisan migrate --seed` :

- **email** : `admin@samu-niger.ne`
- **rôle** : Administrateur national

### API

Toutes les routes sont sous `/api`, protégées par Laravel Sanctum (token Bearer) et le système de
rôles/permissions `spatie/laravel-permission`. Voir `backend/routes/api.php` pour la liste complète
(auth, régions, structures, capacités, patients, interventions, orientations, confirmations
d'accueil, tableau de bord, notifications).

### Backoffice (Filament)

Une interface d'administration complète est disponible sur `/admin` (voir [Compte de test](#compte-de-test)
ci-dessus). Elle reprend la charte graphique du Ministère de la Santé et de l'Hygiène Publique (MS/HP :
vert institutionnel, logo) et propose un tableau de bord avec indicateurs temps réel (interventions du
jour, structures en saturation, lits disponibles) et la liste des structures en tension.

Une équipe mobile SAMU peut créer et gérer ses propres interventions depuis le backoffice (en plus de
l'API/app mobile) : le champ "Équipe mobile" du formulaire se pré-remplit et se verrouille sur sa propre
équipe — impossible de créer une intervention pour une autre équipe. Cela nécessite qu'un administrateur
ait créé au moins une **Équipe mobile** (`/admin/equipe-mobiles`) et y ait rattaché l'utilisateur
(champ "Équipe mobile" du formulaire Utilisateur). Les **Ambulances** se gèrent de la même façon
(`/admin/ambulances`).

## Mobile (Flutter)

Application pour les équipes mobiles SAMU (section 14 du cahier des charges) : connexion, liste des
interventions affectées, détail avec progression du statut, recherche de structures avec disponibilité
des lits en temps réel, et orientation du patient. Pas de mode hors-ligne pour l'instant (prévu en
évolution — section 14.1).

```bash
cd mobile
flutter pub get
flutter run -d chrome                 # ou -d web-server pour un serveur simple
# API par défaut : http://127.0.0.1:8099/api (voir lib/config/api_config.dart)
# Modifiable via : flutter run --dart-define=API_BASE_URL=https://mon-api/api
```

⚠️ Ni le SDK Android ni Xcode complet ne sont installés sur cette machine — impossible de tester sur
émulateur/simulateur pour l'instant. L'app a été développée et validée via le mode web de Flutter
(`flutter run -d web-server`), qui ne nécessite aucune installation supplémentaire. Pour tester sur un
vrai appareil ou émulateur, installer Android Studio et/ou Xcode, puis relancer `flutter doctor`.

Compte de test : créer un utilisateur avec le rôle **Équipe mobile SAMU** et un `equipe_mobile_id`
renseigné (voir le backoffice `/admin/users`), avec au moins une intervention qui lui est affectée.

## État d'avancement

- [x] Backend Laravel initialisé, connecté à PostgreSQL
- [x] Migrations pour toutes les entités du cahier des charges (régions, structures sanitaires,
      capacités + historique, patients, interventions, orientations, confirmations d'accueil,
      notifications, journal d'audit)
- [x] Rôles et permissions seedés (6 rôles conformes à la section 4 du cahier des charges)
- [x] Calcul automatique de la disponibilité des lits et du statut de saturation (seuils
      paramétrables dans `config/capacites.php`), avec historisation automatique
- [x] API REST : authentification, CRUD des référentiels, cycle de vie complet d'une intervention
      (demande → affectation → orientation → confirmation d'accueil → clôture)
- [x] Contrôle d'accès par rôle/région/structure (policies Laravel, section 20 du cahier des charges)
- [x] 34 tests automatisés (auth, calcul de capacité, parcours intervention, contrôle d'accès)
- [x] Backoffice Filament aux couleurs du MS/HP (logo, palette), navigation regroupée par domaine,
      tableau de bord personnalisé (indicateurs + structures en tension)
- [x] Gestion des équipes mobiles et ambulances (backoffice) ; une équipe mobile peut créer et gérer
      ses propres interventions depuis le web (champ équipe verrouillé sur elle-même)
- [x] Application mobile Flutter : connexion, interventions affectées, progression du statut,
      recherche de structures avec disponibilité, orientation du patient — testée de bout en bout
      (voir ci-dessous ; pas encore testée sur émulateur/simulateur réel)
- [ ] Frontend Web (framework à choisir — Laravel Blade, Vue ou React)
- [ ] Notifications push, cartographie, mode hors-ligne mobile
