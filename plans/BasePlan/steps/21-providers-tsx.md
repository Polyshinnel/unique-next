# Шаг 4.3 — Создать `providers.tsx` с MantineProvider

**Этап:** 4. Интеграция Mantine UI  
**Статус:** [ ] Не выполнен

## Описание

Создать компонент-обёртку `Providers`, который инициализирует Mantine UI, систему уведомлений и менеджер модальных окон.

## Содержимое `frontend/src/app/providers.tsx`

```tsx
'use client';

import { MantineProvider, createTheme } from '@mantine/core';
import { Notifications } from '@mantine/notifications';
import { ModalsProvider } from '@mantine/modals';
import '@mantine/core/styles.css';
import '@mantine/notifications/styles.css';

const theme = createTheme({
  primaryColor: 'blue',
  fontFamily: 'Inter, sans-serif',
});

export function Providers({ children }: { children: React.ReactNode }) {
  return (
    <MantineProvider theme={theme} defaultColorScheme="auto">
      <ModalsProvider>
        <Notifications position="top-right" />
        {children}
      </ModalsProvider>
    </MantineProvider>
  );
}
```

## Ключевые моменты

- **`'use client'`** — обязательная директива, т.к. Mantine использует React Context (клиентский компонент)
- **`defaultColorScheme="auto"`** — автоматическое определение тёмной/светлой темы по системным настройкам
- **CSS-импорты** должны быть именно здесь (в клиентском компоненте), не в `layout.tsx`
- **`createTheme()`** — кастомизация темы Mantine:
  - `primaryColor: 'blue'` — основной цвет
  - `fontFamily: 'Inter, sans-serif'` — шрифт (нужно подключить Inter отдельно, если требуется)

## Порядок вложенности

```
MantineProvider
  └── ModalsProvider
        └── Notifications
              └── {children}
```

## Зависимости

- Шаг 4.1 (пакеты Mantine установлены)
- Шаг 4.2 (PostCSS настроен)

## Критерий завершения

Файл `frontend/src/app/providers.tsx` создан. Компонент экспортирует `Providers`.
