# BasePlan — Docker + Next.js (SSR) + Mantine UI

## Обзор текущего окружения

| Компонент       | Версия / Значение                  |
|-----------------|------------------------------------|
| PHP             | ^8.4 (в Docker), ^8.3 (composer)   |
| Laravel         | ^13.8                              |
| Node.js         | Node.js 22, Next.js 15, Mantine UI |
| БД              | SQLite (переводим на MySQL)        |
| Очередь         | database (переводим на Redis)      |
| Кэш             | database (переводим на Redis)      |
| Redis           | Конфиг присутствует, не активен    |

### Занятые порты на хосте

| Порт  | Сервис                           | Статус      |
|-------|----------------------------------|-------------|
| 3306  | supply_db (MySQL)                | **работает** |
| 3307  | potolok-mysql                    | **работает** |
| 3308  | complectkidsberryorg-db          | **работает** |
| 5173  | supply_app (Vite)                | **работает** |
| 6379  | supply_redis                     | **работает** |
| 8081  | supply_app (nginx)               | **работает** |
| 13316 | uniqset.com mysql                | остановлен  |
| 15173 | uniqset.com vite                 | остановлен  |
| 16380 | uniqset.com redis                | остановлен  |
| 18080 | uniqset.com nginx                | остановлен  |

### Выбранные порты для uniqset2.com

| Порт  | Сервис           |
|-------|------------------|
| 28080 | nginx (HTTP)     |
| 23306 | mysql            |
| 26379 | redis            |

> Все порты конфигурируются через `.env` и могут быть переопределены.

## Целевая архитектура

> Паттерн взят из `supply.kidsberry.org` — монолитный контейнер `app` с PHP-FPM + Nginx + Supervisor внутри.

```
┌─────────────────────────────────────────────────────────────────┐
│ docker-compose (uniqset2_local)                                  │
│                                                                  │
│ ┌───────────────────────────────────────────────────────────────┐│
│ │ app (модульный монолит)                                      ││
│ │ nginx :80 -> host :28080                                     ││
│ │ php-fpm :9000                                                ││
│ │ next :3000 (только внутри контейнера)                         ││
│ │ horizon                                                      ││
│ │ supervisor управляет всеми процессами                         ││
│ └───────────────────────────┬───────────────────────────────────┘│
│                             │                                    │
│              ┌──────────────┴──────────────┐                     │
│              │                             │                     │
│        ┌─────┴─────┐                 ┌─────┴─────┐               │
│        │ mysql     │                 │ redis     │               │
│        │ :23306    │                 │ :26379    │               │
│        └───────────┘                 └───────────┘               │
└─────────────────────────────────────────────────────────────────┘
```

- **app** — монолитный контейнер (как в supply): PHP 8.4 FPM + Nginx + Node.js 22 + Supervisor. Supervisor запускает `php-fpm`, `nginx`, `next`, `horizon`. Порт **28080** → HTTP (nginx). Next.js слушает `127.0.0.1:3000` только внутри контейнера.
- **scheduler** — тот же образ, команда `php artisan schedule:work` (по аналогии с supply prod)
- **mysql** — MySQL 8.4 (порт **23306**)
- **redis** — Redis 7.x (порт **26379**) — кэш, очереди, сессии

---

## Этапы реализации

### Этап 1. Docker-инфраструктура

#### 1.1 Создать структуру файлов Docker

```
Dockerfile                        # Монолитный: PHP 8.4 FPM + Nginx + Node.js 22 + Supervisor
.dockerignore
docker/
├── entrypoint.sh                 # Права, storage dirs, опциональные runtime-задачи
├── nginx/
│   └── default.conf              # Nginx конфиг (fastcgi → 127.0.0.1:9000, proxy → 127.0.0.1:3000)
├── php/
│   ├── php.ini                   # memory_limit, upload, opcache
│   └── www.conf                  # PHP-FPM pool config
├── supervisor/
│   └── supervisord.conf          # php-fpm + nginx + next + horizon
└── mysql/
    └── my.cnf                    # utf8mb4, strict mode
```

#### 1.2 Dockerfile (монолитный, в корне проекта)

Базовый образ: `php:8.4-fpm-alpine` (как supply, но обновлённая версия)

```dockerfile
# Этап 1: Сборка фронтенд-ассетов
FROM node:22-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi
COPY . .
RUN npm run build \
    && mkdir -p /app/resources/js/.next

# Этап 2: PHP приложение
FROM php:8.4-fpm-alpine AS php-base

RUN apk add --no-cache \
    git curl curl-dev libcurl \
    libpng-dev libzip-dev zip unzip \
    oniguruma-dev icu-dev icu-libs \
    freetype-dev libjpeg-turbo-dev \
    nginx supervisor shadow su-exec \
    nodejs npm

# PHP расширения
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql bcmath exif gd intl \
        opcache pcntl zip mbstring

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

COPY . .
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# Копируем Next.js runtime-зависимости и build output
COPY --from=frontend-builder /app/node_modules ./node_modules
COPY --from=frontend-builder /app/resources/js/.next ./resources/js/.next

# Права
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Конфиги
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

#### 1.3 `docker/entrypoint.sh` (по образцу supply)

```bash
#!/bin/sh
set -e

sync_www_data_ids() {
    if [ "$(id -u)" != "0" ]; then return; fi
    target_uid="${WWWUSER:-}"; target_gid="${WWWGROUP:-}"
    if [ -z "$target_uid" ] || [ -z "$target_gid" ]; then return; fi
    current_uid="$(id -u www-data)"; current_gid="$(id -g www-data)"
    [ "$current_gid" != "$target_gid" ] && groupmod -o -g "$target_gid" www-data
    [ "$current_uid" != "$target_uid" ] && usermod -o -u "$target_uid" -g "$target_gid" www-data
}

ensure_laravel_permissions() {
    mkdir -p /var/www/html/storage/logs /var/www/html/bootstrap/cache
    touch /var/www/html/storage/logs/laravel.log /var/www/html/storage/logs/horizon.log /var/www/html/storage/logs/next.log
    if [ "$(id -u)" = "0" ]; then
        chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
    fi
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
}

sync_www_data_ids
ensure_laravel_permissions

[ "${RUN_COMPOSER_INSTALL:-}" = "true" ] && composer install
[ "${RUN_NPM_BUILD:-}" = "true" ] && npm install && npm run build
[ "${RUN_STORAGE_LINK:-}" = "true" ] && php artisan storage:link

if [ "$(id -u)" = "0" ] && [ "${1:-}" = "php" ] && [ "${2:-}" = "artisan" ]; then
    exec su-exec www-data "$@"
fi

exec "$@"
```

#### 1.4 `docker/supervisor/supervisord.conf`

```ini
[supervisord]
nodaemon=true
user=root

[program:php-fpm]
command=/usr/local/sbin/php-fpm --nodaemonize
autostart=true
autorestart=true
stderr_logfile=/var/log/php-fpm.err.log
stdout_logfile=/var/log/php-fpm.out.log

[program:nginx]
command=/usr/sbin/nginx -g 'daemon off;'
autostart=true
autorestart=true
stderr_logfile=/var/log/nginx.err.log
stdout_logfile=/var/log/nginx.out.log

[program:next]
command=/bin/sh -c 'while [ ! -f /var/www/html/resources/js/app/layout.tsx ]; do echo "Waiting for Next.js app in resources/js..."; sleep 10; done; npm install && npm run dev -- --hostname 0.0.0.0 --port 3000'
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/next.log

[program:horizon]
process_name=%(program_name)s
command=php /var/www/html/artisan horizon
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/horizon.log
stopwaitsecs=3600
```

#### 1.5 `docker/nginx/default.conf`

```nginx
server {
    listen 80;
    server_name _;
    root /var/www/html/public;
    index index.php;

    charset utf-8;
    client_max_body_size 64M;

    # Laravel: всё что начинается с /api, /sanctum, /horizon, /telescope, /_ignition
    location ~ ^/(api|sanctum|horizon|telescope|_ignition)(/.*)? {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ^~ /_next/ {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Next.js SSR — всё остальное проксируем в локальный процесс внутри app
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

}
```

#### 1.6 `docker/php/php.ini`

```ini
[PHP]
memory_limit = 256M
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
max_input_time = 300

[opcache]
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

#### 1.7 `docker/php/www.conf`

```ini
[www]
user = www-data
group = www-data
listen = 127.0.0.1:9000
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
```

#### 1.8 `docker/mysql/my.cnf`

```ini
[client]
default-character-set = utf8mb4

[mysqld]
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
innodb_file_per_table = 1
max_connections = 100
sql_mode = STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION
```

#### 1.9 Написать `docker-compose.yml` (local dev)

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
      network: host
    container_name: uniqset2_app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
      - ./docker/php/php.ini:/usr/local/etc/php/conf.d/custom.ini
      - ./docker/nginx/default.conf:/etc/nginx/http.d/default.conf
    ports:
      - "${APP_HTTP_PORT:-28080}:80"
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
      - BACKEND_URL=http://127.0.0.1
      - NEXT_PUBLIC_API_URL=/api
      - REDIS_HOST=uniqset2-redis
      - WWWUSER=${UID:-1000}
      - WWWGROUP=${GID:-1000}
    networks:
      - uniqset2_network
    depends_on:
      - db
      - redis

  scheduler:
    build:
      context: .
      dockerfile: Dockerfile
      network: host
    container_name: uniqset2_scheduler
    restart: unless-stopped
    working_dir: /var/www/html
    command: php artisan schedule:work
    volumes:
      - ./:/var/www/html
    environment:
      - REDIS_HOST=uniqset2-redis
    networks:
      - uniqset2_network
    depends_on:
      - db
      - redis

  db:
    image: mysql:8.4
    container_name: uniqset2_db
    restart: unless-stopped
    command: ["--defaults-file=/etc/mysql/conf.d/my.cnf"]
    environment:
      MYSQL_DATABASE: ${DB_DATABASE:-uniqset2}
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD:-root}
      MYSQL_PASSWORD: ${DB_PASSWORD:-root}
      MYSQL_USER: ${DB_USERNAME:-uniqset2}
    ports:
      - "${MYSQL_HOST_PORT:-23306}:3306"
    volumes:
      - db_data:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf:ro
    networks:
      - uniqset2_network

  redis:
    image: redis:7-alpine
    container_name: uniqset2_redis
    restart: unless-stopped
    command: ["redis-server", "--appendonly", "yes"]
    ports:
      - "${REDIS_HOST_PORT:-26379}:6379"
    volumes:
      - redis_data:/data
    networks:
      uniqset2_network:
        aliases:
          - uniqset2-redis

volumes:
  db_data:
  redis_data:

networks:
  uniqset2_network:
    driver: bridge
```

#### 1.10 `.dockerignore`

```
node_modules
vendor
.git
.gitignore
.env
.env.backup
.phpunit.result.cache
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
bootstrap/cache/*
npm-debug.log
.DS_Store
docker-compose.yml
docker-compose.prod.yml
README.md
tests
phpunit.xml
resources/js/.next
resources/js/.env.local
```

#### 1.11 `docker-compose.prod.yml`

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
      network: host
    container_name: uniqset2_app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - storage_public_data:/var/www/html/storage/app/public
    ports:
      - "${APP_HTTP_PORT:-28080}:80"
    environment:
      RUN_STORAGE_LINK: "true"
      REDIS_HOST: uniqset2-redis
      REDIS_PORT: "6379"
      REDIS_PASSWORD: ""
    networks:
      - uniqset2_network
    depends_on:
      - db
      - redis

  scheduler:
    build:
      context: .
      dockerfile: Dockerfile
      network: host
    container_name: uniqset2_scheduler
    restart: unless-stopped
    working_dir: /var/www/html
    command: php artisan schedule:work
    volumes:
      - storage_public_data:/var/www/html/storage/app/public
    environment:
      REDIS_HOST: uniqset2-redis
    networks:
      - uniqset2_network
    depends_on:
      - db
      - redis

  db:
    image: mysql:8.4
    container_name: uniqset2_db
    restart: unless-stopped
    command: ["--defaults-file=/etc/mysql/conf.d/my.cnf"]
    environment:
      MYSQL_DATABASE: ${DB_DATABASE:-uniqset2}
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD:-root}
      MYSQL_PASSWORD: ${DB_PASSWORD:-root}
      MYSQL_USER: ${DB_USERNAME:-uniqset2}
    volumes:
      - db_data:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf:ro
    networks:
      - uniqset2_network

  redis:
    image: redis:7-alpine
    container_name: uniqset2_redis
    restart: unless-stopped
    command: ["redis-server", "--appendonly", "yes"]
    volumes:
      - redis_data:/data
    networks:
      uniqset2_network:
        aliases:
          - uniqset2-redis

volumes:
  storage_public_data:
  db_data:
  redis_data:

networks:
  uniqset2_network:
    driver: bridge
```

#### 1.12 Обновить `.env.example`

Добавить в конец:
```env
# Docker ports (host)
APP_HTTP_PORT=28080
MYSQL_HOST_PORT=23306
REDIS_HOST_PORT=26379
```

### Работа с Next.js из контейнера

Контейнер `app` содержит Node.js 22 + npm (установлены через apk). Next.js-приложение лежит в `resources/js`, а npm-команды запускаются из корня проекта:

```bash
# Подключиться к контейнеру
docker compose exec app sh

# Внутри контейнера:
npm install
npm run dev -- --hostname 0.0.0.0 --port 3000
npm run build
```

Наружу публикуется только nginx: `http://localhost:28080`. Nginx проксирует frontend-запросы в локальный Next.js-процесс `127.0.0.1:3000`.

---

### Этап 2. Настройка Laravel под MySQL + Redis

#### 2.1 Обновить `.env.example`

- `DB_CONNECTION=mysql`, `DB_HOST=mysql`, `DB_PORT=3306`, `DB_DATABASE=uniqset2`, `DB_USERNAME=uniqset2`, `DB_PASSWORD=secret`
- `QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`, `SESSION_DRIVER=redis`
- `REDIS_HOST=redis`

#### 2.2 Установить PHP-расширение Redis для Laravel

```bash
composer require predis/predis
# или использовать phpredis (уже будет в Docker-образе)
```

> Предпочтительно `phpredis` — уже указан в `.env.example` как `REDIS_CLIENT=phpredis`. Расширение ставится в Dockerfile.

#### 2.3 Проверить конфигурацию

- `config/database.php` — убедиться что `mysql` connection корректен (уже есть из коробки Laravel)
- `config/cache.php` — Redis store настроен (есть из коробки)
- `config/queue.php` — Redis connection настроен (есть из коробки)
- `config/session.php` — поддержка Redis driver (есть из коробки)

#### 2.4 Добавить health-check эндпоинт

Создать `GET /api/health` — возвращает JSON со статусом подключений (`database`, `redis`, `cache`) и `503`, если хотя бы один сервис недоступен.

---

### Этап 3. Инициализация Next.js приложения

#### 3.1 Инициализировать Next.js в `resources/js`

```bash
npm install next react react-dom
npm install -D typescript @types/react @types/react-dom @types/node eslint eslint-config-next
```

Корневой `package.json`:

```json
{
  "scripts": {
    "dev": "next dev resources/js",
    "build": "next build resources/js",
    "start": "next start resources/js"
  }
}
```

Структура:
```
resources/js/
├── app/
│   ├── layout.tsx
│   ├── page.tsx
│   └── providers.tsx      # MantineProvider, тема
├── components/
├── lib/
│   └── api.ts             # HTTP-клиент для Laravel API
├── styles/
├── public/
├── next.config.ts
└── tsconfig.json
```

#### 3.2 Настроить `next.config.ts`

```typescript
const nextConfig = {
  output: 'standalone',           // для Docker
  reactStrictMode: true,
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: `${process.env.BACKEND_URL || 'http://127.0.0.1'}/api/:path*`,
      },
    ];
  },
};

export default nextConfig;
```

- `output: 'standalone'` — обязательно для Docker (минимальный self-contained build)
- `rewrites` — проксирование `/api/*` запросов к Laravel внутри того же контейнера (для SSR, серверные компоненты)

---

### Этап 4. Интеграция Mantine UI

#### 4.1 Установить пакеты Mantine

```bash
npm install @mantine/core @mantine/hooks @mantine/form @mantine/notifications @mantine/modals
npm install postcss postcss-preset-mantine postcss-simple-vars
```

#### 4.2 Настроить PostCSS

`resources/js/postcss.config.mjs`:
```javascript
export default {
  plugins: {
    'postcss-preset-mantine': {},
    'postcss-simple-vars': {
      variables: {
        'mantine-breakpoint-xs': '36em',
        'mantine-breakpoint-sm': '48em',
        'mantine-breakpoint-md': '62em',
        'mantine-breakpoint-lg': '75em',
        'mantine-breakpoint-xl': '88em',
      },
    },
  },
};
```

#### 4.3 Создать `providers.tsx` с MantineProvider

```tsx
'use client';

import { MantineProvider, createTheme } from '@mantine/core';
import { Notifications } from '@mantine/notifications';
import { ModalsProvider } from '@mantine/modals';
import '@mantine/core/styles.css';
import '@mantine/notifications/styles.css';

const theme = createTheme({
  primaryColor: 'blue',
  fontFamily: 'Inter, sans-serif',
});

export function Providers({ children }: { children: React.ReactNode }) {
  return (
    <MantineProvider theme={theme} defaultColorScheme="auto">
      <ModalsProvider>
        <Notifications position="top-right" />
        {children}
      </ModalsProvider>
    </MantineProvider>
  );
}
```

#### 4.4 Обновить `layout.tsx`

```tsx
import { ColorSchemeScript } from '@mantine/core';
import { Providers } from './providers';

export const metadata = {
  title: 'Uniqset',
  description: 'Uniqset application',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="ru" suppressHydrationWarning>
      <head>
        <ColorSchemeScript />
      </head>
      <body>
        <Providers>{children}</Providers>
      </body>
    </html>
  );
}
```

---

### Этап 5. Связка Laravel API ↔ Next.js

#### 5.1 Laravel: настроить API-маршруты

- Создать `routes/api.php` (если отсутствует, установить `php artisan install:api`)
- Настроить Laravel Sanctum для SPA-аутентификации
- Добавить CORS-конфигурацию для `http://localhost:28080` и production-домен

#### 5.2 Next.js: создать HTTP-клиент

`resources/js/lib/api.ts` — обёртка над `fetch` / `axios`:
- Базовый URL по умолчанию `/api` (same-origin через nginx)
- Автоматическое добавление CSRF-токена (Sanctum)
- Обработка ошибок и типизация ответов

#### 5.3 Паттерн SSR ↔ API

- **Серверные компоненты** (RSC): запросы к Laravel через локальный nginx внутри контейнера (`http://127.0.0.1/api/...`)
- **Клиентские компоненты**: запросы через браузер к `/api/...` (прокси через nginx)

---

### Этап 6. Финальная проверка и документация

#### 6.1 Проверки запуска

1. `docker compose up -d --build` — все контейнеры поднимаются без ошибок
2. `docker compose exec app php artisan migrate` — миграции проходят на MySQL
3. `http://localhost:28080/api/health` — JSON-ответ от Laravel
4. `http://localhost:28080` — Next.js SSR-страница через nginx-прокси
5. `docker compose exec app supervisorctl status next` — Next.js процесс запущен внутри `app`, HMR работает через `http://localhost:28080`
6. Horizon работает (supervisor), логи в `storage/logs/horizon.log`
7. Кэш/сессии через Redis: `php artisan cache:clear`

#### 6.2 Обновить README.md

- Описание архитектуры (схема из плана)
- Быстрый старт: `docker compose up -d --build`
- Полезные команды: `docker compose exec app sh`, `npm run dev`, `php artisan migrate`
- Переменные окружения и порты

#### 6.3 Обновить `.gitignore`

Добавить:
```
# Next.js
resources/js/.next/
resources/js/.env.local
```

---

## Порядок выполнения (чеклист)

- [ ] **1.1–1.12** — Docker-инфраструктура (Dockerfile, docker-compose.yml/prod.yml, конфиги supervisor/nginx/php/mysql, entrypoint, .dockerignore)
- [x] **2.1–2.4** — Настройка Laravel (MySQL, Redis, health-check)
- [ ] **3.1–3.2** — Инициализация Next.js в `resources/js`
- [ ] **4.1–4.4** — Подключение Mantine UI
- [ ] **5.1–5.3** — Связка Laravel API ↔ Next.js (маршруты, HTTP-клиент, CORS)
- [ ] **6.1–6.3** — Проверка, README, .gitignore
