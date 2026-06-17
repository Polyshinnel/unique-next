# BasePlan — Оглавление шагов

## Этап 1. Docker-инфраструктура

| # | Файл | Описание | Статус |
|---|---|---|---|
| 1.1 | [01-docker-file-structure.md](01-docker-file-structure.md) | Создать структуру файлов Docker | [ ] |
| 1.2 | [02-dockerfile.md](02-dockerfile.md) | Dockerfile (монолитный, multi-stage) | [ ] |
| 1.3 | [03-entrypoint-sh.md](03-entrypoint-sh.md) | `docker/entrypoint.sh` | [ ] |
| 1.4 | [04-supervisord-conf.md](04-supervisord-conf.md) | `docker/supervisor/supervisord.conf` | [ ] |
| 1.5 | [05-nginx-default-conf.md](05-nginx-default-conf.md) | `docker/nginx/default.conf` | [ ] |
| 1.6 | [06-php-ini.md](06-php-ini.md) | `docker/php/php.ini` | [ ] |
| 1.7 | [07-php-fpm-www-conf.md](07-php-fpm-www-conf.md) | `docker/php/www.conf` | [ ] |
| 1.8 | [08-mysql-my-cnf.md](08-mysql-my-cnf.md) | `docker/mysql/my.cnf` | [ ] |
| 1.9 | [09-docker-compose-yml.md](09-docker-compose-yml.md) | `docker-compose.yml` (local dev) | [ ] |
| 1.10 | [10-dockerignore.md](10-dockerignore.md) | `.dockerignore` | [ ] |
| 1.11 | [11-docker-compose-prod-yml.md](11-docker-compose-prod-yml.md) | `docker-compose.prod.yml` | [ ] |
| 1.12 | [12-env-docker-ports.md](12-env-docker-ports.md) | Обновить `.env.example` (Docker-порты) | [ ] |

## Этап 2. Настройка Laravel под MySQL + Redis

| # | Файл | Описание | Статус |
|---|---|---|---|
| 2.1 | [13-env-mysql-redis.md](13-env-mysql-redis.md) | Обновить `.env.example` (MySQL + Redis) | [ ] |
| 2.2 | [14-install-redis-extension.md](14-install-redis-extension.md) | PHP-расширение Redis | [ ] |
| 2.3 | [15-check-laravel-configs.md](15-check-laravel-configs.md) | Проверить конфигурацию Laravel | [ ] |
| 2.4 | [16-health-check-endpoint.md](16-health-check-endpoint.md) | Health-check эндпоинт `/api/health` | [ ] |

## Этап 3. Инициализация Next.js

| # | Файл | Описание | Статус |
|---|---|---|---|
| 3.1 | [17-create-nextjs-app.md](17-create-nextjs-app.md) | Создать `frontend/` (Next.js) | [ ] |
| 3.2 | [18-next-config.md](18-next-config.md) | Настроить `next.config.ts` | [ ] |

## Этап 4. Интеграция Mantine UI

| # | Файл | Описание | Статус |
|---|---|---|---|
| 4.1 | [19-install-mantine.md](19-install-mantine.md) | Установить пакеты Mantine | [ ] |
| 4.2 | [20-postcss-config.md](20-postcss-config.md) | Настроить PostCSS | [ ] |
| 4.3 | [21-providers-tsx.md](21-providers-tsx.md) | Создать `providers.tsx` | [ ] |
| 4.4 | [22-layout-tsx.md](22-layout-tsx.md) | Обновить `layout.tsx` | [ ] |

## Этап 5. Связка Laravel API ↔ Next.js

| # | Файл | Описание | Статус |
|---|---|---|---|
| 5.1 | [23-laravel-api-routes.md](23-laravel-api-routes.md) | API-маршруты + Sanctum + CORS | [ ] |
| 5.2 | [24-nextjs-http-client.md](24-nextjs-http-client.md) | HTTP-клиент (`api.ts`) | [ ] |
| 5.3 | [25-ssr-api-pattern.md](25-ssr-api-pattern.md) | Паттерн SSR ↔ API | [ ] |

## Этап 6. Финальная проверка и документация

| # | Файл | Описание | Статус |
|---|---|---|---|
| 6.1 | [26-launch-checks.md](26-launch-checks.md) | Проверки запуска (7 тестов) | [ ] |
| 6.2 | [27-update-readme.md](27-update-readme.md) | Обновить README.md | [ ] |
| 6.3 | [28-update-gitignore.md](28-update-gitignore.md) | Обновить `.gitignore` | [ ] |
