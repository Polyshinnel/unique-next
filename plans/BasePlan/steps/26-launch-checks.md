# Шаг 6.1 — Проверки запуска

**Этап:** 6. Финальная проверка и документация  
**Статус:** [x] Выполнен

## Описание

Комплексная проверка работоспособности всей инфраструктуры. Все тесты должны пройти перед переходом к документации.

## Чеклист проверок

### 1. Docker Compose — все контейнеры поднимаются

```bash
docker compose up -d --build
docker compose ps
```

**Ожидание:** все 4 контейнера в статусе `Up`:
- `uniqset2_app`
- `uniqset2_scheduler`
- `uniqset2_db`
- `uniqset2_redis`

### 2. Миграции проходят на MySQL

```bash
docker compose exec app php artisan migrate
```

**Ожидание:** миграции применяются без ошибок.

### 3. Health-check эндпоинт

```bash
curl http://localhost:28080/api/health
```

**Ожидание:** JSON-ответ с `"status": "ok"` и все сервисы `"ok"`.

### 4. Next.js SSR через nginx

Открыть в браузере: `http://localhost:28080`

**Ожидание:** Next.js страница рендерится корректно (SSR через nginx proxy к процессу Next.js внутри `app`).

### 5. Next.js dev server

```bash
docker compose exec app supervisorctl status next
```

**Ожидание:** процесс `next` в статусе `RUNNING`; HMR работает через `http://localhost:28080`.

### 6. Horizon (Supervisor)

```bash
docker compose exec app supervisorctl status
```

**Ожидание:** все четыре процесса `RUNNING`:
- `php-fpm`
- `nginx`
- `next`
- `horizon`

Проверить логи: `docker compose exec app cat /var/www/html/storage/logs/horizon.log`

### 7. Redis (кэш/сессии)

```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan tinker --execute="Cache::put('test', 'ok', 60); echo Cache::get('test');"
```

**Ожидание:** `ok` — кэш работает через Redis.

## Устранение проблем

| Проблема | Проверить |
|---|---|
| Контейнер не стартует | `docker compose logs <service>` |
| MySQL connection refused | `docker compose logs db`, проверить `.env` credentials |
| Redis connection refused | `docker compose logs redis`, проверить `REDIS_HOST` |
| Nginx 502 | PHP-FPM не запущен или локальный Next.js-процесс не отвечает |
| Next.js не работает | Проверить `resources/js/app/layout.tsx`, корневой `package.json`, Node.js версию и `storage/logs/next.log` |

## Зависимости

- Все предыдущие шаги (1.1–5.3)

## Критерий завершения

Все 7 проверок пройдены успешно.

## Проверка выполнения

- `docker compose up -d --build` завершился успешно, все 4 контейнера в статусе `Up`
- `php artisan migrate --force` внутри `app` завершился без ошибок; повторный прогон сообщает `Nothing to migrate`
- `curl http://localhost:28080/api/health` возвращает `{"status":"ok",...}` со статусами `database`, `redis`, `cache` = `ok`
- `http://localhost:28080` отвечает `200 OK`, SSR-страница Next.js рендерится через nginx
- `docker compose exec app supervisorctl status` показывает `php-fpm`, `nginx`, `next`, `horizon` в статусе `RUNNING`
- Redis-кэш подтверждён через `Cache::put(...); Cache::get(...)` с ответом `ok`

## Исправления по итогам проверки

- В [docker-compose.yml](/home/andrey/projects/uniqset2.com/docker-compose.yml:1) добавлены корректные Docker-host значения для `DB_HOST`/`REDIS_HOST` и network aliases `mysql`/`redis`, чтобы Laravel внутри контейнера видел MySQL и Redis без ручной правки `.env`
- В [docker/supervisor/supervisord.conf](/home/andrey/projects/uniqset2.com/docker/supervisor/supervisord.conf:1) добавлены `unix_http_server`, `rpcinterface` и `supervisorctl`, а также выровнено имя сокета `supervisord.sock`
- В [composer.json](/home/andrey/projects/uniqset2.com/composer.json:1) добавлен пакет `laravel/horizon`, потому что launch-конфигурация уже ожидала реальный процесс Horizon
- В [resources/js/app/page.tsx](/home/andrey/projects/uniqset2.com/resources/js/app/page.tsx:1) исправлен SSR-рендер списка сервисов, чтобы главная страница стабильно отвечала `200 OK`
