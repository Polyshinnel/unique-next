# Шаг 1.9 — `docker-compose.yml` (local dev)

**Этап:** 1. Docker-инфраструктура  
**Статус:** [ ] Не выполнен

## Описание

Основной файл Docker Compose для локальной разработки. Определяет 5 сервисов:

1. **app** — монолитный контейнер (PHP-FPM + Nginx + Supervisor + Node.js)
2. **next** — контейнер Next.js SSR (Mantine UI)
3. **scheduler** — Laravel scheduler (`php artisan schedule:work`)
4. **db** — MySQL 8.4
5. **redis** — Redis 7.x

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
      - "${VITE_PORT:-25173}:${VITE_PORT:-25173}"
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
      - REDIS_HOST=uniqset2-redis
      - WWWUSER=${UID:-1000}
      - WWWGROUP=${GID:-1000}
    networks:
      - uniqset2_network
    depends_on:
      - db
      - redis
      - next

  next:
    image: node:22-alpine
    container_name: uniqset2_next
    restart: unless-stopped
    working_dir: /app
    command: sh -c "npm install && npm run dev"
    volumes:
      - ./frontend:/app
      - next_node_modules:/app/node_modules
    environment:
      - BACKEND_URL=http://uniqset2_app:80
      - NEXT_PUBLIC_API_URL=http://localhost:${APP_HTTP_PORT:-28080}/api
    networks:
      - uniqset2_network

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
  next_node_modules:

networks:
  uniqset2_network:
    driver: bridge
```

## Ключевые моменты

- **Порты конфигурируются через `.env`** — значения по умолчанию: 28080, 25173, 23306, 26379
- **`network: host`** в `build` — для доступа к npm registry через хостовую сеть при сборке
- **Volumes для app** — весь проект монтируется для hot-reload в dev-режиме
- **`next_node_modules`** — именованный volume, чтобы node_modules не перезаписывался хостовым маунтом
- **Redis alias `uniqset2-redis`** — используется как `REDIS_HOST` в приложении
- **`depends_on`** — задаёт порядок запуска (db, redis → app)

## Зависимости

- Шаги 1.1–1.8 (все конфиг-файлы Docker должны быть готовы)
- Шаг 1.12 (переменные портов в `.env`)

## Критерий завершения

Файл `docker-compose.yml` создан в корне проекта. Команда `docker compose config` проходит без ошибок.
