# uniqset2.com

Laravel 13 + Next.js 16 приложение в Docker-окружении с монолитным контейнером `app`, внутри которого вместе работают PHP-FPM, nginx, Next.js dev server и Horizon под управлением Supervisor.

## Архитектура

```text
┌─────────────────────────────────────────────────────────────────┐
│ docker compose (uniqset2_local)                                │
│                                                                 │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ app                                                        │ │
│ │ nginx :80 -> host :28080                                   │ │
│ │ php-fpm :9000                                              │ │
│ │ next :3000 (только внутри контейнера)                      │ │
│ │ horizon                                                    │ │
│ │ supervisor управляет всеми процессами                      │ │
│ └──────────────────────────┬──────────────────────────────────┘ │
│                            │                                    │
│             ┌──────────────┴──────────────┐                     │
│             │                             │                     │
│       ┌─────┴─────┐                 ┌─────┴─────┐               │
│       │ db        │                 │ redis     │               │
│       │ :23306    │                 │ :26379    │               │
│       └───────────┘                 └───────────┘               │
└─────────────────────────────────────────────────────────────────┘
```

### Контейнеры

- `app` — основной контейнер: PHP 8.4 FPM, nginx, Node.js 22, Next.js, Horizon и Supervisor; наружу публикуется HTTP-порт `28080`
- `scheduler` — отдельный контейнер на том же образе с командой `php artisan schedule:work`
- `db` — MySQL 8.4 с хранением данных в volume `db_data`
- `redis` — Redis 7 с AOF persistence в volume `redis_data`

### Технологический стек

- Laravel 13
- Next.js 16
- React 19
- Mantine UI 9
- MySQL 8.4
- Redis 7
- Docker Compose

### SSR и API

- SSR-запросы из Next.js идут внутри контейнера `app` на `http://127.0.0.1/api` через `BACKEND_URL`
- Браузерные запросы идут на `/api/...` через same-origin nginx
- Health-check доступен по `GET /api/health`

## Быстрый старт

```bash
git clone <repo-url>
cd uniqset2.com

cp .env.example .env

docker compose up -d --build

docker compose exec app php artisan migrate --force

# Открыть в браузере
# http://localhost:28080
```

После старта можно проверить API:

```bash
curl http://localhost:28080/api/health
```

## Полезные команды

```bash
# Контейнеры и логи
docker compose ps
docker compose logs -f app
docker compose logs -f scheduler

# Подключиться к app
docker compose exec app sh

# Artisan
docker compose exec app php artisan migrate
docker compose exec app php artisan tinker
docker compose exec app php artisan cache:clear
docker compose exec app php artisan queue:restart

# Supervisor
docker compose exec app supervisorctl status
docker compose exec app supervisorctl status next

# Frontend
docker compose exec app npm run dev -- --hostname 0.0.0.0 --port 3000
docker compose exec app npm run build

# Проверки
curl http://localhost:28080/api/health
docker compose exec app php artisan tinker --execute="Cache::put('test', 'ok', 60); echo Cache::get('test');"
```

## Импорт товаров и заполнение данных

### Импорт и обновление товаров

```bash
# Импорт товаров из XML-фида в очередь
docker compose exec app php artisan catalog:import-products

# Импорт товаров синхронно с выводом прогресса в консоль
docker compose exec app php artisan catalog:import-products --sync

# Импорт товаров с обновлением изменившихся полей существующих записей
docker compose exec app php artisan catalog:import-products --update-existing

# Импорт/обновление из произвольного фида
docker compose exec app php artisan catalog:import-products --url="https://example.com/feed.xml" --sync

# Быстрое обновление существующих товаров: статусы и цены
docker compose exec app php artisan catalog:update-existing-products

# Быстрое обновление существующих товаров синхронно
docker compose exec app php artisan catalog:update-existing-products --sync

# Полное обновление существующих товаров: поля и фото
docker compose exec app php artisan catalog:update-revision-products

# Полное обновление существующих товаров синхронно
docker compose exec app php artisan catalog:update-revision-products --sync
```

### Пользователь для Filament

```bash
docker compose exec app php artisan make:filament-user
```

Команда интерактивно попросит указать имя, email и пароль пользователя для входа в админ-панель Filament.

### Сиды базы данных

```bash
# Запустить все сиды из DatabaseSeeder
docker compose exec app php artisan db:seed

# Пересоздать таблицы, прогнать миграции и заново заполнить данными
docker compose exec app php artisan migrate:fresh --seed
```

## Переменные окружения и порты

Основные значения для локального запуска уже описаны в `.env.example`.

| Переменная | Значение по умолчанию | Назначение |
|---|---:|---|
| `APP_HTTP_PORT` | `28080` | HTTP nginx на хосте |
| `MYSQL_HOST_PORT` | `23306` | MySQL на хосте |
| `REDIS_HOST_PORT` | `26379` | Redis на хосте |
| `DB_HOST` | `mysql` | имя MySQL-сервиса внутри Docker-сети |
| `REDIS_HOST` | `redis` | имя Redis-сервиса внутри Docker-сети |
| `FRONTEND_URL` | `http://localhost:28080` | frontend origin для Sanctum/CORS |

| Порт | Сервис |
|---|---|
| `28080` | nginx (HTTP) |
| `23306` | MySQL |
| `26379` | Redis |

## Production деплой

Production-конфигурация описана в [`docker-compose.prod.yml`](./docker-compose.prod.yml). Базовый запуск:

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

Особенности production-сборки:

- `app` и `scheduler` используют тот же `Dockerfile`
- статические и runtime-файлы для `storage/app/public` вынесены в volume `storage_public_data`
- наружу публикуется только HTTP-порт приложения

## Что проверить после запуска

- `docker compose ps` показывает `app`, `scheduler`, `db`, `redis` в статусе `Up`
- `docker compose exec app supervisorctl status` показывает `php-fpm`, `nginx`, `next`, `horizon` в статусе `RUNNING`
- `http://localhost:28080` открывает SSR-страницу Next.js
- `http://localhost:28080/api/health` возвращает статус `ok` или диагностический `degraded`
