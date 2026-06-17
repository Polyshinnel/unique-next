# Шаг 5.3 — Паттерн SSR ↔ API

**Этап:** 5. Связка Laravel API ↔ Next.js  
**Статус:** [ ] Не выполнен

## Описание

Определить и задокументировать паттерн взаимодействия Next.js с Laravel API для серверных и клиентских компонентов.

## Два режима запросов

### 1. Серверные компоненты (RSC) — запросы через внутреннюю Docker-сеть

```
[Next.js SSR] --HTTP--> [app container nginx :80] --fastcgi--> [PHP-FPM]
     │                           ▲
     │     Docker internal       │
     └───── http://app:80/api/ ──┘
```

- URL: `http://uniqset2_app:80/api/...` (env `BACKEND_URL`)
- Без cookies — серверный контекст, нет браузера
- Быстро — нет выхода за пределы Docker-сети
- Используется `api.server.get()` из `@/lib/api`

### 2. Клиентские компоненты — запросы через браузер

```
[Browser] --HTTP--> [nginx :28080] --proxy/fastcgi--> [PHP-FPM / Next.js]
```

- URL: `http://localhost:28080/api/...` (env `NEXT_PUBLIC_API_URL`)
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
