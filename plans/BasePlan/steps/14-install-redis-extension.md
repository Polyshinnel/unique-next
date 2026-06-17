# Шаг 2.2 — Установить PHP-расширение Redis для Laravel

**Этап:** 2. Настройка Laravel под MySQL + Redis  
**Статус:** [x] Выполнен

## Описание

Обеспечить работу Laravel с Redis. Два варианта:

1. **phpredis** (C-расширение) — уже устанавливается в Dockerfile через `pecl install redis`. Предпочтительный вариант.
2. **predis/predis** (PHP-пакет) — альтернатива через Composer.

## Действия

### Вариант A: phpredis (рекомендуемый)

Расширение уже указано в Dockerfile (шаг 1.2):

```dockerfile
RUN pecl install redis && docker-php-ext-enable redis
```

В `.env.example` уже должно быть:

```env
REDIS_CLIENT=phpredis
```

**Никаких дополнительных Composer-пакетов не нужно.**

### Вариант B: predis (если phpredis недоступен)

```bash
composer require predis/predis
```

В `.env`:

```env
REDIS_CLIENT=predis
```

## Рекомендация

Использовать **phpredis** — быстрее, уже установлен в Docker-образе. `REDIS_CLIENT=phpredis` уже есть в стандартном `.env.example` Laravel.

## Проверка выполнения

- В [Dockerfile](/home/andrey/projects/uniqset2.com/Dockerfile:23) есть установка расширения Redis через `pecl install redis`
- В [.env.example](/home/andrey/projects/uniqset2.com/.env.example:45) указан `REDIS_CLIENT=phpredis`
- Дополнительный Composer-пакет `predis/predis` не требуется

## Зависимости

- Шаг 1.2 (Dockerfile с `pecl install redis`)
- Шаг 2.1 (настройки Redis в `.env.example`)

## Критерий завершения

- `REDIS_CLIENT=phpredis` указан в `.env.example`
- Расширение `redis` устанавливается в Dockerfile
- Либо альтернативно: `predis/predis` добавлен в `composer.json`
