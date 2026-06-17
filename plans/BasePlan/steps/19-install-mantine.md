# Шаг 4.1 — Установить пакеты Mantine

**Этап:** 4. Интеграция Mantine UI  
**Статус:** [ ] Не выполнен

## Описание

Установить Mantine UI и все необходимые пакеты в Next.js приложение.

## Команды

```bash
cd frontend

# Основные пакеты Mantine
npm install @mantine/core @mantine/hooks @mantine/form @mantine/notifications @mantine/modals

# PostCSS плагины (обязательны для Mantine)
npm install postcss postcss-preset-mantine postcss-simple-vars
```

## Устанавливаемые пакеты

| Пакет | Назначение |
|---|---|
| `@mantine/core` | Основные компоненты (Button, Input, Modal, etc.) |
| `@mantine/hooks` | React-хуки (useDisclosure, useMediaQuery, etc.) |
| `@mantine/form` | Управление формами с валидацией |
| `@mantine/notifications` | Система уведомлений (toast) |
| `@mantine/modals` | Менеджер модальных окон |
| `postcss` | CSS-процессор |
| `postcss-preset-mantine` | PostCSS-пресет для Mantine (CSS variables, mixins) |
| `postcss-simple-vars` | Поддержка переменных в PostCSS (breakpoints) |

## Зависимости

- Шаг 3.1 (Next.js приложение создано, `package.json` существует)

## Критерий завершения

Все пакеты установлены, `package.json` содержит зависимости Mantine. `npm install` проходит без ошибок.
