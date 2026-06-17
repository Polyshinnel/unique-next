# Шаг 6.3 — Обновить `.gitignore`

**Этап:** 6. Финальная проверка и документация  
**Статус:** [ ] Не выполнен

## Описание

Добавить записи для Next.js в корневой `.gitignore`, чтобы не коммитить build-артефакты и зависимости фронтенда.

## Что добавить в `.gitignore`

```gitignore
# Next.js
resources/js/.next/
resources/js/.env.local
```

## Пояснение

| Паттерн | Зачем |
|---|---|
| `resources/js/.next/` | Build-кеш и output Next.js (пересоздаётся при сборке) |
| `resources/js/.env.local` | Локальные переменные окружения (могут содержать секреты) |

## Важно

- Корневые `node_modules/` и `vendor/` уже в `.gitignore` (стандарт Laravel)
- Не добавлять отдельный `resources/js/package.json` — npm-зависимости живут в корневом `package.json`

## Зависимости

- Шаг 3.1 (Next.js структура в `resources/js` существует)

## Критерий завершения

`.gitignore` содержит записи для Next.js. `git status` не показывает `resources/js/.next/`.
