# Шаг 3.1 — Создать директорию `frontend/` (Next.js)

**Этап:** 3. Инициализация Next.js приложения  
**Статус:** [ ] Не выполнен

## Описание

Инициализировать Next.js приложение в директории `frontend/` с TypeScript, ESLint, App Router и `src/` структурой.

## Команда инициализации

```bash
npx create-next-app@latest frontend \
  --typescript \
  --eslint \
  --app \
  --src-dir \
  --import-alias "@/*"
```

## Целевая структура

```
frontend/
├── src/
│   ├── app/
│   │   ├── layout.tsx
│   │   ├── page.tsx
│   │   └── providers.tsx      # MantineProvider, тема (создаётся на шаге 4.3)
│   ├── components/
│   ├── lib/
│   │   └── api.ts             # HTTP-клиент для Laravel API (создаётся на шаге 5.2)
│   └── styles/
├── public/
├── next.config.ts
├── package.json
├── tsconfig.json
└── .env.local
```

## Действия

1. Убедиться, что Node.js ≥ 18 установлен на хосте
2. Выполнить `npx create-next-app@latest` с указанными флагами
3. Проверить, что приложение запускается: `cd frontend && npm run dev`
4. Создать пустые директории для будущих файлов:
   - `frontend/src/components/`
   - `frontend/src/lib/`
   - `frontend/src/styles/`

## Важно

- **Не использовать TailwindCSS** — вместо него будет Mantine UI (шаг 4)
- При вопросе `create-next-app` о Tailwind — выбрать **No**
- `--import-alias "@/*"` позволяет импорты вида `import { api } from '@/lib/api'`

## Зависимости

- Этап 1 завершён (Docker-инфраструктура, т.к. `frontend/` монтируется в контейнер `next`)

## Критерий завершения

- Директория `frontend/` создана со стандартной структурой Next.js
- `npm run dev` внутри `frontend/` запускается без ошибок
- TypeScript, ESLint, App Router активны
