# Шаг 1.11 — `docker-compose.prod.yml`

**Этап:** 1. Docker-инфраструктура  
**Статус:** [x] Выполнен

## Описание

Production-версия Docker Compose. Отличия от dev-версии:

- **Нет bind-mount** всего проекта — код запекается в образ
- **Именованный volume** `storage_public_data` для `storage/app/public`
- **Нет контейнера `next`** — Next.js является частью монолитного контейнера `app`
- **Нет проброса портов** для MySQL и Redis наружу (безопасность)
- **`RUN_STORAGE_LINK=true`** в env — создаёт symlink при старте

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
      - storage_public_data:/var/www/html/storage/app/public
    ports:
      - "${APP_HTTP_PORT:-28080}:80"
    environment:
      RUN_STORAGE_LINK: "true"
      BACKEND_URL: http://127.0.0.1
      NEXT_PUBLIC_API_URL: /api
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

## Использование

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

## Зависимости

- Шаги 1.1–1.8 (конфиг-файлы)
- Шаг 1.9 (понимание dev-версии)

## Критерий завершения

Файл `docker-compose.prod.yml` создан в корне проекта.
