# Dokploy

## Container Port

Для этого проекта в `Dokploy -> Container Port` нужно указать:

```text
80
```

Причина: внутри контейнера наружу смотрит `nginx` на порту `80`. `Next.js` работает внутри контейнера на `3000`, но напрямую наружу не публикуется.

## Environment Settings

Ниже список переменных, которые понадобятся для `app`-ресурса в Dokploy.

## Готовый `.env` блок для `deploy-server.ru`

Ниже уже собранный production-блок под домен `https://deploy-server.ru`.

Важно: здесь я принял допущение, что ресурсы в Dokploy будут называться `uniqset2-db` и `uniqset2-redis`.
Если назовёшь их иначе, поменяй только `DB_HOST` и `REDIS_HOST`.

```env
APP_NAME=uniqset2.com
APP_ENV=production
APP_KEY=base64:f1sq8SAkMuZNdyqaqobMIsV/JZFdnxrGZJlbCaG31hk=
APP_DEBUG=false
APP_URL=https://deploy-server.ru
FRONTEND_URL=https://deploy-server.ru

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=uniqset2-db
DB_PORT=3306
DB_DATABASE=uniqset2
DB_USERNAME=uniqset2
DB_PASSWORD=CHANGE_ME_DB_PASSWORD

SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SAME_SITE=lax
SESSION_SECURE_COOKIE=true

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis

CACHE_STORE=redis

REDIS_CLIENT=phpredis
REDIS_HOST=uniqset2-redis
REDIS_PASSWORD=null
REDIS_PORT=6379

SANCTUM_STATEFUL_DOMAINS=deploy-server.ru

BACKEND_URL=http://127.0.0.1
NEXT_PUBLIC_API_URL=/api

HORIZON_BASIC_AUTH_USERNAME=admin
HORIZON_BASIC_AUTH_PASSWORD=CHANGE_ME_HORIZON_PASSWORD

CATALOG_IMPORT_FEED_URL=https://panel.uniqset.com/storage/exports/advertisements.xml
CATALOG_IMPORT_IMAGE_DISK=public
CATALOG_IMPORT_HTTP_TIMEOUT=60
CATALOG_IMPORT_QUEUE=imports
CATALOG_IMPORT_UPDATE_EXISTING=false

RUN_STORAGE_LINK=true
```

### Обязательные

```env
APP_NAME=uniqset2.com
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:CHANGE_ME
APP_URL=https://your-domain.com
FRONTEND_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-mysql-service-name
DB_PORT=3306
DB_DATABASE=uniqset2
DB_USERNAME=uniqset2
DB_PASSWORD=CHANGE_ME

REDIS_CLIENT=phpredis
REDIS_HOST=your-redis-service-name
REDIS_PORT=6379
REDIS_PASSWORD=null

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

SANCTUM_STATEFUL_DOMAINS=your-domain.com

BACKEND_URL=http://127.0.0.1
NEXT_PUBLIC_API_URL=/api

RUN_STORAGE_LINK=true
```

### Нужны почти всегда

```env
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SAME_SITE=lax
SESSION_SECURE_COOKIE=true

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

HORIZON_BASIC_AUTH_USERNAME=admin
HORIZON_BASIC_AUTH_PASSWORD=CHANGE_ME
```

### Опционально

Если нужны почта, карты, S3 или импорт каталога:

```env
MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS=hello@your-domain.com
MAIL_FROM_NAME=uniqset2.com

YANDEX_MAPS_API_KEY=

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

CATALOG_IMPORT_FEED_URL=https://panel.uniqset.com/storage/exports/advertisements.xml
CATALOG_IMPORT_IMAGE_DISK=public
CATALOG_IMPORT_HTTP_TIMEOUT=60
CATALOG_IMPORT_QUEUE=imports
CATALOG_IMPORT_UPDATE_EXISTING=false
```

## Что подставить в `DB_HOST` и `REDIS_HOST`

- Если MySQL и Redis создаются как отдельные ресурсы в Dokploy, укажи их внутренние имена сервисов.
- Если имена ресурсов будут, например, `uniqset2-db` и `uniqset2-redis`, то значения будут:

```env
DB_HOST=uniqset2-db
REDIS_HOST=uniqset2-redis
```

## Что важно не забыть

- `APP_KEY` должен быть настоящим ключом Laravel, не оставляй `CHANGE_ME`.
- `APP_URL` и `FRONTEND_URL` должны совпадать с боевым доменом.
- `SANCTUM_STATEFUL_DOMAINS` указывай без `https://`, только домен. Пример: `your-domain.com`.
- `BACKEND_URL=http://127.0.0.1` оставляем именно так, потому что SSR-запросы Next.js идут внутри того же контейнера через `nginx`.
- Если в Dokploy ты настраиваешь порт через UI, `APP_HTTP_PORT` обычно не нужен.

## Минимальный рабочий набор

Если нужен самый короткий набор для первого старта:

```env
APP_NAME=uniqset2.com
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:CHANGE_ME
APP_URL=https://your-domain.com
FRONTEND_URL=https://your-domain.com
DB_CONNECTION=mysql
DB_HOST=your-mysql-service-name
DB_PORT=3306
DB_DATABASE=uniqset2
DB_USERNAME=uniqset2
DB_PASSWORD=CHANGE_ME
REDIS_CLIENT=phpredis
REDIS_HOST=your-redis-service-name
REDIS_PORT=6379
REDIS_PASSWORD=null
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SANCTUM_STATEFUL_DOMAINS=your-domain.com
BACKEND_URL=http://127.0.0.1
NEXT_PUBLIC_API_URL=/api
RUN_STORAGE_LINK=true
HORIZON_BASIC_AUTH_USERNAME=admin
HORIZON_BASIC_AUTH_PASSWORD=CHANGE_ME
```
