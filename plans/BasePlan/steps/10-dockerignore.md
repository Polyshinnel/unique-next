# Шаг 1.10 — `.dockerignore`

**Этап:** 1. Docker-инфраструктура  
**Статус:** [x] Выполнен

## Описание

Создать файл `.dockerignore` для исключения ненужных файлов из Docker build context. Ускоряет сборку и уменьшает размер образа.

## Содержимое файла

```
node_modules
vendor
.git
.gitignore
.env
.env.backup
.phpunit.result.cache
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
bootstrap/cache/*
npm-debug.log
.DS_Store
docker-compose.yml
docker-compose.prod.yml
README.md
tests
phpunit.xml
resources/js/.next
resources/js/.env.local
```

## Что исключается и почему

| Паттерн | Причина |
|---|---|
| `node_modules`, `vendor` | Устанавливаются внутри образа |
| `.git` | Не нужен в образе, большой размер |
| `.env` | Секреты не должны попадать в образ |
| `storage/logs/*`, `storage/framework/*` | Runtime-данные, не часть кода |
| `docker-compose*.yml` | Файлы оркестрации, не нужны внутри |
| `tests`, `phpunit.xml` | Тесты не нужны в production-образе |
| `resources/js/.next`, `resources/js/.env.local` | Runtime/build-данные и локальное окружение Next.js |

## Зависимости

- Шаг 1.1

## Критерий завершения

Файл `.dockerignore` создан в корне проекта.
