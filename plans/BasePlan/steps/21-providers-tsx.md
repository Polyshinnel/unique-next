# Шаг 4.3 — Создать `providers.tsx` с MantineProvider

**Этап:** 4. Интеграция Mantine UI  
**Статус:** [x] Выполнен

## Описание

Создать компонент-обёртку `Providers`, который инициализирует Mantine UI, систему уведомлений и менеджер модальных окон.

## Содержимое `resources/js/app/providers.tsx`

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

Файл `resources/js/app/providers.tsx` создан. Компонент экспортирует `Providers`.

## Проверка выполнения

- Создан файл [resources/js/app/providers.tsx](/home/andrey/projects/uniqset2.com/resources/js/app/providers.tsx:1) с клиентским компонентом `Providers`
- Внутри подключены `MantineProvider`, `ModalsProvider` и `Notifications` в ожидаемом порядке
- CSS Mantine импортируется из `providers.tsx`, как и требуется для клиентского компонента
