# Шаг 6.2 — Обновить README.md

**Этап:** 6. Финальная проверка и документация  
**Статус:** [x] Выполнен

## Описание

Обновить `README.md` в корне проекта с описанием архитектуры, инструкциями по запуску и полезными командами.

## Содержимое README.md (секции)

### 1. Описание архитектуры

- Схема из BasePlan (ASCII-диаграмма docker-compose)
- Описание контейнеров: app, scheduler, db, redis
- Технологический стек: Laravel 13, Next.js 16, Mantine UI, MySQL 8.4, Redis 7

### 2. Быстрый старт

```bash
# Клонировать репозиторий
git clone <repo-url>
cd uniqset2.com

# Скопировать окружение
cp .env.example .env

# Поднять контейнеры
docker compose up -d --build

# Выполнить миграции
docker compose exec app php artisan migrate

# Открыть в браузере
# http://localhost:28080
```

### 3. Полезные команды

```bash
# Подключиться к контейнеру app
docker compose exec app sh

# Artisan команды
docker compose exec app php artisan migrate
docker compose exec app php artisan tinker
docker compose exec app php artisan cache:clear

# Npm/Next.js в контейнере
docker compose exec app npm run dev -- --hostname 0.0.0.0 --port 3000
docker compose exec app npm run build

# Логи
docker compose logs -f app

# Supervisor статус
docker compose exec app supervisorctl status
```

### 4. Переменные окружения и порты

Таблица портов из BasePlan:

| Порт | Сервис |
|---|---|
| 28080 | nginx (HTTP) |
| 23306 | MySQL |
| 26379 | Redis |

### 5. Production деплой

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

## Зависимости

- Шаг 6.1 (проверки пройдены — знаем что всё работает)

## Критерий завершения

`README.md` содержит актуальное описание архитектуры, инструкции быстрого старта, список команд и таблицу портов.

## Проверка выполнения

- Корневой [README.md](/home/andrey/projects/uniqset2.com/README.md:1) полностью обновлён под текущую архитектуру проекта вместо стандартного шаблона Laravel
- Добавлены ASCII-схема docker-compose, описание контейнеров `app` / `scheduler` / `db` / `redis`, быстрый старт, полезные команды и таблицы переменных/портов
- Production-запуск через `docker compose -f docker-compose.prod.yml up -d --build` задокументирован отдельной секцией
- Технологический стек в README приведён к фактическому состоянию репозитория: Laravel 13, Next.js 16, Mantine UI, MySQL 8.4, Redis 7
