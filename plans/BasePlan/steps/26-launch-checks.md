# Шаг 6.1 — Проверки запуска

**Этап:** 6. Финальная проверка и документация  
**Статус:** [ ] Не выполнен

## Описание

Комплексная проверка работоспособности всей инфраструктуры. Все тесты должны пройти перед переходом к документации.

## Чеклист проверок

### 1. Docker Compose — все контейнеры поднимаются

```bash
docker compose up -d --build
docker compose ps
```

**Ожидание:** все 5 контейнеров в статусе `Up`:
- `uniqset2_app`
- `uniqset2_next`
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

**Ожидание:** Next.js страница рендерится корректно (SSR через nginx proxy к контейнеру `next`).

### 5. Vite HMR

```bash
docker compose exec app sh
npm run dev
```

Открыть: `http://localhost:25173`

**Ожидание:** Vite dev server работает, HMR обновляет страницу при изменении файлов.

### 6. Horizon (Supervisor)

```bash
docker compose exec app supervisorctl status
```

**Ожидание:** все три процесса `RUNNING`:
- `php-fpm`
- `nginx`
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
| Nginx 502 | PHP-FPM не запущен или Next.js не отвечает |
| Vite не работает | Node.js версия, проверить `npm install` |

## Зависимости

- Все предыдущие шаги (1.1–5.3)

## Критерий завершения

Все 7 проверок пройдены успешно.
