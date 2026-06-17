# Шаг 3.1 — Инициализировать Next.js в `resources/js`

**Этап:** 3. Инициализация Next.js приложения  
**Статус:** [x] Выполнен

## Описание

Инициализировать Next.js приложение внутри существующей Laravel-структуры `resources/js` с TypeScript, ESLint и App Router.

Это повторяет подход `supply.kidsberry.org`: фронтенд живёт в `resources/js`, зависимости и npm-скрипты остаются в корневом `package.json`, отдельной директории `frontend/` нет.

## Пакеты и npm-скрипты

Установить Next.js в корневой `package.json`:

```bash
npm install next react react-dom
npm install -D typescript @types/react @types/react-dom @types/node eslint eslint-config-next
```

Обновить scripts в `package.json`:

```json
{
  "scripts": {
    "dev": "next dev resources/js",
    "build": "next build resources/js",
    "start": "next start resources/js"
  }
}
```

## Целевая структура

```
resources/js/
├── app/
│   ├── layout.tsx
│   ├── page.tsx
│   └── providers.tsx      # MantineProvider, тема (создаётся на шаге 4.3)
├── components/
├── lib/
│   └── api.ts             # HTTP-клиент для Laravel API (создаётся на шаге 5.2)
├── styles/
├── public/
├── next.config.ts
└── tsconfig.json
```

## Действия

1. Убедиться, что Node.js ≥ 18 установлен на хосте
2. Установить Next.js/React/TypeScript-пакеты в корневый `package.json`
3. Обновить npm-скрипты на `next dev resources/js`, `next build resources/js`, `next start resources/js`
4. Создать директории:
   - `resources/js/app/`
   - `resources/js/components/`
   - `resources/js/lib/`
   - `resources/js/styles/`
   - `resources/js/public/`
5. Создать базовые файлы `resources/js/app/layout.tsx`, `resources/js/app/page.tsx`, `resources/js/next.config.ts`, `resources/js/tsconfig.json`
6. Проверить запуск: `npm run dev -- --hostname 0.0.0.0 --port 3000`

## Важно

- **Не использовать TailwindCSS** — вместо него будет Mantine UI (шаг 4)
- Алиас `@/*` должен указывать на `resources/js/*`, чтобы импорты выглядели как `import { api } from '@/lib/api'`
- Не создавать вложенный `package.json` внутри `resources/js`; пакетный менеджер остаётся на корне Laravel-проекта

## Зависимости

- Этап 1 завершён (Docker-инфраструктура, т.к. `resources/js` запускается внутри контейнера `app`)

## Критерий завершения

- Next.js App Router живёт в `resources/js/app`
- `npm run dev -- --hostname 0.0.0.0 --port 3000` запускается без ошибок из корня проекта
- TypeScript, ESLint, App Router активны

## Проверка выполнения

- Node.js на хосте: `v24.14.0`
- Добавлены зависимости Next.js/React/TypeScript/ESLint в корневые [package.json](/home/andrey/projects/uniqset2.com/package.json:1) и [package-lock.json](/home/andrey/projects/uniqset2.com/package-lock.json:1)
- Обновлены npm-скрипты: `dev`, `build`, `start` используют `next ... resources/js`
- Создан App Router каркас в [resources/js/app/layout.tsx](/home/andrey/projects/uniqset2.com/resources/js/app/layout.tsx:1) и [resources/js/app/page.tsx](/home/andrey/projects/uniqset2.com/resources/js/app/page.tsx:1)
- Настроены [resources/js/next.config.ts](/home/andrey/projects/uniqset2.com/resources/js/next.config.ts:1), [resources/js/tsconfig.json](/home/andrey/projects/uniqset2.com/resources/js/tsconfig.json:1), [eslint.config.mjs](/home/andrey/projects/uniqset2.com/eslint.config.mjs:1)
- Удалены старые Vite/Tailwind точки входа: `vite.config.js`, `resources/css/app.css`, `resources/js/app.js`
- `npm run lint` завершился успешно
- `npm run build` завершился успешно
- `npm run dev -- --hostname 0.0.0.0 --port 3000` запустился успешно; проверка `HEAD /` вернула `HTTP/1.1 200 OK`
