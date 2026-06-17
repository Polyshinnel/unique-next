# Шаг 3.2 — Настроить `next.config.ts`

**Этап:** 3. Инициализация Next.js приложения  
**Статус:** [ ] Не выполнен

## Описание

Настроить конфигурацию Next.js для работы в Docker и интеграции с Laravel API.

## Содержимое `frontend/next.config.ts`

```typescript
const nextConfig = {
  output: 'standalone',           // для Docker
  reactStrictMode: true,
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: `${process.env.BACKEND_URL || 'http://app:9000'}/api/:path*`,
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
- Используется для **серверных компонентов** (SSR) — запросы идут по внутренней Docker-сети
- `BACKEND_URL` задаётся через env в `docker-compose.yml` (`http://uniqset2_app:80`)
- Fallback `http://app:9000` — для обратной совместимости

### `reactStrictMode: true`

- Включает строгий режим React (double-rendering в dev для обнаружения проблем)

## Зависимости

- Шаг 3.1 (Next.js приложение создано)

## Критерий завершения

Файл `frontend/next.config.ts` содержит `output: 'standalone'` и rewrites для `/api/*`.
