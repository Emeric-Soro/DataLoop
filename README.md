# DataLoop Backend

API Laravel pour une plateforme de collecte et validation communautaire de donnees (annotation + contributions utilisateurs).

## Stack

- PHP 8.4+
- Laravel 12
- MySQL (Laragon ou Docker)
- Sanctum (auth API token)

## Fonctionnalites principales

- Authentification API: inscription, connexion, OTP mock, profil
- Annotation de taches avec consensus communautaire
- Score de confiance dynamique et taches sentinelles
- Wallet virtuel et transactions (gain/retrait)
- Contributions utilisateurs (texte/image/audio)
- Workflow de review communautaire avec integration dataset
- Endpoints admin (dashboard, upload taches, config)

## Demarrage rapide (Laragon recommande)

1. Cloner le projet et installer les dependances:

```bash
composer install
```

2. Copier l'environnement:

```bash
cp .env.example .env
```

3. Configurer la base locale dans `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dataloop
DB_USERNAME=root
DB_PASSWORD=
```

4. Generer la cle application:

```bash
php artisan key:generate
```

5. Executer les migrations:

```bash
php artisan migrate
```

6. Lancer le serveur:

```bash
php artisan serve
```

## Option Docker

Le projet inclut un `docker-compose.yml` (app + mysql + phpmyadmin). Pour un usage Docker, adapter `DB_HOST` a `db` dans `.env`.

## Deploiement sur Railway

Le projet est deployable via le `Dockerfile` du repo.

### 1. Creer les services Railway

- Service 1: Backend (ce repo)
- Service 2: MySQL (plugin Railway)

### 2. Variables d'environnement a definir (service Backend)

Minimum recommande:

```env
APP_NAME=DataLoop
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<ton-domaine-railway>
APP_KEY=base64:...

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=<MYSQLHOST>
DB_PORT=<MYSQLPORT>
DB_DATABASE=<MYSQLDATABASE>
DB_USERNAME=<MYSQLUSER>
DB_PASSWORD=<MYSQLPASSWORD>

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
```

Notes:

- Les valeurs `MYSQL*` sont fournies automatiquement par Railway sur le service MySQL.
- Le backend sait maintenant lire directement `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD` et `MYSQL_URL`, meme si tu ne recopies pas ces valeurs dans des variables `DB_*`.
- Eviter de versionner un vrai fichier `.env` ou d'importer tel quel un `.env` local dans Railway.
- Renseigner `APP_KEY` (genere localement avec `php artisan key:generate --show`).

### 3. Build et demarrage

- Railway detecte le `Dockerfile` et build l'image automatiquement.
- Le serveur web demarre avec Apache (`apache2-foreground`).

### 4. Initialisation base de donnees (une fois apres premier deploy)

Dans le shell Railway du service Backend:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan l5-swagger:generate
```

### 5. Verification rapide

- Ouvrir `/api/v1/auth/login` via ton client API.
- Ouvrir `/api/documentation` pour Swagger.
- Verifier les logs Railway en cas d'erreur 500.

## Prefixes API

- Public auth: `/api/v1/auth/*`
- Authentifie: `/api/v1/*`
- Admin: `/api/v1/admin/*`

## Endpoints cles

### Auth

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/otp/send`
- `POST /api/v1/auth/otp/verify`
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`

### Tasks

- `GET /api/v1/tasks/next`
- `POST /api/v1/tasks/{id}/annotate`
- `POST /api/v1/tasks/{id}/skip`
- `GET /api/v1/tasks/history`
- `GET /api/v1/tasks/{id}`

### Wallet

- `GET /api/v1/wallet/balance`
- `GET /api/v1/wallet/transactions`
- `POST /api/v1/wallet/withdraw`

### Contributions

- `POST /api/v1/contributions`
- `GET /api/v1/contributions/mine`
- `GET /api/v1/contributions/review/next`
- `POST /api/v1/contributions/{id}/review`
- `GET /api/v1/contributions/{id}`

### Admin

- `GET /api/v1/admin/dashboard`
- `GET /api/v1/admin/users`
- `PATCH /api/v1/admin/users/{id}`
- `GET /api/v1/admin/alerts`
- `POST /api/v1/admin/tasks/upload`
- `GET /api/v1/admin/datasets`
- `GET /api/v1/admin/datasets/{id}/export`
- `PATCH /api/v1/admin/config`

## Lancer les tests

```bash
php artisan test
```

La suite couvre actuellement:

- Feature: Auth, Tasks, Wallet, Contributions, Admin
- Unit: ConsensusService, TrustScoreService, ContributionConsensusService

## Recette finale

Une checklist complete de validation manuelle est disponible dans:

- `storage/app/private/checklist_recette_finale.md`

## Notes implementation

- Les recompenses sont gerees par `RewardService`
- Le consensus de taches est gere par `ConsensusService`
- Le consensus contributions est gere par `ContributionConsensusService`
- Le score de confiance est gere par `TrustScoreService`

## Licence

Projet interne DataLoop.
