# Шаг 1.9 — `docker-compose.yml` (local dev)

**Этап:** 1. Docker-инфраструктура  
**Статус:** [x] Выполнен

## Описание

Основной файл Docker Compose для локальной разработки. Определяет 4 сервиса:

1. **app** — монолитный контейнер (PHP-FPM + Nginx + Supervisor + Node.js)
2. **scheduler** — Laravel scheduler (`php artisan schedule:work`)
3. **db** — MySQL 8.4
4. **redis** — Redis 7.x

Next.js живёт внутри того же репозитория и контейнера `app` как часть модульного монолита. Nginx проксирует фронтенд-запросы на локальный Next.js-процесс `127.0.0.1:3000`, а не в отдельный Docker-сервис.

## Содержимое файла

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

## Ключевые моменты

- **Порты конфигурируются через `.env`** — значения по умолчанию: 28080, 23306, 26379
- **`network: host`** в `build` — для доступа к npm registry через хостовую сеть при сборке
- **Volumes для app** — весь проект монтируется для hot-reload в dev-режиме
- **Next.js внутри app** — отдельного сервиса `next` нет; процесс запускается Supervisor-ом внутри монолитного контейнера
- **Same-origin API** — клиентский Next.js код использует относительный `/api`, SSR-запросы используют `BACKEND_URL=http://127.0.0.1`
- **Redis alias `uniqset2-redis`** — используется как `REDIS_HOST` в приложении
- **`depends_on`** — задаёт порядок запуска (db, redis → app)

## Зависимости

- Шаги 1.1–1.8 (все конфиг-файлы Docker должны быть готовы)
- Шаг 1.12 (переменные портов в `.env`)

## Критерий завершения

Файл `docker-compose.yml` создан в корне проекта. Команда `docker compose config` проходит без ошибок.
