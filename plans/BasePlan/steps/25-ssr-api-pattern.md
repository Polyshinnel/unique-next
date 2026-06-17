# Шаг 5.3 — Паттерн SSR ↔ API

**Этап:** 5. Связка Laravel API ↔ Next.js  
**Статус:** [x] Выполнен

## Описание

Определить и задокументировать паттерн взаимодействия Next.js с Laravel API для серверных и клиентских компонентов.

## Два режима запросов

### 1. Серверные компоненты (RSC) — запросы внутри контейнера `app`

```
[Next.js SSR] --HTTP--> [nginx 127.0.0.1:80] --fastcgi--> [PHP-FPM]
     │                            ▲
     └──── http://127.0.0.1/api ──┘
```

- URL: `http://127.0.0.1/api/...` (env `BACKEND_URL`)
- Без cookies — серверный контекст, нет браузера
- Быстро — нет выхода за пределы контейнера
- Используется `api.server.get()` из `@/lib/api`

### 2. Клиентские компоненты — запросы через браузер

```
[Browser] --HTTP--> [nginx :28080] --proxy/fastcgi--> [PHP-FPM / Next.js]
```

- URL: `/api/...` (same-origin через nginx)
- С cookies — Sanctum CSRF + session
- Для мутирующих запросов (POST, PUT, DELETE) — сначала `getCsrfToken()`
- Используется `api.get()`, `api.post()` из `@/lib/api`

## Правила

1. **Данные для первоначального рендера** — загружать в серверных компонентах через `api.server.get()`
2. **Интерактивные действия** (формы, кнопки) — в клиентских компонентах через `api.post()` / `api.put()`
3. **Аутентификация** — Sanctum cookie-based. CSRF-токен получать перед каждым мутирующим запросом
4. **Типизация** — все API-ответы должны иметь TypeScript-интерфейсы

## Пример: страница со списком

```tsx
// app/items/page.tsx — серверный компонент
import { api } from '@/lib/api';
import { ItemsList } from './items-list';

interface Item {
  id: number;
  name: string;
}

export default async function ItemsPage() {
  const items = await api.server.get<Item[]>('/items');
  return <ItemsList initialItems={items} />;
}
```

```tsx
// app/items/items-list.tsx — клиентский компонент
'use client';

import { api, getCsrfToken } from '@/lib/api';
import { Button } from '@mantine/core';

export function ItemsList({ initialItems }: { initialItems: Item[] }) {
  const handleDelete = async (id: number) => {
    await getCsrfToken();
    await api.delete(`/items/${id}`);
    // обновить список...
  };

  return (
    <div>
      {initialItems.map(item => (
        <div key={item.id}>
          {item.name}
          <Button onClick={() => handleDelete(item.id)}>Удалить</Button>
        </div>
      ))}
    </div>
  );
}
```

## Зависимости

- Шаг 5.1 (API-маршруты и Sanctum)
- Шаг 5.2 (HTTP-клиент)

## Критерий завершения

Паттерн задокументирован. Тестовая страница работает с SSR-запросом к `/api/health` и отображает результат.

## Проверка выполнения

- Главная страница [resources/js/app/page.tsx](/home/andrey/projects/uniqset2.com/resources/js/app/page.tsx:1) переведена на SSR-запрос через `api.server.get<HealthResponse>('/health')`
- Ответ `/api/health` типизирован и отображается в UI, включая общий статус, timestamp и статусы сервисов
- Для неуспешного ответа добавлен fallback с сообщением об ошибке, чтобы поведение SSR оставалось прозрачным при проблемах бэкенда
