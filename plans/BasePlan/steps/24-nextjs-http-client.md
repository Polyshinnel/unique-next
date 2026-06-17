# Шаг 5.2 — Next.js: создать HTTP-клиент

**Этап:** 5. Связка Laravel API ↔ Next.js  
**Статус:** [ ] Не выполнен

## Описание

Создать обёртку над `fetch` для взаимодействия с Laravel API из Next.js. Клиент должен поддерживать CSRF-токены Sanctum и типизацию ответов.

## Файл: `frontend/src/lib/api.ts`

```typescript
const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:28080/api';
const BACKEND_URL = process.env.BACKEND_URL || 'http://uniqset2_app:80';

// Для серверных компонентов (SSR) — запросы по внутренней Docker-сети
export function getServerApiUrl(): string {
  return `${BACKEND_URL}/api`;
}

// Для клиентских компонентов — запросы через браузер
export function getClientApiUrl(): string {
  return API_URL;
}

interface ApiOptions extends RequestInit {
  params?: Record<string, string>;
}

// Получить CSRF-cookie от Sanctum (для мутирующих запросов)
export async function getCsrfToken(): Promise<void> {
  await fetch(`${getClientApiUrl().replace('/api', '')}/sanctum/csrf-cookie`, {
    credentials: 'include',
  });
}

// Основная функция запроса
export async function apiRequest<T>(
  endpoint: string,
  options: ApiOptions = {},
  isServer = false,
): Promise<T> {
  const baseUrl = isServer ? getServerApiUrl() : getClientApiUrl();
  
  const url = new URL(`${baseUrl}${endpoint}`);
  if (options.params) {
    Object.entries(options.params).forEach(([key, value]) => {
      url.searchParams.append(key, value);
    });
  }

  const response = await fetch(url.toString(), {
    ...options,
    credentials: isServer ? 'omit' : 'include',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...options.headers,
    },
  });

  if (!response.ok) {
    throw new Error(`API Error: ${response.status} ${response.statusText}`);
  }

  return response.json();
}

// Удобные обёртки
export const api = {
  get: <T>(endpoint: string, options?: ApiOptions) =>
    apiRequest<T>(endpoint, { ...options, method: 'GET' }),
  
  post: <T>(endpoint: string, data?: unknown, options?: ApiOptions) =>
    apiRequest<T>(endpoint, { ...options, method: 'POST', body: JSON.stringify(data) }),
  
  put: <T>(endpoint: string, data?: unknown, options?: ApiOptions) =>
    apiRequest<T>(endpoint, { ...options, method: 'PUT', body: JSON.stringify(data) }),
  
  delete: <T>(endpoint: string, options?: ApiOptions) =>
    apiRequest<T>(endpoint, { ...options, method: 'DELETE' }),

  // Для серверных компонентов
  server: {
    get: <T>(endpoint: string, options?: ApiOptions) =>
      apiRequest<T>(endpoint, { ...options, method: 'GET' }, true),
  },
};
```

## Использование

### В серверном компоненте (RSC)

```tsx
// app/page.tsx (серверный компонент)
import { api } from '@/lib/api';

export default async function Page() {
  const health = await api.server.get('/health');
  return <div>{JSON.stringify(health)}</div>;
}
```

### В клиентском компоненте

```tsx
'use client';
import { api, getCsrfToken } from '@/lib/api';

async function handleSubmit(data: FormData) {
  await getCsrfToken();
  const result = await api.post('/some-endpoint', data);
}
```

## Переменные окружения

| Переменная | Где используется | Значение |
|---|---|---|
| `NEXT_PUBLIC_API_URL` | Клиент (браузер) | `http://localhost:28080/api` |
| `BACKEND_URL` | Сервер (SSR, Docker) | `http://uniqset2_app:80` |

## Зависимости

- Шаг 3.1 (Next.js приложение создано)
- Шаг 5.1 (API-маршруты и Sanctum настроены)

## Критерий завершения

Файл `frontend/src/lib/api.ts` создан. Запросы к Laravel API проходят из серверных и клиентских компонентов.
