# Шаг 4.4 — Обновить `layout.tsx`

**Этап:** 4. Интеграция Mantine UI  
**Статус:** [ ] Не выполнен

## Описание

Обновить корневой layout Next.js для интеграции с Mantine UI. Добавить `ColorSchemeScript` в `<head>` и обернуть `{children}` в `Providers`.

## Содержимое `resources/js/app/layout.tsx`

```tsx
import { ColorSchemeScript } from '@mantine/core';
import { Providers } from './providers';

export const metadata = {
  title: 'Uniqset',
  description: 'Uniqset application',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="ru" suppressHydrationWarning>
      <head>
        <ColorSchemeScript />
      </head>
      <body>
        <Providers>{children}</Providers>
      </body>
    </html>
  );
}
```

## Ключевые моменты

- **`ColorSchemeScript`** — инлайн-скрипт в `<head>`, предотвращает "мигание" при загрузке тёмной/светлой темы (FOUC)
- **`lang="ru"`** — русская локаль
- **`suppressHydrationWarning`** — подавляет предупреждение React о несовпадении серверного/клиентского HTML (из-за ColorSchemeScript)
- **`Providers`** — клиентский компонент-обёртка из шага 4.3
- **`metadata`** — серверный export для SEO (title, description)

## Важно

- `layout.tsx` — **серверный компонент** (нет `'use client'`). Mantine-провайдер вынесен в отдельный клиентский `providers.tsx`.
- Не добавлять CSS-импорты Mantine сюда — они в `providers.tsx`.

## Зависимости

- Шаг 4.3 (`providers.tsx` создан)

## Критерий завершения

`resources/js/app/layout.tsx` содержит `ColorSchemeScript` и `Providers`. Страница рендерится без ошибок гидратации.
