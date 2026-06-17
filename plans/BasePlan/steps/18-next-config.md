# Шаг 3.2 — Настроить `next.config.ts`

**Этап:** 3. Инициализация Next.js приложения  
**Статус:** [x] Выполнен

## Описание

Настроить конфигурацию Next.js для работы в Docker и интеграции с Laravel API.

## Содержимое `resources/js/next.config.ts`

```typescript
const nextConfig = {
  output: 'standalone',           // для Docker
  reactStrictMode: true,
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: `${process.env.BACKEND_URL || 'http://127.0.0.1'}/api/:path*`,
      },
    ];
  },
};

export default nextConfig;
```

## Ключевые настройки

### `output: 'standalone'`

- Обязательно для Docker-деплоя
- Создаёт минимальный self-contained build в `.next/standalone/`
- Включает только необходимые файлы (не весь `node_modules`)

### `rewrites`

- Проксирует `/api/*` запросы из Next.js к Laravel
- Используется для **серверных компонентов** (SSR) — запросы идут через nginx внутри контейнера `app`
- `BACKEND_URL` задаётся через env в `docker-compose.yml` (`http://127.0.0.1`)
- Fallback `http://127.0.0.1` — Next.js и nginx живут в одном контейнере

### `reactStrictMode: true`

- Включает строгий режим React (double-rendering в dev для обнаружения проблем)

## Зависимости

- Шаг 3.1 (Next.js приложение создано)

## Критерий завершения

Файл `resources/js/next.config.ts` содержит `output: 'standalone'` и rewrites для `/api/*`.

## Проверка выполнения

- В [resources/js/next.config.ts](/home/andrey/projects/uniqset2.com/resources/js/next.config.ts:1) настроены `output: 'standalone'`, `reactStrictMode: true` и rewrite `/api/:path*` на `${process.env.BACKEND_URL || 'http://127.0.0.1'}/api/:path*`
- `npm run lint` завершился успешно
