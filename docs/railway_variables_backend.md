# Variables Railway correctes pour le backend DataLoop

## Pourquoi le deployement cassait

Le backend `DataLoop` etait configure avec de mauvaises variables MySQL sur Railway:

- `DB_USERNAME` pointait vers le mot de passe
- `DB_PASSWORD` etait vide
- le service MySQL Railway expose en realite `MYSQLUSER=root`

Cela provoquait l'erreur:

```text
SQLSTATE[HY000] [1045] Access denied
```

## Variables correctes a utiliser sur le service backend

Dans le service Railway `DataLoop`, utiliser ces references vers le service `MySQL`:

```env
APP_NAME=DataLoop
APP_ENV=production
APP_DEBUG=false
APP_URL=https://dataloop-production.up.railway.app/

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
DB_URL=${{MySQL.MYSQL_URL}}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database
```

## Variables MySQL observees sur Railway

Le service `MySQL` Railway expose actuellement:

```env
MYSQLDATABASE=railway
MYSQLHOST=mysql.railway.internal
MYSQLPORT=3306
MYSQLUSER=root
MYSQL_URL=${{MySQL.MYSQL_URL}}
```

Le mot de passe doit venir de:

```env
MYSQLPASSWORD=${{MySQL.MYSQLPASSWORD}}
```

## Variables a ne pas utiliser pour la connexion DB

Ne pas confondre avec:

```env
RAILWAY_TOKEN=...
```

`RAILWAY_TOKEN` sert pour:

- la CLI Railway
- l'API Railway
- les scripts d'automatisation Railway

`RAILWAY_TOKEN` ne sert pas pour:

- `DB_PASSWORD`
- `DB_USERNAME`
- la connexion Laravel vers MySQL

## Bonnes pratiques

- Ne plus versionner `.env` ni `.env.local`
- Garder uniquement `.env.example` sans secrets reels
- Utiliser les references Railway entre services au lieu de recopier les secrets a la main

## Apres mise a jour des variables

Railway redeploie automatiquement le service. Si besoin, relancer ensuite:

```bash
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
```
