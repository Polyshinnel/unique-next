# Шаг 2.1 — Обновить `.env.example` (MySQL + Redis)

**Этап:** 2. Настройка Laravel под MySQL + Redis  
**Статус:** [ ] Не выполнен

## Описание

Обновить переменные окружения в `.env.example` для переключения Laravel с SQLite на MySQL и с database-драйверов на Redis.

## Что изменить в `.env.example`

### База данных (MySQL)

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=uniqset2
DB_USERNAME=uniqset2
DB_PASSWORD=secret
```

### Очередь, кэш, сессии (Redis)

```env
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
REDIS_HOST=redis
```

## До и после

| Параметр | Было | Стало |
|---|---|---|
| `DB_CONNECTION` | sqlite | mysql |
| `DB_HOST` | — | mysql |
| `QUEUE_CONNECTION` | database | redis |
| `CACHE_STORE` | database | redis |
| `SESSION_DRIVER` | database | redis |
| `REDIS_HOST` | — | redis |

## Важно

- `DB_HOST=mysql` — это имя сервиса в docker-compose (DNS-имя внутри Docker-сети)
- `REDIS_HOST=redis` — аналогично, имя сервиса
- После изменения `.env.example` — обновить `.env` соответственно

## Зависимости

- Шаг 1.12 (порты Docker уже добавлены в `.env.example`)

## Критерий завершения

`.env.example` содержит корректные настройки для MySQL и Redis.
