# Шаг 06 — Frontend: хелпер `lib/seo.ts`

## Цель

Один раз описать тип ответа SEO и хелперы, которые страницы используют в `generateMetadata`.
Запрос идёт **на сервере** через существующий `api.server.get` (`resources/js/lib/api.ts`).

## Действия

Создать файл `resources/js/lib/seo.ts`:

```ts
import type { Metadata } from 'next';
import { api } from '@/lib/api';

export interface PageSeo {
    key: string;
    path: string;
    title: string | null;
    description: string | null;
    og_image: string | null;
}

// Запрос по key на сервере; null, если записи нет (404) — страница отдаст дефолты.
export async function getPageSeo(key: string): Promise<PageSeo | null> {
    try {
        return await api.server.get<PageSeo>(`/seo/by-key/${key}`, {
            // SEO меняется редко — кэшируем и периодически ревалидируем.
            next: { revalidate: 3600, tags: [`seo:${key}`] },
        } as never);
    } catch {
        return null;
    }
}

// Превращает запись page_seo в объект Metadata с дефолтами.
export function toMetadata(seo: PageSeo | null, fallback: Metadata): Metadata {
    if (!seo) return fallback;

    return {
        title: seo.title ?? fallback.title,
        description: seo.description ?? fallback.description,
        openGraph: seo.og_image
            ? { images: [{ url: seo.og_image }] }
            : fallback.openGraph,
    };
}
```

## Как это работает (контекст проекта)

- `api.server.get(endpoint, options)` (см. `resources/js/lib/api.ts`) бьёт на
  `BACKEND_URL/api` (`http://127.0.0.1/api`) → nginx → PHP-FPM (Laravel). Это SSR-запрос.
- Поле `next` из объекта опций проходит через `...requestInit` в `fetch`, поэтому
  `revalidate`/`tags` работают как обычные Next.js fetch-опции. `as never` гасит несовпадение
  типов `ApiOptions` (можно заменить на корректную типизацию опций, если потребуется).
- `getPageSeo` ловит `404` (`firstOrFail` на бэке) и возвращает `null` → `toMetadata`
  отдаёт `fallback`, страница не падает.

Поток данных:

```text
Браузер/краулер → GET /services
  → nginx → Next.js SSR (3000)
     → generateMetadata() на сервере
        → api.server.get('/seo/by-key/services')
           → http://127.0.0.1/api/seo/by-key/services
              → nginx → PHP-FPM (Laravel) → page_seo → JSON
     → Next.js рендерит <title>/<meta>/og в HTML
  ← готовый HTML с SEO-тегами
```

## Замечания

- Импорт `@/lib/api` использует существующий alias (как в кодовой базе). Проверить, что
  `@` указывает на `resources/js` (tsconfig).
- Расширять `toMetadata` (canonical, keywords, twitter и т.д.) можно позже — сигнатура не меняется.

## Definition of Done

- [ ] `resources/js/lib/seo.ts` создан с `PageSeo`, `getPageSeo`, `toMetadata`.
- [ ] Используется `api.server.get` (SSR), не `api.get`/клиентские хуки.
- [ ] Запрос кэшируется (`next.revalidate` + `tags`).
- [ ] Ошибка/404 → `null` → дефолты.
