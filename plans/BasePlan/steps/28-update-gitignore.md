# Шаг 6.3 — Обновить `.gitignore`

**Этап:** 6. Финальная проверка и документация  
**Статус:** [ ] Не выполнен

## Описание

Добавить записи для Next.js в корневой `.gitignore`, чтобы не коммитить build-артефакты и зависимости фронтенда.

## Что добавить в `.gitignore`

```gitignore
# Next.js
frontend/.next/
frontend/node_modules/
frontend/.env.local
```

## Пояснение

| Паттерн | Зачем |
|---|---|
| `frontend/.next/` | Build-кеш и output Next.js (пересоздаётся при сборке) |
| `frontend/node_modules/` | npm-зависимости (устанавливаются через `npm install`) |
| `frontend/.env.local` | Локальные переменные окружения (могут содержать секреты) |

## Важно

- Корневые `node_modules/` и `vendor/` уже в `.gitignore` (стандарт Laravel)
- Не добавлять `frontend/package-lock.json` — он **должен** быть в git (воспроизводимость сборки)

## Зависимости

- Шаг 3.1 (директория `frontend/` существует)

## Критерий завершения

`.gitignore` содержит записи для Next.js. `git status` не показывает `frontend/.next/` и `frontend/node_modules/`.
