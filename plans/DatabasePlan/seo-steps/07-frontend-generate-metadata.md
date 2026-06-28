# Шаг 07 — Frontend: перевод страниц на `generateMetadata`

## Цель

На каждой статической странице заменить статический `export const metadata` на серверную
функцию `generateMetadata`, использующую `getPageSeo` / `toMetadata` (шаг 06).
Компонент рендера страницы трогать не нужно.

## Страницы и их `key`

Все файлы существуют в `resources/js/app/**/page.tsx`:

| `key` | Файл |
|---|---|
| `home` | `app/page.tsx` |
| `services` | `app/services/page.tsx` |
| `catalog` | `app/catalog/page.tsx` |
| `shipments` | `app/otgruzki/page.tsx` |
| `company` | `app/about/page.tsx` |
| `contacts` | `app/contacts/page.tsx` |
| `why_we` | `app/why-we/page.tsx` |
| `vacancy` | `app/vacancy/page.tsx` |
| `privacy_policy` | `app/private-policy/page.tsx` |
| `labor_safety` | `app/ohrana-truda/page.tsx` |
| `equipment_sale` | `app/services/prodazha-oborudovaniya/page.tsx` |
| `equipment_buyout` | `app/services/vykup/page.tsx` |
| `tools_sale` | `app/services/prodazha-instrumenta/page.tsx` |
| `equipment_import` | `app/services/import-oborudovaniya/page.tsx` |

> Динамические страницы `app/catalog/[...slug]/page.tsx` и `app/otgruzki/[id]/page.tsx`
> сюда **не входят** — их SEO относится к каталогу/отгрузкам (другой план).

## Порядок на каждой странице

1. Если есть статический `export const metadata = {...}` — **перенести его значения в сидер**
   `page_seo` (шаг 04), чтобы фронт получал готовые `title`/`description`, а не дефолты.
2. Удалить статический `export const metadata`.
3. Добавить `generateMetadata` с нужным `key`, оставив текущие значения как `fallback`.

## Шаблон

```ts
// app/services/page.tsx
import type { Metadata } from 'next';
import { getPageSeo, toMetadata } from '@/lib/seo';

export async function generateMetadata(): Promise<Metadata> {
    const seo = await getPageSeo('services');

    return toMetadata(seo, {
        title: 'Услуги | ЮНИК С',
        description:
            'Услуги ЮНИК С: продажа, выкуп, поставка, импорт и сопровождение сделок.',
    });
}
```

## Замечания

- `generateMetadata` — **серверная** функция; внутри неё `getPageSeo` ходит через
  `api.server.get`, поэтому теги попадают в первоначальный HTML (нужно для краулеров/OG).
- `fallback` оставлять всегда — на случай, если записи в БД ещё нет или она `is_active = false`.
- Нельзя одновременно экспортировать `const metadata` и `generateMetadata` в одном файле —
  должен остаться только `generateMetadata`.
- Дефолты `fallback` берутся из текущих статических `metadata` каждой страницы.

## Проверка

- Собрать/запустить Next.js, открыть страницу, проверить `<title>`/`<meta name="description">`/
  `og:image` в **исходном** HTML (View Source или `curl`), а не только в DevTools.
- Поменять запись в `page_seo` → после ревалидации (или сборки) теги обновляются.

## Definition of Done

- [ ] На всех 14 страницах `export const metadata` заменён на `generateMetadata`.
- [ ] У каждой указан корректный `key` из таблицы выше.
- [ ] Сохранены `fallback`-значения.
- [ ] Текущие статические значения перенесены в сидер `page_seo`.
